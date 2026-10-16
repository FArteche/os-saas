<?php

use App\Enums\CompanyRole;
use App\Models\Company;
use App\Models\User;

it('lets a user belong to several companies with a different role in each', function () {
    $ownShop = Company::factory()->create();
    $friendsShop = Company::factory()->create();

    $user = User::factory()->withCompany($ownShop)->create();
    $user->companies()->attach($friendsShop, ['role' => CompanyRole::Technician]);

    expect($user->companies)->toHaveCount(2)
        ->and($user->roleIn($ownShop))->toBe(CompanyRole::Owner)
        ->and($user->roleIn($friendsShop))->toBe(CompanyRole::Technician)
        ->and($user->currentCompany->is($ownShop))->toBeTrue();
});

it('casts the pivot columns through the Membership model', function () {
    $user = User::factory()->withCompany(role: CompanyRole::Attendant)->create();

    $membership = $user->companies->first()->membership;

    expect($membership->role)->toBe(CompanyRole::Attendant)
        ->and($membership->is_active)->toBeTrue();
});

it('only considers active memberships as belonging to the company', function () {
    $company = Company::factory()->create();
    $member = User::factory()->withCompany($company)->create();
    $deactivated = User::factory()->withCompany($company, CompanyRole::Technician, active: false)->create();
    $stranger = User::factory()->create();

    expect($member->belongsToCompany($company))->toBeTrue()
        ->and($deactivated->belongsToCompany($company))->toBeFalse()
        ->and($stranger->belongsToCompany($company))->toBeFalse()
        ->and($stranger->roleIn($company))->toBeNull();
});

it('removes memberships but keeps users when a company is permanently deleted', function () {
    $user = User::factory()->withCompany()->create();
    $company = $user->currentCompany;

    $company->forceDelete();
    $user->refresh();

    expect($user->exists)->toBeTrue()
        ->and($user->current_company_id)->toBeNull()
        ->and($user->companies)->toBeEmpty();
});

it('hides a deactivated company from its members', function () {
    $user = User::factory()->withCompany()->create();
    $company = $user->currentCompany;

    $company->delete();
    $user->refresh();

    expect($user->currentCompany)->toBeNull()
        ->and($user->companies)->toBeEmpty()
        ->and($user->belongsToCompany($company))->toBeFalse();
});

it('does not allow mass assigning the current company', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create();

    $user->update(['current_company_id' => $company->id]);

    expect($user->fresh()->current_company_id)->toBeNull();
});
