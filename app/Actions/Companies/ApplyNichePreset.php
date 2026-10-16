<?php

namespace App\Actions\Companies;

use App\Models\Company;
use App\Models\EquipmentType;
use App\Tenancy\CurrentCompany;

/**
 * Copies the company's niche preset (equipment types and settings) into it.
 * After this the company owns its copy and can edit everything freely.
 */
class ApplyNichePreset
{
    public function __construct(private CurrentCompany $currentCompany) {}

    public function handle(Company $company): void
    {
        $preset = $company->niche->preset();

        // The request may be working on another company (a user creating their second
        // one), so the new company's rows must be written explicitly "as" it.
        $this->currentCompany->actingAs($company, function () use ($preset) {
            foreach ($preset->equipmentTypes() as $position => $type) {
                EquipmentType::create([...$type, 'position' => $position]);
            }
        });

        // Settings already present on the company win over the preset defaults.
        $company->settings = [...$preset->settings(), ...$company->settings];
        $company->save();
    }
}
