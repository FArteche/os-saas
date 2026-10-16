<?php

namespace App\Tenancy;

use App\Enums\CompanyRole;
use App\Models\Company;
use App\Models\Membership;

/**
 * Holds the company the current request (or queued job) is working on,
 * plus the authenticated user's membership in it when known.
 *
 * Registered as a scoped binding, so it starts empty on every request/job.
 */
class CurrentCompany
{
    private ?Company $company = null;

    private ?Membership $membership = null;

    public function set(Company $company, ?Membership $membership = null): void
    {
        $this->company = $company;
        $this->membership = $membership;
    }

    public function forget(): void
    {
        $this->company = null;
        $this->membership = null;
    }

    public function get(): ?Company
    {
        return $this->company;
    }

    public function id(): ?int
    {
        return $this->company?->id;
    }

    public function has(): bool
    {
        return $this->company !== null;
    }

    public function membership(): ?Membership
    {
        return $this->membership;
    }

    /**
     * The authenticated user's role in the current company.
     */
    public function role(): ?CompanyRole
    {
        return $this->membership?->role;
    }

    /**
     * Run a callback as if the given company were the current one, then restore
     * the previous state (even if the callback throws). Used when code legitimately
     * writes into a company other than the request's, e.g. seeding a new company.
     *
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    public function actingAs(Company $company, callable $callback): mixed
    {
        [$previousCompany, $previousMembership] = [$this->company, $this->membership];

        $this->set($company);

        try {
            return $callback();
        } finally {
            $this->company = $previousCompany;
            $this->membership = $previousMembership;
        }
    }
}
