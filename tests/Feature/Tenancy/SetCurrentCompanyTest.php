<?php

use App\Enums\CompanyRole;
use App\Models\Company;
use App\Models\User;
use App\Tenancy\CurrentCompany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::middleware('web')->get('/_test/current-company', fn (CurrentCompany $current) => [
        'company_id' => $current->id(),
        'role' => $current->role()?->value,
    ]);
});

it('exposes the membership role without extra queries', function () {
    $user = User::factory()->withCompany(role: CompanyRole::Attendant)->create();

    $this->actingAs($user)->get('/_test/current-company')
        ->assertJson(['company_id' => $user->current_company_id, 'role' => 'attendant']);
});

it('resolves the company, its membership and the switcher list in at most 2 queries', function () {
    $user = User::factory()->withCompany()->create();
    $user->companies()->attach(Company::factory()->create(), ['role' => CompanyRole::Technician]);

    DB::enableQueryLog();
    $this->actingAs($user)->get(route('dashboard'))->assertOk();

    $companyQueries = collect(DB::getQueryLog())
        ->pluck('query')
        ->filter(fn (string $sql) => str_contains($sql, 'companies'));

    expect($companyQueries)->toHaveCount(2);
});

it('uses the current company of an active member', function () {
    $user = User::factory()->withCompany()->create();

    $this->actingAs($user)->get('/_test/current-company')
        ->assertJson(['company_id' => $user->current_company_id]);
});

it('sets no company for guests', function () {
    $this->get('/_test/current-company')->assertJson(['company_id' => null]);
});

it('sets no company for users without companies', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/_test/current-company')->assertJson(['company_id' => null]);
});

it('falls back to another active company when deactivated in the current one', function () {
    $other = Company::factory()->create();
    $user = User::factory()->withCompany(role: CompanyRole::Technician, active: false)->create();
    $user->companies()->attach($other, ['role' => CompanyRole::Owner]);

    $this->actingAs($user)->get('/_test/current-company')
        ->assertJson(['company_id' => $other->id]);

    expect($user->fresh()->current_company_id)->toBe($other->id);
});

it('ignores a current_company_id the user is not a member of', function () {
    $user = User::factory()->withCompany()->create();
    $ownCompanyId = $user->current_company_id;
    $foreign = Company::factory()->create();
    $user->forceFill(['current_company_id' => $foreign->id])->save();

    $this->actingAs($user)->get('/_test/current-company')
        ->assertJson(['company_id' => $ownCompanyId]);
});

it('clears current_company_id when the user has no active company left', function () {
    $user = User::factory()->withCompany(active: false)->create();

    $this->actingAs($user)->get('/_test/current-company')->assertJson(['company_id' => null]);

    expect($user->fresh()->current_company_id)->toBeNull();
});
