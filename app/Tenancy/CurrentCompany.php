<?php

namespace App\Tenancy;

use App\Models\Company;

/**
 * Holds the company the current request (or queued job) is working on.
 *
 * Registered as a scoped binding, so it starts empty on every request/job.
 */
class CurrentCompany
{
    private ?Company $company = null;

    public function set(Company $company): void
    {
        $this->company = $company;
    }

    public function forget(): void
    {
        $this->company = null;
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
}
