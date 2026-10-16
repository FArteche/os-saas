<?php

namespace App\Enums;

/**
 * The kinds of custom field an equipment type can define.
 */
enum FieldType: string
{
    case Text = 'text';
    case Textarea = 'textarea';
    case Number = 'number';
    case Select = 'select';
    case Boolean = 'boolean';
    case Date = 'date';
    case Secret = 'secret';

    public function label(): string
    {
        return match ($this) {
            self::Text => 'Texto curto',
            self::Textarea => 'Texto longo',
            self::Number => 'Número',
            self::Select => 'Lista de opções',
            self::Boolean => 'Sim / Não',
            self::Date => 'Data',
            self::Secret => 'Senha (protegida)',
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(fn (self $type) => ['value' => $type->value, 'label' => $type->label()], self::cases());
    }
}
