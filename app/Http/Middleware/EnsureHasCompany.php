<?php

namespace App\Http\Middleware;

use App\Tenancy\CurrentCompany;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sends users without an active company to the onboarding page.
 * Must run after SetCurrentCompany (which is in the web group).
 */
class EnsureHasCompany
{
    public function __construct(private CurrentCompany $currentCompany) {}

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->currentCompany->has()) {
            return redirect()->route('onboarding.create');
        }

        return $next($request);
    }
}
