<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Models\User;
use App\Tenancy\CurrentCompany;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves which company the authenticated user is working on in this request.
 *
 * It never trusts current_company_id alone: the user must hold an active
 * membership in that company, otherwise we fall back to another active one.
 */
class SetCurrentCompany
{
    public function __construct(private CurrentCompany $currentCompany) {}

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User && ($company = $this->resolveCompany($user))) {
            $this->currentCompany->set($company);
        }

        return $next($request);
    }

    private function resolveCompany(User $user): ?Company
    {
        $current = $user->currentCompany;

        if ($current && $user->belongsToCompany($current)) {
            return $current;
        }

        $fallback = $user->companies()
            ->wherePivot('is_active', true)
            ->oldest('company_user.created_at')
            ->first();

        if ($user->current_company_id !== $fallback?->id) {
            $user->forceFill(['current_company_id' => $fallback?->id])->save();
        }

        return $fallback;
    }
}
