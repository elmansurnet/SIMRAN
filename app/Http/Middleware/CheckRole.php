<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Usage in routes:
     *   middleware('role:superadmin')
     *   middleware('role:superadmin,auditor')   ← comma-separated for multi-role
     *
     * Valid roles: superadmin | pic | auditor | applicant | approver
     *
     * WHY the @var annotation:
     *   auth()->user() is declared as returning Authenticatable|null on the Guard
     *   interface. Intelephense resolves only the interface contract, which does NOT
     *   declare the 'role' property — that belongs to App\Models\User. Adding @var
     *   gives the analyser the concrete type so it can resolve ->role->value without
     *   flagging "Undefined property". This is the correct Laravel idiom (not a
     *   suppression hack).
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        /** @var User|null $user */
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! in_array($user->role->value, $roles, true)) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk halaman ini.');
        }

        return $next($request);
    }
}