<?php

namespace App\Http\Controllers;

use App\Actions\Companies\CreateCompany;
use App\Enums\Niche;
use App\Http\Requests\StoreCompanyRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    public function create(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        $hasActiveCompany = $user->companies()->wherePivot('is_active', true)->exists();

        return Inertia::render('onboarding', [
            'niches' => Niche::options(),
            // Without an active company there is no dashboard to go back to.
            'isFirstCompany' => ! $hasActiveCompany,
            // Was removed from every company they belonged to (e.g. a technician who left).
            'lostAccess' => ! $hasActiveCompany && $user->companies()->exists(),
        ]);
    }

    public function store(StoreCompanyRequest $request, CreateCompany $createCompany): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        /** @var array{name: string, niche: string, document?: string|null, phone?: string|null} $data */
        $data = $request->validated();

        $createCompany->handle($user, $data);

        return redirect()->route('dashboard');
    }
}
