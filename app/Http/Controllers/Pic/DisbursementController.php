<?php

namespace App\Http\Controllers\Pic;

use App\Http\Controllers\Controller;
use App\Models\Disbursement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DisbursementController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Disbursement::where('pic_id', $request->user()->id)
            ->with(['budgetAllocation'])
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where('name', 'like', "%{$q}%");
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
                'allocation_name' => $d->budgetAllocation->name,
                'amount'          => (float) $d->amount,
                'remaining_funds' => $d->remaining_funds,
                'realization_pct' => $d->realization_percentage,
                'status'          => $d->status,
                'status_label'    => $d->status_label,
                'days_remaining'  => $d->days_remaining,
                'tx_count'        => $d->transaction_count,
                'chairperson'     => $d->chairperson,
            ]);

        return Inertia::render('Pic/Disbursements/Index', [
            'disbursements' => $disbursements,
            'filters'       => $request->only(['search', 'status']),
            'summary' => [
                'total'          => (float) Disbursement::where('pic_id', $request->user()->id)->sum('amount'),
                'active_count'   => Disbursement::where('pic_id', $request->user()->id)
                                        ->where('start_date', '<=', now())->where('end_date', '>=', now())->count(),
            ],
        ]);
    }

    public function show(Request $request, Disbursement $disbursement): Response
    {
        abort_unless($disbursement->pic_id === $request->user()->id, 403);

        $disbursement->load([
            'budgetAllocation', 'creator',
            'transactions' => fn ($q) => $q->orderByDesc('transaction_date')->orderByDesc('created_at'),
        ]);

        $transactions = $disbursement->transactions;
        $runningBalance = (float) $disbursement->amount;

        $txList = [];
        foreach ($transactions->reverse() as $t) {
            if ($t->type->value === 'income') {
                $runningBalance += (float) $t->amount;
            } else {
                $runningBalance -= (float) $t->amount;
            }
            $txList[] = [
                'id'               => $t->id,
                'type'             => $t->type->label(),
                'type_value'       => $t->type->value,
                'transaction_date' => $t->transaction_date->format('d/m/Y'),
                'description'      => $t->description,
                'amount'           => (float) $t->amount,
                'running_balance'  => $runningBalance,
                'has_proof'        => $t->hasProof(),
                'proof_name'       => $t->proof_original_name,
            ];
        }

        return Inertia::render('Pic/Disbursements/Show', [
            'disbursement' => [
                'id'              => $disbursement->id,
                'name'            => $disbursement->name,
                'purpose'         => $disbursement->purpose->label(),
                'purpose_value'   => $disbursement->purpose->value,
                'chairperson'     => $disbursement->chairperson,
                'start_date'      => $disbursement->start_date->format('d/m/Y'),
                'end_date'        => $disbursement->end_date->format('d/m/Y'),
                'start_date_raw'  => $disbursement->start_date->format('Y-m-d'),
                'end_date_raw'    => $disbursement->end_date->format('Y-m-d'),
                'allocation_name' => $disbursement->budgetAllocation->name,
                'amount'          => (float) $disbursement->amount,
                'remaining_funds' => $disbursement->remaining_funds,
                'total_expense'   => $disbursement->total_expense,
                'total_income'    => $disbursement->total_income,
                'realization_pct' => $disbursement->realization_percentage,
                'status'          => $disbursement->status,
                'status_label'    => $disbursement->status_label,
                'days_remaining'  => $disbursement->days_remaining,
                'is_active'       => $disbursement->isActive(),
                'transactions'    => array_reverse($txList),
            ],
        ]);
    }
}
