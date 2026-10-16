<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\EquipmentType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EquipmentType>
 */
class EquipmentTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => ucfirst(fake()->unique()->word()),
            'icon' => 'wrench',
            'identifier_label' => 'Nº de série',
            'fields' => [
                ['key' => 'cor', 'label' => 'Cor', 'type' => 'text', 'required' => false, 'options' => null, 'help' => null],
            ],
            'checklist' => [
                ['key' => 'liga', 'label' => 'Liga normalmente'],
            ],
            'is_active' => true,
            'position' => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
