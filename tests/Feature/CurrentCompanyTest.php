<?php

use App\Actions\Companies\SwitchCompany;
use App\Enums\CompanyRole;
use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->shopA = Company::factory()->create(['name' => 'Loja A']);
    $this->shopB = Company::factory()->create(['name' => 'Loja B']);

    $this->user = User::factory()->withCompany($this->shopA)->create();
    $this->user->companies()->attach($this->shopB, ['role' => CompanyRole::Technician]);
});

it('shares the current company and the switchable companies with every page', function () {
    $this->actingAs($this->user)->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('currentCompany.id', $this->shopA->id)
            ->where('currentCompany.name', 'Loja A')
            ->where('currentCompany.role', 'owner')
            ->where('currentCompany.roleLabel', 'Proprietário')
            ->has('companies', 2)
            ->where('companies.0.name', 'Loja A')
            ->where('companies.1.name', 'Loja B')
            ->missing('companies.0.document'));
});

it('switches to another company the user is an active member of', function () {
    $this->actingAs($this->user)
        ->put(route('current-company.update'), ['company_id' => $this->shopB->id])
        ->assertRedirect(route('dashboard'));

    expect($this->user->fresh()->current_company_id)->toBe($this->shopB->id);

    $this->actingAs($this->user->fresh())->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('currentCompany.id', $this->shopB->id)
            ->where('currentCompany.roleLabel', 'Técnico'));
});

it('returns 404 for companies the user cannot access', function (string $case) {
    $targetId = match ($case) {
        'not a member' => Company::factory()->create()->id,
        'inactive membership' => tap(Company::factory()->create(), fn (Company $c) => $this->user->companies()
            ->attach($c, ['role' => CompanyRole::Attendant, 'is_active' => false]))->id,
        'deactivated company' => tap($this->shopB)->delete()->id,
        'nonexistent id' => 999999,
    };

    $this->actingAs($this->user)
        ->put(route('current-company.update'), ['company_id' => $targetId])
        ->assertNotFound();

    expect($this->user->fresh()->current_company_id)->toBe($this->shopA->id);
})->with(['not a member', 'inactive membership', 'deactivated company', 'nonexistent id']);

it('validates the company id', function () {
    $this->actingAs($this->user)
        ->put(route('current-company.update'), ['company_id' => 'abc'])
        ->assertSessionHasErrors('company_id');
});

it('requires authentication to switch companies', function () {
    $this->put(route('current-company.update'), ['company_id' => $this->shopB->id])
        ->assertRedirect(route('login'));
});

it('refuses to switch through the action when the user is not a member', function () {
    app(SwitchCompany::class)->handle($this->user, Company::factory()->create());
})->throws(AuthorizationException::class);
