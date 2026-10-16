<?php

namespace App\Actions\Companies;

use App\Enums\CompanyRole;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateCompany
{
    public function __construct(private ApplyNichePreset $applyNichePreset) {}

    /**
     * Create a company owned by the given user, seed it with its niche preset
     * and switch the user to it.
     *
     * @param  array{name: string, niche: string, document?: string|null, phone?: string|null}  $data
     */
    public function handle(User $owner, array $data): Company
    {
        return DB::transaction(function () use ($owner, $data) {
            $company = Company::create($data);

            $owner->companies()->attach($company, ['role' => CompanyRole::Owner]);
            $owner->forceFill(['current_company_id' => $company->id])->save();

            $this->applyNichePreset->handle($company);

            return $company;
        });
    }
}
