<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DisbursementPurpose;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\BudgetAllocation;
use App\Models\Disbursement;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DisbursementController extends Controller
{
    public function index(Request $request): Response
    {
        $disbursements = Disbursement::with(['pic:id,name', 'budgetAllocation:id,name'])
            ->withoutTrashed()
            ->when($request->search, fn ($q, $s) =>
                $q->where('name', 'like', "%{$s}%")
            )
            ->when($request->type, fn ($q, $t) => $q->where('purpose', $t))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn ($d) => $this->row($d));

        return Inertia::render('Admin/Disbursements/Index', [
            'disbursements' => $disbursements,
            'filters'       => $request->only('search', 'type'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Disbursements/Create', [
            'allocations'       => BudgetAllocation::withoutTrashed()
                                    ->get(['id', 'name', 'amount'])
                                    ->map(fn ($a) => [
                                        'id'               => $a->id,
                                        'name'             => $a->name,
                                        'remaining_amount' => $a->remaining_amount,
                                    ]),
            'pics'              => User::where('role', UserRole::Pic)->get(['id', 'name']),
            'approvedProposals' => $this->approvedProposalList(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateDisbursement($request);

        Disbursement::create([
            ...$validated,
            // ✅ Auth::id() instead of auth()->id() — static analysis fix
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.disbursements.index')
            ->with('success', 'Pencairan berhasil dibuat.');
    }

    public function show(Disbursement $disbursement): Response
    {
        $disbursement->load(['transactions.creator', 'pic', 'budgetAllocation', 'proposal']);

        $transactions = $disbursement->transactions()
            ->orderBy('transaction_date')
            ->orderBy('created_at')
            ->get();

        $runningBalance = (float) $disbursement->amount;
        $txPayload = $transactions->map(function ($t) use (&$runningBalance) {
            $runningBalance += $t->type->value === 'income' ? $t->amount : -$t->amount;
            return [
                'id'               => $t->id,
                'type'             => $t->type->label(),
                'type_value'       => $t->type->value,
                'description'      => $t->description,
                'amount'           => (float) $t->amount,
                'transaction_date' => $t->transaction_date->format('d/m/Y'),
                'running_balance'  => $runningBalance,
                'created_by_name'  => $t->creator?->name ?? '(dihapus)',
                'has_proof'        => $t->hasProof(),
                'proof_name'       => $t->proof_original_name,
            ];
        });

        return Inertia::render('Admin/Disbursements/Show', [
            'disbursement' => [
                'id'                   => $disbursement->id,
                'name'                 => $disbursement->name,
                'purpose'              => $disbursement->purpose->label(),
                'purpose_value'        => $disbursement->purpose->value,
                'chairperson'          => $disbursement->chairperson,
                'pic_name'             => $disbursement->pic_name,
                'allocation_name'      => $disbursement->allocation_name,
                'amount'               => (float) $disbursement->amount,
                'total_expense'        => $disbursement->total_expense,
                'total_income'         => $disbursement->total_income,
                'remaining_funds'      => $disbursement->remaining_funds,
                'realization_pct'      => $disbursement->realization_percentage,
                'status'               => $disbursement->status,
                'status_label'         => $disbursement->status_label,
                'days_remaining'       => $disbursement->days_remaining,
                'start_date'           => $disbursement->start_date->format('d M Y'),
                'end_date'             => $disbursement->end_date->format('d M Y'),
                'is_active'            => $disbursement->allowsTransactions(),
                'from_proposal'        => $disbursement->proposal?->code,
                // ✅ expose deletion ability to UI
                'can_delete'           => ! $disbursement->transactions()->exists(),
                'transactions'         => $txPayload,
            ],
        ]);
    }

    public function edit(Disbursement $disbursement): Response
    {
        return Inertia::render('Admin/Disbursements/Create', [
            'editDisbursement'  => [
                'id'                   => $disbursement->id,
                'purpose'              => $disbursement->purpose->value,
                'budget_allocation_id' => $disbursement->budget_allocation_id,
                'name'                 => $disbursement->name,
                'chairperson'          => $disbursement->chairperson,
                'pic_id'               => $disbursement->pic_id,
                'amount'               => $disbursement->amount,
                'start_date'           => $disbursement->start_date->format('Y-m-d'),
                'end_date'             => $disbursement->end_date->format('Y-m-d'),
                'proposal_id'          => $disbursement->proposal_id,
            ],
            'allocations'       => BudgetAllocation::withoutTrashed()
                                    ->get(['id', 'name', 'amount'])
                                    ->map(fn ($a) => [
                                        'id'               => $a->id,
                                        'name'             => $a->name,
                                        'remaining_amount' => $a->remaining_amount,
                                    ]),
            'pics'              => User::where('role', UserRole::Pic)->get(['id', 'name']),
            'approvedProposals' => $this->approvedProposalList(),
        ]);
    }

    public function update(Request $request, Disbursement $disbursement): RedirectResponse
    {
        $validated = $this->validateDisbursement($request);
        $disbursement->update($validated);

        return redirect()->route('admin.disbursements.show', $disbursement)
            ->with('success', 'Pencairan berhasil diperbarui.');
    }

    public function destroy(Disbursement $disbursement): RedirectResponse
    {
        // ✅ PART D – BUSINESS CONSTRAINT:
        //    A disbursement with existing transactions MUST NOT be deleted.
        //    This prevents orphaned transaction records and data loss.
        //    Enforced at controller level in addition to DB foreign key constraint.
        if ($disbursement->transactions()->exists()) {
            $count = $disbursement->transactions()->count();
            return back()->with(
                'error',
                "Pencairan '{$disbursement->name}' tidak dapat dihapus karena memiliki {$count} transaksi. Hapus semua transaksi terlebih dahulu."
            );
        }

        $disbursement->delete();

        return redirect()->route('admin.disbursements.index')
            ->with('success', 'Pencairan berhasil dihapus.');
    }

    /** API-like endpoint: return approved proposals for JS fetch in Create form */
    public function approvedProposals(): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->approvedProposalList());
    }

    // ── Private helpers ───────────────────────────────

    private function row(Disbursement $d): array
    {
        return [
            'id'              => $d->id,
            'name'            => $d->name,
            'purpose'         => $d->purpose->label(),
            'purpose_value'   => $d->purpose->value,
            'pic_name'        => $d->pic_name,
            'allocation_name' => $d->allocation_name,
            'amount'          => (float) $d->amount,
            'remaining_funds' => $d->remaining_funds,
            'realization_pct' => $d->realization_percentage,
            'status'          => $d->status,
            'status_label'    => $d->status_label,
            'start_date'      => $d->start_date->format('d/m/Y'),
            'end_date'        => $d->end_date->format('d/m/Y'),
            'days_remaining'  => $d->days_remaining,
            // ✅ expose can_delete in index list for UI disable logic
            'can_delete'      => ! $d->transactions()->exists(),
        ];
    }

    private function approvedProposalList(): array
    {
        return Proposal::where('status', 'approved')
            ->with('pic:id,name')
            ->whereDoesntHave('disbursement')
            ->latest()
            ->get()
            ->map(fn ($p) => [
                'id'              => $p->id,
                'code'            => $p->code,
                'name'            => $p->name,
                'chairperson'     => $p->chairperson,
                'pic_id'          => $p->pic_id,
                'pic_name'        => $p->pic?->name,
                'approved_amount' => $p->approved_amount,
                'start_date'      => $p->start_date->format('Y-m-d'),
                'end_date'        => $p->end_date->format('Y-m-d'),
            ])
            ->all();
    }

    private function validateDisbursement(Request $request): array
    {
        return $request->validate([
            'purpose'              => ['required', Rule::in(['activity', 'operational'])],
            'budget_allocation_id' => ['required', 'exists:budget_allocations,id'],
            'name'                 => ['required', 'string', 'max:255'],
            'chairperson'          => ['required', 'string', 'max:255'],
            'pic_id'               => ['required', 'exists:users,id'],
            'amount'               => ['required', 'numeric', 'min:1'],
            'start_date'           => ['required', 'date'],
            'end_date'             => ['required', 'date', 'after_or_equal:start_date'],
            'proposal_id'          => ['nullable', 'exists:proposals,id'],
        ]);
    }
}