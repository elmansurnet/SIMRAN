<?php

namespace App\Http\Controllers\Pic;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransactionRequest;
use App\Models\Disbursement;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function __construct(private TransactionService $service) {}

    /* ── CREATE ──────────────────────────────────────── */

    public function create(Request $request, Disbursement $disbursement): \Inertia\Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_unless($disbursement->pic_id === $user->id, 403);
        abort_unless(
            $disbursement->allowsTransactions(),
            403,
            'Periode kegiatan dan masa pelaporan telah berakhir.'
        );

        return Inertia::render('Pic/Transactions/Create', [
            'disbursement' => [
                'id'                   => $disbursement->id,
                'name'                 => $disbursement->name,
                'start_date'           => $disbursement->start_date->format('Y-m-d'),
                'end_date'             => $disbursement->end_date->format('Y-m-d'),
                'transaction_deadline' => $disbursement->transaction_deadline, // Y-m-d :max for date input
                'remaining_funds'      => $disbursement->remaining_funds,
                'amount'               => (float) $disbursement->amount,
                'status'               => $disbursement->status,
                'status_label'         => $disbursement->status_label,
            ],
        ]);
    }

    public function store(TransactionRequest $request, Disbursement $disbursement): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_unless($disbursement->pic_id === $user->id, 403);
        abort_unless($disbursement->allowsTransactions(), 403, 'Periode pencairan telah berakhir.');

        $this->service->create(
            $request->validated(),
            $user->id,
            $disbursement,
            $request->file('proof')
        );

        return redirect()->route('pic.disbursements.show', $disbursement)
            ->with('success', 'Transaksi berhasil ditambahkan.');
    }

    /* ── EDIT / UPDATE ───────────────────────────────── */

    public function edit(Request $request, Disbursement $disbursement, Transaction $transaction): \Inertia\Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_unless($disbursement->pic_id === $user->id, 403);
        abort_unless($transaction->disbursement_id === $disbursement->id, 403);
        abort_unless($transaction->created_by === $user->id, 403, 'Hanya bisa mengedit transaksi milik Anda.');
        abort_unless($disbursement->allowsTransactions(), 403, 'Periode pencairan telah berakhir.');

        return Inertia::render('Pic/Transactions/Edit', [
            'disbursement' => [
                'id'                   => $disbursement->id,
                'name'                 => $disbursement->name,
                'start_date'           => $disbursement->start_date->format('Y-m-d'),
                'end_date'             => $disbursement->end_date->format('Y-m-d'),
                'transaction_deadline' => $disbursement->transaction_deadline,
                'status'               => $disbursement->status,
                'status_label'         => $disbursement->status_label,
            ],
            'transaction' => [
                'id'               => $transaction->id,
                'type'             => $transaction->type->value,
                'transaction_date' => $transaction->transaction_date->format('Y-m-d'),
                'description'      => $transaction->description,
                'amount'           => (float) $transaction->amount,
                'proof_name'       => $transaction->proof_original_name,
            ],
        ]);
    }

    public function update(TransactionRequest $request, Disbursement $disbursement, Transaction $transaction): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_unless($disbursement->pic_id === $user->id, 403);
        abort_unless($transaction->disbursement_id === $disbursement->id, 403);
        abort_unless($transaction->created_by === $user->id, 403, 'Hanya bisa mengedit transaksi milik Anda.');
        abort_unless($disbursement->allowsTransactions(), 403, 'Periode pencairan telah berakhir.');

        $this->service->update($transaction, $request->validated(), $request->file('proof'));

        return redirect()->route('pic.disbursements.show', $disbursement)
            ->with('success', 'Transaksi berhasil diperbarui.');
    }

    /* ── DESTROY (single) ────────────────────────────── */

    public function destroy(Request $request, Disbursement $disbursement, Transaction $transaction): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_unless($disbursement->pic_id === $user->id, 403);
        abort_unless($transaction->disbursement_id === $disbursement->id, 403);
        abort_unless($transaction->created_by === $user->id, 403, 'Hanya bisa menghapus transaksi milik Anda.');
        abort_unless(
            $disbursement->allowsTransactions(),
            403,
            'Transaksi tidak bisa dihapus setelah periode pelaporan berakhir.'
        );

        $this->service->delete($transaction);

        return back()->with('success', 'Transaksi berhasil dihapus.');
    }

    /* ── BATCH DESTROY ───────────────────────────────── */

    public function batchDestroy(Request $request, Disbursement $disbursement): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_unless($disbursement->pic_id === $user->id, 403);
        abort_unless(
            $disbursement->allowsTransactions(),
            403,
            'Transaksi tidak bisa dihapus setelah periode pelaporan berakhir.'
        );

        $validated = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'exists:transactions,id'],
        ]);

        // Only delete transactions that belong to this disbursement AND were created by this PIC
        $transactions = Transaction::whereIn('id', $validated['ids'])
            ->where('disbursement_id', $disbursement->id)
            ->where('created_by', $user->id)
            ->get();

        if ($transactions->count() !== count($validated['ids'])) {
            return back()->with(
                'error',
                'Beberapa transaksi yang dipilih tidak valid atau bukan milik Anda.'
            );
        }

        DB::transaction(function () use ($transactions) {
            foreach ($transactions as $tx) {
                $this->service->delete($tx);
            }
        });

        return back()->with('success', $transactions->count() . ' transaksi berhasil dihapus.');
    }

    /* ── PROOF DOWNLOAD ──────────────────────────────── */

    /**
     * Serve proof files inline (for browser view and QR code scan).
     */
    public function downloadProof(Request $request, Transaction $transaction)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Link bukti tidak valid atau telah kedaluwarsa.');
        }

        abort_unless($transaction->hasProof(), 404, 'Bukti tidak tersedia.');

        // 🔑 GUNAKAN STORAGE (SAMA SEPERTI ZIP)
        abort_unless(Storage::exists($transaction->proof_path), 404, 'File tidak ditemukan.');

        $path = Storage::path($transaction->proof_path);

        return response()->file($path, [
            'Content-Type'        => mime_content_type($path) ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="'.$transaction->proof_original_name.'"',
        ]);
    }
}