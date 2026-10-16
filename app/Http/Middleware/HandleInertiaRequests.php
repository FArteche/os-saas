<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Models\User;
use App\Tenancy\CurrentCompany;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'currentCompany' => fn () => $this->currentCompany($request),
            'companies' => fn () => $this->companies($request),
        ];
    }

    /**
     * @return array{id: int, name: string, role: string|null, roleLabel: string|null}|null
     */
    private function currentCompany(Request $request): ?array
    {
        $company = app(CurrentCompany::class)->get();
        $user = $request->user();

        if (! $company || ! $user instanceof User) {
            return null;
        }

        $role = $user->roleIn($company);

        return [
            'id' => $company->id,
            'name' => $company->name,
            'role' => $role?->value,
            'roleLabel' => $role?->label(),
        ];
    }

    /**
     * The companies the user can switch to.
     *
     * @return list<array{id: int, name: string}>
     */
    private function companies(Request $request): array
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return [];
        }

        return array_values($user->companies()
            ->wherePivot('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Company $company) => ['id' => $company->id, 'name' => $company->name])
            ->all());
    }
}
