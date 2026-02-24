<?php

namespace App\Http\Controllers\Pic;

use App\Http\Controllers\Controller;
use App\Models\Disbursement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;

class DisbursementController extends Controller
{
    public function index(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $query = Disbursement::where('pic_id', $user->id)
            ->with(['budgetAllocation'])
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $today = now()->toDateString();
            match ($request->status) {
                'active'   => $query->where('start_date', '<=', $today)->where('end_date', '>=', $today),
                'expired'  => $query->where('end_date', '<', $today),
                'upcoming' => $query->where('start_date', '>', $today),
                default    => null,
            };
        }

        $disbursements = $query->paginate(12)->withQueryString()
            ->through(fn ($d) => [
                'id'              => $d->id,
                'purpose'         => $d->purpose->label(),
                'purpose_value'   => $d->purpose->value,
                'name'            => $d->name,
                'start_date'      => $d->start_date->format('d/m/Y'),
                'end_date'        => $d->end_date->format('d/m/Y'),
                'allocation_name' => $d->budgetAllocation?->name ?? '—',
                'amount'          => (float) $d->amount,
                'remaining_funds' => $d->remaining_funds,
                'realization_pct' => $d->realization_percentage,
                'status'          => $d->status,          // upcoming|active|grace|expired
                'status_label'    => $d->status_label,
                'days_remaining'  => $d->days_remaining,
                'tx_count'        => $d->transaction_count,
                'chairperson'     => $d->chairperson,
                'is_active'       => $d->allowsTransactions(), // Phase 1+2+3 = true
            ]);

        return Inertia::render('Pic/Disbursements/Index', [
            'disbursements' => $disbursements,
            'filters'       => $request->only(['search', 'status']),
            'summary' => [
                'total'        => (float) Disbursement::where('pic_id', $user->id)->sum('amount'),
                'active_count' => Disbursement::where('pic_id', $user->id)
                                    ->where('start_date', '<=', now())
                                    ->where('end_date', '>=', now())
                                    ->count(),
            ],
        ]);
    }

    public function show(Request $request, Disbursement $disbursement): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_unless($disbursement->pic_id === $user->id, 403);

        $disbursement->load([
            'budgetAllocation',
            'creator',
            // Load oldest first so running balance is computed forward
            'transactions' => fn ($q) => $q->orderBy('transaction_date')->orderBy('created_at'),
        ]);

        // Build running balance oldest → newest, then reverse for newest-first display
        $runningBalance = (float) $disbursement->amount;
        $txList = [];

        foreach ($disbursement->transactions as $t) {
            $runningBalance += $t->type->value === 'income'
                ? (float) $t->amount
                : -(float) $t->amount;

            $txList[] = [
                'id'                   => $t->id,
                'disbursement_id'      => $disbursement->id,
                'type'                 => $t->type->label(),
                'type_value'           => $t->type->value,
                'transaction_date'     => $t->transaction_date->format('d/m/Y'),
                'transaction_date_raw' => $t->transaction_date->format('Y-m-d'), // for Edit form
                'description'          => $t->description,
                'amount'               => (float) $t->amount,
                'running_balance'      => $runningBalance,
                'has_proof'            => $t->hasProof(),
                'proof_name'           => $t->proof_original_name,
                // PIC can only edit/delete transactions they personally created
                'created_by_me'        => $t->created_by === $user->id,
                // PROOF URL BUKTI TRANSAKSI
                'proof_url' => $t->hasProof()? URL::temporarySignedRoute
                    ('transactions.proof.download', now()->addDays(7), ['transaction' => $t->id]
                    ): null,
            ];
        }

        return Inertia::render('Pic/Disbursements/Show', [
            'disbursement' => [
                'id'                   => $disbursement->id,
                'name'                 => $disbursement->name,
                'purpose'              => $disbursement->purpose->label(),
                'purpose_value'        => $disbursement->purpose->value,
                'chairperson'          => $disbursement->chairperson,
                'start_date'           => $disbursement->start_date->format('d/m/Y'),
                'end_date'             => $disbursement->end_date->format('d/m/Y'),
                'start_date_raw'       => $disbursement->start_date->format('Y-m-d'),
                'end_date_raw'         => $disbursement->end_date->format('Y-m-d'),
                'transaction_deadline' => $disbursement->transaction_deadline, // Y-m-d; used by form :max
                'allocation_name'      => $disbursement->budgetAllocation?->name ?? '—',
                'amount'               => (float) $disbursement->amount,
                'remaining_funds'      => $disbursement->remaining_funds,
                'total_expense'        => $disbursement->total_expense,
                'total_income'         => $disbursement->total_income,
                'realization_pct'      => $disbursement->realization_percentage,
                'status'               => $disbursement->status,       // upcoming|active|grace|expired
                'status_label'         => $disbursement->status_label,
                'days_remaining'       => $disbursement->days_remaining,
                // TRUE during Phase 1 (Akan Datang) + Phase 2 (Aktif) + Phase 3 (Pelaporan)
                // FALSE only when Phase 4 (Selesai)
                'is_active'            => $disbursement->allowsTransactions(),
                'transactions'         => array_reverse($txList), // newest first for display
            ],
        ]);
    }
}