<?php

namespace App\Policies;

use App\Enums\CompanyRole;
use App\Models\EquipmentType;
use App\Models\User;
use App\Tenancy\CurrentCompany;

/**
 * Equipment types shape how everyone in the company works, so only owners manage them.
 * Tenant isolation is already guaranteed by the BelongsToCompany scope (other companies'
 * types are never even loaded); this policy only decides by role.
 */
class EquipmentTypePolicy
{
    public function __construct(private CurrentCompany $currentCompany) {}

    public function viewAny(User $user): bool
    {
        return $this->isOwner();
    }

    public function create(User $user): bool
    {
        return $this->isOwner();
    }

    public function update(User $user, EquipmentType $equipmentType): bool
    {
        return $this->isOwner();
    }

    private function isOwner(): bool
    {
        return $this->currentCompany->role() === CompanyRole::Owner;
    }
}
