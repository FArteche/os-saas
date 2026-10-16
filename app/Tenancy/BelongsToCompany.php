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
            $currentId = app(CurrentCompany::class)->id();
            $givenId = $model->getAttribute('company_id');

            // Inside a request the current company always wins: a forged company_id
            // (e.g. from Model::create($request->all())) must never reach another tenant.
            if ($currentId !== null) {
                if ($givenId !== null && (int) $givenId !== $currentId) {
                    throw new LogicException(sprintf(
                        'Cannot create %s for company %s while working on company %d.',
                        $model::class, $givenId, $currentId,
                    ));
                }

                $model->setAttribute('company_id', $currentId);

                return;
            }

            // Outside a request (seeders, console), an explicit company_id is required.
            if ($givenId === null) {
                throw new LogicException(sprintf(
                    'Cannot create %s without a current company or an explicit company_id.',
                    $model::class,
                ));
            }
        });

        static::updating(function (Model $model): void {
            if ($model->isDirty('company_id')) {
                throw new LogicException(sprintf('The company of %s cannot be changed.', $model::class));
            }
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
