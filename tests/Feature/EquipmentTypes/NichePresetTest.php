<?php

use App\Actions\Companies\CreateCompany;
use App\Enums\Niche;
use App\Http\Requests\SaveEquipmentTypeRequest;
use App\Models\EquipmentType;
use App\Models\User;
use App\Tenancy\CurrentCompany;
use Illuminate\Support\Facades\Validator;

it('seeds each niche with its equipment types', function (string $niche, array $expectedTypes) {
    $user = User::factory()->create();

    $company = app(CreateCompany::class)->handle($user, ['name' => 'Loja', 'niche' => $niche]);

    $names = app(CurrentCompany::class)->actingAs(
        $company,
        fn () => EquipmentType::ordered()->pluck('name')->all(),
    );

    expect($names)->toBe($expectedTypes)
        ->and($company->fresh()->settings)->toBe(['warranty_days' => 90]);
})->with([
    'generic' => ['generic', ['Equipamento']],
    'cellphone' => ['cellphone', ['Celular', 'Tablet', 'Notebook']],
    'auto repair' => ['auto_repair', ['Carro', 'Moto']],
]);

it('seeds the NEW company when the user already works on another one', function () {
    $user = User::factory()->withCompany()->create();
    $first = $user->currentCompany;

    $this->actingAs($user)->post(route('onboarding.store'), ['name' => 'Oficina', 'niche' => 'auto_repair'])
        ->assertSessionHasNoErrors();

    $second = $user->fresh()->currentCompany;
    $typesOf = fn ($company) => EquipmentType::withoutCompanyScope()->where('company_id', $company->id)->pluck('name')->all();

    expect($typesOf($second))->toBe(['Carro', 'Moto'])
        ->and($typesOf($first))->toBe([]);
});

it('restores the current company after seeding', function () {
    $user = User::factory()->withCompany()->create();
    $current = app(CurrentCompany::class);
    $current->set($user->currentCompany);

    app(CreateCompany::class)->handle($user, ['name' => 'Outra', 'niche' => 'generic']);

    expect($current->id())->toBe($user->currentCompany->id);
});

it('ships presets that pass the same validation as the editor', function (Niche $niche) {
    $rules = (new SaveEquipmentTypeRequest)->rules();
    unset($rules['name']); // uniqueness needs a request context; names are checked above

    foreach ($niche->preset()->equipmentTypes() as $type) {
        $validator = Validator::make($type, $rules);

        expect($validator->errors()->all())->toBe([], "{$niche->value} / {$type['name']}");
    }
})->with(Niche::cases());

it('uses unique field and checklist keys inside every preset type', function (Niche $niche) {
    foreach ($niche->preset()->equipmentTypes() as $type) {
        $fieldKeys = array_column($type['fields'], 'key');
        $itemKeys = array_column($type['checklist'], 'key');

        expect($fieldKeys)->toBe(array_values(array_unique($fieldKeys)))
            ->and($itemKeys)->toBe(array_values(array_unique($itemKeys)));
    }
})->with(Niche::cases());
