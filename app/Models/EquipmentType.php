<?php

namespace App\Models;

use App\Enums\EquipmentIcon;
use App\Tenancy\BelongsToCompany;
use Database\Factories\EquipmentTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * A kind of equipment a company services (e.g. "Celular", "Carro"), with its own
 * custom fields and entry checklist.
 *
 * @phpstan-type FieldDefinition array{key: string, label: string, type: string, required: bool, options: list<string>|null, help: string|null}
 * @phpstan-type ChecklistItem array{key: string, label: string}
 *
 * @property int $id
 * @property string $name
 * @property EquipmentIcon $icon
 * @property string $identifier_label
 * @property list<FieldDefinition> $fields
 * @property list<ChecklistItem> $checklist
 * @property bool $is_active
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'icon', 'identifier_label', 'fields', 'checklist', 'is_active', 'position'])]
class EquipmentType extends Model
{
    /** @use HasFactory<EquipmentTypeFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * Defaults mirrored from the migration.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'icon' => 'wrench',
        'fields' => '[]',
        'checklist' => '[]',
        'is_active' => true,
        'position' => 0,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'icon' => EquipmentIcon::class,
            'fields' => 'array',
            'checklist' => 'array',
            'is_active' => 'boolean',
            'position' => 'integer',
        ];
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('position')->orderBy('name');
    }
}
