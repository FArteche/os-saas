<?php

namespace App\Actions\Users;

use App\Enums\CompanyRole;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Deactivates (soft deletes) a user account without leaving companies ownerless.
 *
 * For every company the user owns alone:
 *  - the oldest active member is promoted to owner, or
 *  - if nobody else is active there, the company is deactivated too.
 */
class DeactivateAccount
{
    public function handle(User $user): void
    {
        DB::transaction(function () use ($user) {
            $ownedIds = $user->companies()
                ->wherePivot('role', CompanyRole::Owner)
                ->wherePivot('is_active', true)
                ->pluck('companies.id');

            // Lock the owned companies (always in id order, to avoid deadlocks) so two
            // owners leaving at the same time are handled one after the other.
            $ownedCompanies = Company::whereKey($ownedIds)->orderBy('id')->lockForUpdate()->get();

            foreach ($ownedCompanies as $company) {
                $this->handOver($company, $user);
            }

            $user->companies()->newPivotQuery()->update(['is_active' => false]);

            // Passkeys are credentials, not history: an account that cannot log in keeps none.
            $user->passkeys()->delete();

            $user->delete();
        });
    }

    private function handOver(Company $company, User $leaving): void
    {
        $otherMembers = $company->users()
            ->whereKeyNot($leaving->id)
            ->wherePivot('is_active', true);

        if ((clone $otherMembers)->wherePivot('role', CompanyRole::Owner)->exists()) {
            return;
        }

        $successor = $otherMembers->oldest('company_user.created_at')->first();

        if ($successor) {
            $company->users()->updateExistingPivot($successor->id, ['role' => CompanyRole::Owner]);

            return;
        }

        $company->delete();
    }
}
