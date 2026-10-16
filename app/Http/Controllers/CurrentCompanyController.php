<?php

namespace App\Http\Controllers;

use App\Actions\Companies\SwitchCompany;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CurrentCompanyController extends Controller
{
    public function update(Request $request, SwitchCompany $switchCompany): RedirectResponse
    {
        $validated = $request->validate([
            'company_id' => ['required', 'integer'],
        ]);

        /** @var User $user */
        $user = $request->user();

        // Only the user's active companies are reachable: anything else is a 404,
        // so the response doesn't reveal whether another company exists.
        /** @var Company $company */
        $company = $user->companies()
            ->wherePivot('is_active', true)
            ->findOrFail($validated['company_id']);

        $switchCompany->handle($user, $company);

        Inertia::flash('toast', ['type' => 'success', 'message' => "Você está em {$company->name}."]);

        // Back to the dashboard: the previous page may belong to the old company.
        return redirect()->route('dashboard');
    }
}
