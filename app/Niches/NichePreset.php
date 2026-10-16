<?php

namespace App\Niches;

/**
 * Starting configuration copied into a company when it is created.
 * Adding a niche = adding a class; the core never changes.
 *
 * @phpstan-import-type FieldDefinition from \App\Models\EquipmentType
 * @phpstan-import-type ChecklistItem from \App\Models\EquipmentType
 */
interface NichePreset
{
    /**
     * @return list<array{name: string, icon: string, identifier_label: string, fields: list<FieldDefinition>, checklist: list<ChecklistItem>}>
     */
    public function equipmentTypes(): array;

    /**
     * Company settings this niche starts with.
     *
     * @return array<string, mixed>
     */
    public function settings(): array;
}
