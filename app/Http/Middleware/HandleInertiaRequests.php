<?php

namespace App\Http\Middleware;

use App\Models\BudgetAllocation;
use App\Models\Setting;
use App\Models\User;
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
        /**
         * WHY @var here:
         *   $request->user() returns Authenticatable|null per the framework interface.
         *   Intelephense cannot know that the authenticatable IS App\Models\User, so
         *   it reports "Undefined method 'role'" for ->role->value and ->role->label().
         *   The @var annotation provides the concrete type to the static analyser
         *   without changing runtime behaviour.
         *
         * @var User|null $user
         */
        $user = $request->user();

        $lowBudgetAlerts = [];
        if ($user && in_array($user->role->value, ['superadmin', 'auditor'], true)) {
            $lowBudgetAlerts = BudgetAllocation::withoutTrashed()
                ->get()
                ->filter(fn ($a) => $a->utilization_percentage >= 80)
                ->map(fn ($a) => [
                    'id'        => $a->id,
                    'name'      => $a->name,
                    'pct'       => round((float) $a->utilization_percentage, 1),
                    'remaining' => (float) $a->remaining_amount,
                ])
                ->values()
                ->all();
        }

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id'         => $user->id,
                    'name'       => $user->name,
                    'email'      => $user->email,
                    'role'       => $user->role->label(),
                    'role_value' => $user->role->value,
                    'initials'   => collect(explode(' ', $user->name))
                                        ->map(fn ($w) => $w[0] ?? '')
                                        ->take(2)->join(''),
                ] : null,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error'   => $request->session()->get('error'),
            ],
            'low_budget_alerts'      => $lowBudgetAlerts,
            'extra_transaction_days' => (int) Setting::extraTransactionDays(),
        ]);
    }
}