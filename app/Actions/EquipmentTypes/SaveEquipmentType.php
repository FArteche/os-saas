<?php

namespace App\Actions\EquipmentTypes;

use App\Enums\FieldType;
use App\Models\EquipmentType;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Creates or updates an equipment type.
 *
 * Field and checklist keys identify stored values (equipment custom_fields, OS checklists),
 * so they never change: existing items keep their key even when renamed, and only new
 * items get a key, generated from their label.
 *
 * @phpstan-import-type FieldDefinition from EquipmentType
 * @phpstan-import-type ChecklistItem from EquipmentType
 *
 * @phpstan-type FieldInput array{key?: string|null, label: string, type: string, required?: bool, options?: list<string>|null, help?: string|null}
 * @phpstan-type ChecklistInput array{key?: string|null, label: string}
 * @phpstan-type TypeInput array{name: string, icon: string, identifier_label: string, fields: list<FieldInput>, checklist: list<ChecklistInput>}
 */
class SaveEquipmentType
{
    /**
     * @param  TypeInput  $data
     */
    public function handle(?EquipmentType $type, array $data): EquipmentType
    {
        $type ??= new EquipmentType(['position' => (int) EquipmentType::max('position') + 1]);

        $type->fill([
            'name' => $data['name'],
            'icon' => $data['icon'],
            'identifier_label' => $data['identifier_label'],
            'fields' => $this->normalizeFields($data['fields'], array_column($type->fields, 'key')),
            'checklist' => $this->normalizeChecklist($data['checklist'], array_column($type->checklist, 'key')),
        ]);

        try {
            // Own (nested) transaction = savepoint: in PostgreSQL a failed statement aborts the
            // whole surrounding transaction unless it is rolled back to a savepoint.
            DB::transaction(fn () => $type->save());
        } catch (UniqueConstraintViolationException) {
            // Two saves raced past the validation rule; the database index caught it.
            throw ValidationException::withMessages([
                'name' => 'Já existe um tipo de equipamento com este nome.',
            ]);
        }

        return $type;
    }

    /**
     * @param  list<FieldInput>  $fields
     * @param  list<string>  $existingKeys
     * @return list<FieldDefinition>
     */
    private function normalizeFields(array $fields, array $existingKeys): array
    {
        $used = [];

        return array_map(function (array $field) use ($existingKeys, &$used) {
            $isSelect = $field['type'] === FieldType::Select->value;

            return [
                'key' => $this->resolveKey($field['key'] ?? null, $field['label'], $existingKeys, $used),
                'label' => $field['label'],
                'type' => $field['type'],
                'required' => (bool) ($field['required'] ?? false),
                'options' => $isSelect ? ($field['options'] ?? []) : null,
                'help' => ($field['help'] ?? null) ?: null,
            ];
        }, $fields);
    }

    /**
     * @param  list<ChecklistInput>  $items
     * @param  list<string>  $existingKeys
     * @return list<ChecklistItem>
     */
    private function normalizeChecklist(array $items, array $existingKeys): array
    {
        $used = [];

        return array_map(fn (array $item) => [
            'key' => $this->resolveKey($item['key'] ?? null, $item['label'], $existingKeys, $used),
            'label' => $item['label'],
        ], $items);
    }

    /**
     * Keep a key that already belongs to this type; otherwise derive one from the label.
     *
     * @param  list<string>  $existingKeys
     * @param  array<string, true>  $used  keys taken so far in this save (passed by reference)
     */
    private function resolveKey(?string $sentKey, string $label, array $existingKeys, array &$used): string
    {
        $key = $sentKey !== null && in_array($sentKey, $existingKeys, true) && ! isset($used[$sentKey])
            ? $sentKey
            : $this->uniqueKey(Str::slug($label, '_') ?: 'campo', $existingKeys, $used);

        $used[$key] = true;

        return $key;
    }

    /**
     * @param  list<string>  $existingKeys
     * @param  array<string, true>  $used
     */
    private function uniqueKey(string $base, array $existingKeys, array $used): string
    {
        $base = substr($base, 0, 50);
        $key = $base;

        // A new item never reuses a key this type already had, not even one from a removed
        // item: otherwise values stored under the old field would "come back" in the new one.
        for ($i = 2; isset($used[$key]) || in_array($key, $existingKeys, true); $i++) {
            $key = "{$base}_{$i}";
        }

        return $key;
    }
}
