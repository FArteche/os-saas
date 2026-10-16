<?php

namespace App\Http\Requests;

use App\Enums\EquipmentIcon;
use App\Enums\FieldType;
use App\Models\EquipmentType;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates both creating and updating an equipment type (fields and checklist included).
 */
class SaveEquipmentTypeRequest extends FormRequest
{
    public const MAX_FIELDS = 30;

    public const MAX_CHECKLIST_ITEMS = 40;

    private const KEY_FORMAT = 'regex:/^[a-z0-9_]{1,60}$/';

    /**
     * @return array<string, ValidationRule|Closure|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', $this->uniqueNameRule()],
            'icon' => ['required', Rule::enum(EquipmentIcon::class)],
            'identifier_label' => ['required', 'string', 'max:50'],

            'fields' => ['present', 'array', 'max:'.self::MAX_FIELDS],
            'fields.*' => ['array:key,label,type,required,options,help'],
            'fields.*.key' => ['nullable', 'string', self::KEY_FORMAT],
            'fields.*.label' => ['required', 'string', 'max:60', 'distinct:ignore_case'],
            'fields.*.type' => ['required', Rule::enum(FieldType::class)],
            'fields.*.required' => ['boolean'],
            'fields.*.options' => ['exclude_unless:fields.*.type,select', 'required', 'array', 'min:1', 'max:50'],
            'fields.*.options.*' => ['required', 'string', 'max:60', 'distinct:ignore_case'],
            'fields.*.help' => ['nullable', 'string', 'max:120'],

            'checklist' => ['present', 'array', 'max:'.self::MAX_CHECKLIST_ITEMS],
            'checklist.*' => ['array:key,label'],
            'checklist.*.key' => ['nullable', 'string', self::KEY_FORMAT],
            'checklist.*.label' => ['required', 'string', 'max:60', 'distinct:ignore_case'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nome do tipo',
            'icon' => 'ícone',
            'identifier_label' => 'nome do identificador',
            'fields' => 'campos',
            'fields.*.label' => 'nome do campo',
            'fields.*.type' => 'tipo do campo',
            'fields.*.options' => 'opções',
            'fields.*.options.*' => 'opção',
            'fields.*.help' => 'texto de ajuda',
            'checklist' => 'checklist',
            'checklist.*.label' => 'item do checklist',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'fields.max' => 'Um tipo pode ter no máximo :max campos.',
            'checklist.max' => 'O checklist pode ter no máximo :max itens.',
            'fields.*.label.distinct' => 'Já existe outro campo com este nome.',
            'fields.*.options.required' => 'Adicione pelo menos uma opção à lista.',
            'fields.*.options.*.distinct' => 'Esta opção está repetida.',
            'checklist.*.label.distinct' => 'Este item já está no checklist.',
        ];
    }

    /**
     * Same rule as the database index: unique per company, ignoring case.
     * The BelongsToCompany scope already limits the query to the current company.
     */
    private function uniqueNameRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) {
            if (! is_string($value)) {
                return;
            }

            /** @var EquipmentType|null $current */
            $current = $this->route('equipmentType');

            $taken = EquipmentType::query()
                ->whereRaw('lower(name) = ?', [mb_strtolower($value)])
                ->when($current, fn ($query) => $query->whereKeyNot($current->id))
                ->exists();

            if ($taken) {
                $fail('Já existe um tipo de equipamento com este nome.');
            }
        };
    }
}
