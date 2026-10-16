<?php

namespace App\Actions\Companies;

use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class SwitchCompany
{
    /**
     * Make the given company the user's current one.
     *
     * @throws AuthorizationException when the user is not an active member
     */
    public function handle(User $user, Company $company): void
    {
        if (! $user->belongsToCompany($company)) {
            throw new AuthorizationException('Você não tem acesso a esta empresa.');
        }

        $user->forceFill(['current_company_id' => $company->id])->save();
    }
}
