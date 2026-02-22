<?php

namespace App\Http\Middleware;

use App\Models\Disbursement;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActivityActive
{
    /**
     * Checks whether the disbursement in the route allows new transactions.
     *
     * Allowed windows:
     *   1. Within the strict activity period (start_date – end_date)
     *   2. Within the grace period (end_date + extra_transaction_days from settings)
     *
     * During grace period the status shows "Persiapan Laporan" in the UI.
     * After the grace period ends, all transaction creation is blocked.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Disbursement|null $disbursement */
        $disbursement = $request->route('disbursement');

        // Route may pass the model directly or just an ID — resolve either way
        if (! $disbursement instanceof Disbursement) {
            $disbursement = Disbursement::find($disbursement);
        }

        if (! $disbursement) {
            abort(404, 'Pencairan tidak ditemukan.');
        }

        // allowsTransactions() checks both active + grace period (see Disbursement model)
        if (! $disbursement->allowsTransactions()) {
            if ($request->wantsJson() || $request->header('X-Inertia')) {
                return back()->with('error',
                    'Periode pencairan telah berakhir. Transaksi tidak dapat ditambahkan.'
                );
            }
            abort(403, 'Periode pencairan telah berakhir. Transaksi tidak dapat ditambahkan.');
        }

        return $next($request);
    }
}
