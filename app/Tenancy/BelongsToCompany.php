<?php

namespace App\Tenancy;

use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * Makes a model owned by a company: queries are scoped to the current
 * company and new rows get its company_id automatically.
 *
 * @property int $company_id
 * @property-read Company $company
 *
 * @mixin Model
 */
trait BelongsToCompany
{
    public static function bootBelongsToCompany(): void
    {
        static::addGlobalScope(new CompanyScope);

        static::creating(function (Model $model): void {
            if ($model->getAttribute('company_id') !== null) {
                return;
            }

            $companyId = app(CurrentCompany::class)->id()
                ?? throw new LogicException(sprintf(
                    'Cannot create %s without a current company or an explicit company_id.',
                    $model::class,
                ));

            $model->setAttribute('company_id', $companyId);
        });
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Query without the company restriction (e.g. public pages, admin reports).
     *
     * @return Builder<static>
     */
    public static function withoutCompanyScope(): Builder
    {
        return static::query()->withoutGlobalScope(CompanyScope::class);
    }
}
