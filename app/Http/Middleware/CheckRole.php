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
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // ✅ PHPDoc annotation: tells Intelephense this is App\Models\User,
        //    which has the typed `role` property (cast to UserRole enum).
        //    auth()->user() returns Authenticatable|null and Intelephense cannot
        //    resolve ->role on that interface; the @var resolves the ambiguity.
        /** @var User|null $user */
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! in_array($user->role->value, $roles, true)) {
            if ($request->wantsJson() || $request->header('X-Inertia')) {
                abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk halaman ini.');
            }
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk halaman ini.');
        }

        return $next($request);
    }
}