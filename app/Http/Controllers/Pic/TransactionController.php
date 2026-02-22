<?php

namespace App\Http\Controllers\Pic;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransactionRequest;
use App\Models\Disbursement;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;  // ✅ FIX: adds $this->authorize()
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionController extends Controller
{
    // ✅ FIX ERROR 2: Trait provides $this->authorize() — was missing, causing "Call to undefined method"
    use AuthorizesRequests;

    public function __construct(private TransactionService $service) {}

    public function create(Request $request, Disbursement $disbursement): Response
    {
        $this->authorize('create', [Transaction::class, $disbursement]);

        return Inertia::render('Pic/Transactions/Create', [
            'disbursement' => $this->disbursementPayload($disbursement),
        ]);
    }

    public function store(TransactionRequest $request, Disbursement $disbursement): RedirectResponse
    {
        $this->authorize('create', [Transaction::class, $disbursement]);

        $this->service->create($disbursement, $request->validated(), $request->file('proof'));

        return redirect()
            ->route('pic.disbursements.show', $disbursement)
            ->with('success', 'Transaksi berhasil ditambahkan.');
    }

    public function edit(Request $request, Disbursement $disbursement, Transaction $transaction): Response
    {
        $this->authorize('update', $transaction);

        // Ensure the transaction belongs to this disbursement
        abort_if($transaction->disbursement_id !== $disbursement->id, 404);

        return Inertia::render('Pic/Transactions/Edit', [
            'disbursement' => $this->disbursementPayload($disbursement),
            'transaction'  => $this->transactionPayload($transaction),
        ]);
    }

    public function update(TransactionRequest $request, Disbursement $disbursement, Transaction $transaction): RedirectResponse
    {
        $this->authorize('update', $transaction);
        abort_if($transaction->disbursement_id !== $disbursement->id, 404);

        $this->service->update($transaction, $request->validated(), $request->file('proof'));

        return redirect()
            ->route('pic.disbursements.show', $disbursement)
            ->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(Disbursement $disbursement, Transaction $transaction): RedirectResponse
    {
        $this->authorize('delete', $transaction);
        abort_if($transaction->disbursement_id !== $disbursement->id, 404);

        $this->service->delete($transaction);

        return redirect()
            ->route('pic.disbursements.show', $disbursement)
            ->with('success', 'Transaksi berhasil dihapus.');
    }

    /**
     * ✅ FIX ERROR 3: Proof download via signed URL.
     * - Works for PIC (owner), Superadmin, Auditor.
     * - Signed URL validated first, then policy check.
     * - No $this->authorize() needed — uses Gate::denies() which works without the trait.
     *   (Belt-and-suspenders: trait is present anyway via class-level use.)
     */
    public function downloadProof(Request $request, Transaction $transaction): StreamedResponse
    {
        // Validate signed URL (QR codes embed a temporary signed route)
        abort_unless($request->hasValidSignature(), 403, 'Link tidak valid atau sudah kedaluwarsa.');

        // Policy check — allows owner PIC, Superadmin, Auditor
        if (Gate::denies('downloadProof', $transaction)) {
            abort(403, 'Anda tidak memiliki izin untuk mengunduh bukti ini.');
        }

        abort_unless($transaction->hasProof(), 404, 'Bukti tidak ditemukan.');

        $fullPath = storage_path('app/' . $transaction->proof_path);
        abort_unless(file_exists($fullPath), 404, 'File bukti tidak ditemukan di server.');

        $mime     = mime_content_type($fullPath) ?: 'application/octet-stream';
        $filename = $transaction->proof_original_name ?? basename($fullPath);

        return response()->streamDownload(
            fn () => readfile($fullPath),
            $filename,
            ['Content-Type' => $mime, 'Content-Disposition' => 'inline; filename="' . $filename . '"']
        );
    }

    // ── Private helpers ───────────────────────────────

    private function disbursementPayload(Disbursement $d): array
    {
        $d->loadMissing(['pic:id,name', 'budgetAllocation:id,name']);

        return [
            'id'              => $d->id,
            'name'            => $d->name,
            'start_date'      => $d->start_date->format('Y-m-d'),
            'end_date'        => $d->end_date->format('Y-m-d'),
            'amount'          => $d->amount,
            'remaining_funds' => $d->remaining_funds,
            'is_active'       => $d->allowsTransactions(),
            'status'          => $d->status,
            'status_label'    => $d->status_label,
            'pic_name'        => $d->pic_name,
            'allocation_name' => $d->allocation_name,
        ];
    }

    private function transactionPayload(Transaction $t): array
    {
        return [
            'id'               => $t->id,
            'type'             => $t->type->value,
            'transaction_date' => $t->transaction_date->format('Y-m-d'),
            'description'      => $t->description,
            'amount'           => $t->amount,
            'proof_name'       => $t->proof_original_name,
            'has_proof'        => $t->hasProof(),
        ];
    }
}
