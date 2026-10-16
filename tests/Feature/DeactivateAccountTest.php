<?php

use App\Actions\Users\DeactivateAccount;
use App\Enums\CompanyRole;
use App\Models\Company;
use App\Models\User;

/**
 * Adds $user to $company, $daysAgo days in the past (so "oldest member" is deterministic).
 */
function joinCompany(User $user, Company $company, CompanyRole $role, int $daysAgo, bool $active = true): void
{
    test()->travel(-$daysAgo)->days();
    $user->companies()->attach($company, ['role' => $role, 'is_active' => $active]);
    test()->travelBack();
}

beforeEach(function () {
    $this->company = Company::factory()->create();
    $this->owner = User::factory()->create();
    joinCompany($this->owner, $this->company, CompanyRole::Owner, daysAgo: 30);
});

it('promotes the oldest active member when the only owner leaves', function () {
    $inactiveVeteran = User::factory()->create();
    $oldest = User::factory()->create();
    $newest = User::factory()->create();
    joinCompany($inactiveVeteran, $this->company, CompanyRole::Attendant, daysAgo: 25, active: false);
    joinCompany($oldest, $this->company, CompanyRole::Attendant, daysAgo: 20);
    joinCompany($newest, $this->company, CompanyRole::Technician, daysAgo: 5);

    app(DeactivateAccount::class)->handle($this->owner);

    expect($oldest->roleIn($this->company))->toBe(CompanyRole::Owner)
        ->and($newest->roleIn($this->company))->toBe(CompanyRole::Technician)
        ->and($inactiveVeteran->roleIn($this->company))->toBe(CompanyRole::Attendant)
        ->and($this->company->fresh()->trashed())->toBeFalse();
});

it('does not promote anyone when another active owner exists', function () {
    $coOwner = User::factory()->create();
    $member = User::factory()->create();
    joinCompany($member, $this->company, CompanyRole::Attendant, daysAgo: 20);
    joinCompany($coOwner, $this->company, CompanyRole::Owner, daysAgo: 10);

    app(DeactivateAccount::class)->handle($this->owner);

    expect($member->roleIn($this->company))->toBe(CompanyRole::Attendant)
        ->and($coOwner->roleIn($this->company))->toBe(CompanyRole::Owner);
});

it('never promotes a member whose own account is deactivated', function () {
    $deactivatedMember = User::factory()->create();
    $activeMember = User::factory()->create();
    joinCompany($deactivatedMember, $this->company, CompanyRole::Attendant, daysAgo: 20);
    joinCompany($activeMember, $this->company, CompanyRole::Technician, daysAgo: 10);
    $deactivatedMember->delete();

    app(DeactivateAccount::class)->handle($this->owner);

    expect($activeMember->roleIn($this->company))->toBe(CompanyRole::Owner);
});

it('deactivates the company when the owner was its only member', function () {
    app(DeactivateAccount::class)->handle($this->owner);

    $this->assertSoftDeleted($this->company);
});

it('keeps history: the user and company rows still exist', function () {
    app(DeactivateAccount::class)->handle($this->owner);

    $this->assertSoftDeleted($this->owner);

    expect(User::withTrashed()->find($this->owner->id))->not->toBeNull()
        ->and(Company::withTrashed()->find($this->company->id))->not->toBeNull()
        ->and($this->owner->companies()->wherePivot('is_active', true)->exists())->toBeFalse();
});

it('does not touch companies where the user is not an owner', function () {
    $otherCompany = Company::factory()->create();
    $boss = User::factory()->create();
    joinCompany($boss, $otherCompany, CompanyRole::Owner, daysAgo: 40);
    joinCompany($this->owner, $otherCompany, CompanyRole::Technician, daysAgo: 15);

    app(DeactivateAccount::class)->handle($this->owner);

    expect($otherCompany->fresh()->trashed())->toBeFalse()
        ->and($boss->roleIn($otherCompany))->toBe(CompanyRole::Owner);
});

it('prevents a deactivated user from logging in', function () {
    app(DeactivateAccount::class)->handle($this->owner);

    $this->post(route('login.store'), [
        'email' => $this->owner->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});
