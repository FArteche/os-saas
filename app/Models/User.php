<?php

namespace App\Models;

use App\Enums\CompanyRole;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property int|null $current_company_id
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Company|null $currentCompany
 * @property-read Membership $membership
 */
#[Fillable(['name', 'email', 'phone', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsToMany<Company, $this, Membership, 'membership'>
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class)
            ->using(Membership::class)
            ->as('membership')
            ->withPivot('role', 'is_active')
            ->withTimestamps();
    }

    /**
     * The company the user is currently working in.
     *
     * @return BelongsTo<Company, $this>
     */
    public function currentCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'current_company_id');
    }

    public function membershipIn(Company $company): ?Membership
    {
        return $this->companies()->whereKey($company->id)->first()?->membership;
    }

    /**
     * Whether the user has an active membership in the given company.
     */
    public function belongsToCompany(Company $company): bool
    {
        return $this->membershipIn($company)?->is_active === true;
    }

    public function roleIn(Company $company): ?CompanyRole
    {
        return $this->membershipIn($company)?->role;
    }
}
