<?php

namespace App\Models;

use App\Enums\CompanyRole;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * A user's link to a company (a row of company_user).
 *
 * @property int $company_id
 * @property int $user_id
 * @property CompanyRole $role
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Membership extends Pivot
{
    protected $table = 'company_user';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => CompanyRole::class,
            'is_active' => 'boolean',
        ];
    }
}
