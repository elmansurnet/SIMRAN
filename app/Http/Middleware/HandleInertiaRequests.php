<?php

namespace App\Http\Middleware;

use App\Models\BudgetAllocation;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        $alerts = [];
        if ($user && $user->isSuperadmin()) {
            $alerts = BudgetAllocation::whereNull('deleted_at')
                ->get()
                ->filter(fn ($a) => $a->is_low_budget)
                ->map(fn ($a) => [
                    'id'        => $a->id,
                    'name'      => $a->name,
                    'remaining' => $a->remaining_amount,
                    'amount'    => (float) $a->amount,
                ])
                ->values()
                ->toArray();
        }

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id'         => $user->id,
                    'name'       => $user->name,
                    'email'      => $user->email,
                    'role'       => $user->role->label(),
                    'role_value' => $user->role->value,
                    'initials'   => strtoupper(
                        collect(explode(' ', $user->name))
                            ->map(fn ($w) => $w[0] ?? '')
                            ->take(2)
                            ->implode('')
                    ),
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
            'alerts' => $alerts,
            // Shared to every page so Vue forms can set the :max on date inputs
            // without needing an extra request.
            'extra_transaction_days' => Setting::extraTransactionDays(),
        ]);
    }
}