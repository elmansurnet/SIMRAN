<?php

namespace App\Http\Middleware;

use App\Models\Disbursement;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActivityActive
{
    /**
     * Gate all transaction write routes (create, store, edit, update, destroy, batchDestroy).
     *
     * Delegates entirely to Disbursement::allowsTransactions() so the
     * phase logic lives in exactly ONE place (the model).
     *
     * Allowed:  Phase 1 (Persiapan), Phase 2 (Aktif), Phase 3 (Pelaporan)
     * Blocked:  Phase 4 (Selesai) only
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Disbursement|null $disbursement */
        $disbursement = $request->route('disbursement');

        if (! $disbursement instanceof Disbursement) {
            $disbursement = Disbursement::find($disbursement);
        }

        if (! $disbursement) {
            abort(404, 'Pencairan tidak ditemukan.');
        }

        if (! $disbursement->allowsTransactions()) {
            $message = 'Periode kegiatan dan masa pelaporan telah berakhir. '
                . 'Transaksi tidak dapat lagi dibuat atau diubah.';

            if ($request->wantsJson() || $request->header('X-Inertia')) {
                return back()->with('error', $message);
            }

            abort(403, $message);
        }

        return $next($request);
    }
}