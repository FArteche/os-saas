<?php

use App\Actions\EquipmentTypes\SaveEquipmentType;
use App\Enums\CompanyRole;
use App\Models\Company;
use App\Models\EquipmentType;
use App\Models\User;
use App\Tenancy\CurrentCompany;
use Illuminate\Validation\ValidationException;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->company = Company::factory()->create();
    $this->owner = User::factory()->withCompany($this->company)->create();
    $this->type = EquipmentType::factory()->create(['company_id' => $this->company->id, 'name' => 'Celular']);
});

function validPayload(array $overrides = []): array
{
    return [
        'name' => 'Notebook',
        'icon' => 'laptop',
        'identifier_label' => 'Nº de série',
        'fields' => [
            ['key' => null, 'label' => 'Número de série extra', 'type' => 'text', 'required' => false, 'options' => null, 'help' => null],
            ['key' => null, 'label' => 'Voltagem', 'type' => 'select', 'required' => true, 'options' => ['110V', '220V'], 'help' => 'Na etiqueta'],
        ],
        'checklist' => [
            ['key' => null, 'label' => 'Tela'],
        ],
        ...$overrides,
    ];
}

// ------------------------------------------------------------------ access

it('lets the owner list, create and edit types', function () {
    $this->actingAs($this->owner);

    $this->get(route('equipment-types.index'))->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/equipment-types/index')
            ->has('equipmentTypes', 1)
            ->where('equipmentTypes.0.name', 'Celular'));

    $this->get(route('equipment-types.create'))->assertOk();
    $this->get(route('equipment-types.edit', $this->type))->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('equipmentType.name', 'Celular'));
});

it('forbids attendants and technicians', function (CompanyRole $role) {
    $member = User::factory()->withCompany($this->company, $role)->create();
    $this->actingAs($member);

    $this->get(route('equipment-types.index'))->assertForbidden();
    $this->get(route('equipment-types.create'))->assertForbidden();
    $this->post(route('equipment-types.store'), validPayload())->assertForbidden();
    $this->get(route('equipment-types.edit', $this->type))->assertForbidden();
    $this->put(route('equipment-types.update', $this->type), validPayload())->assertForbidden();
    $this->patch(route('equipment-types.toggle', $this->type))->assertForbidden();

    expect(EquipmentType::withoutCompanyScope()->count())->toBe(1);
})->with([CompanyRole::Attendant, CompanyRole::Technician]);

it('returns 404 for another company\'s type, even for an owner', function () {
    $foreign = EquipmentType::factory()->create(['name' => 'Alheio']);
    $this->actingAs($this->owner);

    $this->get(route('equipment-types.edit', $foreign))->assertNotFound();
    $this->put(route('equipment-types.update', $foreign), validPayload())->assertNotFound();
    $this->patch(route('equipment-types.toggle', $foreign))->assertNotFound();

    expect($foreign->fresh()->name)->toBe('Alheio');
});

it('only lists the current company\'s types', function () {
    EquipmentType::factory()->create(['name' => 'De outra loja']);

    $this->actingAs($this->owner)->get(route('equipment-types.index'))
        ->assertInertia(fn (Assert $page) => $page->has('equipmentTypes', 1));
});

// ------------------------------------------------------------------ create / update

it('creates a type with generated keys', function () {
    $this->actingAs($this->owner)->post(route('equipment-types.store'), validPayload())
        ->assertRedirect(route('equipment-types.index'));

    $type = EquipmentType::withoutCompanyScope()->where('name', 'Notebook')->sole();

    expect($type->company_id)->toBe($this->company->id)
        ->and(array_column($type->fields, 'key'))->toBe(['numero_de_serie_extra', 'voltagem'])
        ->and($type->fields[1]['options'])->toBe(['110V', '220V'])
        ->and($type->fields[0]['options'])->toBeNull()
        ->and($type->checklist)->toBe([['key' => 'tela', 'label' => 'Tela']])
        ->and($type->position)->toBe($this->type->position + 1);
});

it('keeps field keys when fields are renamed, reordered or removed', function () {
    $this->type->update(['fields' => [
        ['key' => 'cor', 'label' => 'Cor', 'type' => 'text', 'required' => false, 'options' => null, 'help' => null],
        ['key' => 'imei_2', 'label' => 'IMEI 2', 'type' => 'text', 'required' => false, 'options' => null, 'help' => null],
    ]]);

    $this->actingAs($this->owner)->put(route('equipment-types.update', $this->type), validPayload([
        'name' => 'Celular',
        'fields' => [
            ['key' => 'imei_2', 'label' => 'Segundo IMEI', 'type' => 'text', 'required' => true, 'options' => null, 'help' => null],
            ['key' => null, 'label' => 'Cor', 'type' => 'text', 'required' => false, 'options' => null, 'help' => null],
        ],
    ]))->assertRedirect();

    $fields = $this->type->fresh()->fields;

    // renamed + moved field kept its key; the re-added "Cor" did not revive the removed key
    expect(array_column($fields, 'key'))->toBe(['imei_2', 'cor_2'])
        ->and($fields[0]['label'])->toBe('Segundo IMEI');
});

it('ignores keys that do not belong to the type', function () {
    $this->actingAs($this->owner)->put(route('equipment-types.update', $this->type), validPayload([
        'name' => 'Celular',
        'fields' => [['key' => 'hacked_key', 'label' => 'Cor', 'type' => 'text', 'required' => false, 'options' => null, 'help' => null]],
    ]))->assertRedirect();

    expect($this->type->fresh()->fields[0]['key'])->toBe('cor_2'); // factory type already had "cor"
});

it('toggles a type on and off', function () {
    EquipmentType::factory()->create(['company_id' => $this->company->id, 'name' => 'Tablet']);
    $this->actingAs($this->owner);

    $this->patch(route('equipment-types.toggle', $this->type))->assertRedirect();
    expect($this->type->fresh()->is_active)->toBeFalse();

    $this->patch(route('equipment-types.toggle', $this->type))->assertRedirect();
    expect($this->type->fresh()->is_active)->toBeTrue();
});

// ------------------------------------------------------------------ validation

it('rejects invalid types', function (array $overrides, string $errorKey) {
    $this->actingAs($this->owner)
        ->post(route('equipment-types.store'), validPayload($overrides))
        ->assertSessionHasErrors($errorKey);

    expect(EquipmentType::withoutCompanyScope()->count())->toBe(1);
})->with([
    'duplicate name (case-insensitive)' => [['name' => 'CELULAR'], 'name'],
    'unknown icon' => [['icon' => 'skull'], 'icon'],
    'missing identifier label' => [['identifier_label' => ''], 'identifier_label'],
    'unknown field type' => [['fields' => [['label' => 'X', 'type' => 'file']]], 'fields.0.type'],
    'select without options' => [['fields' => [['label' => 'X', 'type' => 'select', 'options' => []]]], 'fields.0.options'],
    'repeated field label' => [['fields' => [['label' => 'Cor', 'type' => 'text'], ['label' => 'cor', 'type' => 'text']]], 'fields.1.label'],
    'repeated option' => [['fields' => [['label' => 'X', 'type' => 'select', 'options' => ['A', 'a']]]], 'fields.0.options.1'],
    'malformed key' => [['fields' => [['key' => 'Bad Key!', 'label' => 'X', 'type' => 'text']]], 'fields.0.key'],
    'extra keys in a field' => [['fields' => [['label' => 'X', 'type' => 'text', 'company_id' => 99]]], 'fields.0'],
    'too many fields' => [['fields' => array_map(fn ($i) => ['label' => "Campo $i", 'type' => 'text'], range(1, 31))], 'fields'],
    'repeated checklist item' => [['checklist' => [['label' => 'Tela'], ['label' => 'TELA']]], 'checklist.1.label'],
    'fields missing entirely' => [['fields' => null], 'fields'],
]);

it('allows keeping the same name when editing', function () {
    $this->actingAs($this->owner)
        ->put(route('equipment-types.update', $this->type), validPayload(['name' => 'celular']))
        ->assertSessionHasNoErrors();

    expect($this->type->fresh()->name)->toBe('celular');
});

it('accepts a type with no custom fields or checklist', function () {
    $this->actingAs($this->owner)
        ->post(route('equipment-types.store'), validPayload(['fields' => [], 'checklist' => []]))
        ->assertSessionHasNoErrors();
});

// ------------------------------------------------------------------ regressions from the QA round

it('refuses to deactivate the last active type', function () {
    $this->actingAs($this->owner)->patch(route('equipment-types.toggle', $this->type))->assertRedirect();

    expect($this->type->fresh()->is_active)->toBeTrue();
});

it('turns a database duplicate (two saves racing) into a validation error', function () {
    app(CurrentCompany::class)->set($this->company);

    expect(fn () => app(SaveEquipmentType::class)->handle(null, validPayload(['name' => 'celular'])))
        ->toThrow(ValidationException::class);

    // the savepoint kept the surrounding transaction usable
    expect(EquipmentType::count())->toBe(1);
});

it('generates valid, unique keys for labels without letters or digits', function () {
    $this->actingAs($this->owner)->post(route('equipment-types.store'), validPayload([
        'fields' => [
            ['label' => '???', 'type' => 'text'],
            ['label' => '!!!', 'type' => 'text'],
            ['label' => '😀', 'type' => 'text'],
        ],
    ]))->assertSessionHasNoErrors();

    $keys = array_column(EquipmentType::withoutCompanyScope()->where('name', 'Notebook')->sole()->fields, 'key');

    expect($keys)->toBe(['campo', 'campo_2', 'campo_3']);
});

it('drops options sent for fields that are not a list', function () {
    $this->actingAs($this->owner)->post(route('equipment-types.store'), validPayload([
        'fields' => [['label' => 'Cor', 'type' => 'text', 'options' => ['<script>', 'x']]],
    ]))->assertSessionHasNoErrors();

    expect(EquipmentType::withoutCompanyScope()->where('name', 'Notebook')->sole()->fields[0]['options'])->toBeNull();
});

it('checks the role per company (owner in one, technician in another)', function () {
    $other = Company::factory()->create();
    $this->owner->companies()->attach($other, ['role' => CompanyRole::Technician]);

    $this->actingAs($this->owner)->get(route('equipment-types.index'))->assertOk();

    $this->owner->forceFill(['current_company_id' => $other->id])->save();
    $this->actingAs($this->owner->fresh())->get(route('equipment-types.index'))->assertForbidden();
});

it('treats options differing only by surrounding spaces as duplicates', function () {
    $this->actingAs($this->owner)->post(route('equipment-types.store'), validPayload([
        'fields' => [['label' => 'Voltagem', 'type' => 'select', 'options' => ['110V', ' 110V ']]],
    ]))->assertSessionHasErrors('fields.0.options.1');
});

it('ignores a company_id injected into the form', function () {
    $other = Company::factory()->create();

    $this->actingAs($this->owner)->post(route('equipment-types.store'), validPayload(['company_id' => $other->id]));

    expect(EquipmentType::withoutCompanyScope()->where('company_id', $other->id)->exists())->toBeFalse()
        ->and(EquipmentType::withoutCompanyScope()->where('name', 'Notebook')->sole()->company_id)->toBe($this->company->id);
});

it('sends Portuguese icon labels to the editor', function () {
    $this->actingAs($this->owner)->get(route('equipment-types.create'))
        ->assertInertia(fn (Assert $page) => $page->where('icons.0', ['value' => 'smartphone', 'label' => 'Celular']));
});
