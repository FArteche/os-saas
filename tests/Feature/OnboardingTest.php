<?php

use App\Enums\CompanyRole;
use App\Enums\Niche;
use App\Models\Company;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('sends users without a company from the dashboard to onboarding', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('onboarding.create'));
});

it('lets users with a company reach the dashboard', function () {
    $user = User::factory()->withCompany()->create();

    $this->actingAs($user)->get(route('dashboard'))->assertOk();
});

it('shows the onboarding page with the niche options', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('onboarding.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('onboarding')
            ->has('niches', count(Niche::cases()))
            ->where('isFirstCompany', true));
});

it('creates the company with the user as owner and switches to it', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('onboarding.store'), [
        'name' => 'Conserta Fácil',
        'document' => '11.222.333/0001-81',
        'phone' => '(11) 98765-4321',
        'niche' => 'cellphone',
    ])->assertRedirect(route('dashboard'));

    $company = Company::sole();

    expect($company->name)->toBe('Conserta Fácil')
        ->and($company->document)->toBe('11222333000181')
        ->and($company->phone)->toBe('11987654321')
        ->and($company->niche)->toBe(Niche::Cellphone)
        ->and($user->fresh()->current_company_id)->toBe($company->id)
        ->and($user->roleIn($company))->toBe(CompanyRole::Owner);
});

it('accepts an alphanumeric CNPJ typed in lowercase with mask', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('onboarding.store'), [
        'name' => 'Loja Nova',
        'document' => '12.abc.345/01de-35',
        'niche' => 'generic',
    ])->assertSessionHasNoErrors();

    expect(Company::sole()->document)->toBe('12ABC34501DE35');
});

it('only requires name and niche', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('onboarding.store'), [
        'name' => 'Só o nome',
        'niche' => 'generic',
    ])->assertSessionHasNoErrors();

    expect(Company::sole()->document)->toBeNull();
});

it('validates the onboarding form', function (array $data, string $field) {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('onboarding.store'), [...['name' => 'Loja', 'niche' => 'generic'], ...$data])
        ->assertSessionHasErrors($field);

    expect(Company::count())->toBe(0);
})->with([
    'missing name' => [['name' => ''], 'name'],
    'invalid document' => [['document' => '123.456.789-00'], 'document'],
    'short phone' => [['phone' => '9876-5432'], 'phone'],
    'unknown niche' => [['niche' => 'bakery'], 'niche'],
]);

it('lets a user who already has a company create another one', function () {
    $user = User::factory()->withCompany()->create();
    $first = $user->currentCompany;

    $this->actingAs($user)->get(route('onboarding.create'))
        ->assertInertia(fn (Assert $page) => $page->where('isFirstCompany', false));

    $this->actingAs($user)->post(route('onboarding.store'), [
        'name' => 'Segunda Loja',
        'niche' => 'auto_repair',
    ]);

    $user->refresh();

    expect($user->companies)->toHaveCount(2)
        ->and($user->current_company_id)->not->toBe($first->id);
});

it('requires authentication for onboarding', function () {
    $this->get(route('onboarding.create'))->assertRedirect(route('login'));
});
