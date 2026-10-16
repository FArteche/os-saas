<?php

namespace App\Niches;

use App\Enums\FieldType;

/**
 * Shared helpers so presets read like a list of fields instead of nested arrays.
 */
abstract class BasePreset implements NichePreset
{
    /**
     * Minimum warranty for services under the Brazilian Consumer Code (CDC, art. 26).
     */
    public const DEFAULT_WARRANTY_DAYS = 90;

    /**
     * @return array<string, mixed>
     */
    public function settings(): array
    {
        return ['warranty_days' => self::DEFAULT_WARRANTY_DAYS];
    }

    /**
     * @param  list<string>|null  $options
     * @return array{key: string, label: string, type: string, required: bool, options: list<string>|null, help: string|null}
     */
    protected static function field(
        string $key,
        string $label,
        FieldType $type = FieldType::Text,
        bool $required = false,
        ?array $options = null,
        ?string $help = null,
    ): array {
        return [
            'key' => $key,
            'label' => $label,
            'type' => $type->value,
            'required' => $required,
            'options' => $options,
            'help' => $help,
        ];
    }

    /**
     * @return array{key: string, label: string}
     */
    protected static function item(string $key, string $label): array
    {
        return ['key' => $key, 'label' => $label];
    }
}
