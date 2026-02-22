<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\BudgetAllocation;
use App\Models\Disbursement;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        return match ($user->role) {
            UserRole::Superadmin => $this->superadminDashboard(),
            UserRole::Pic        => $this->picDashboard($user),
            UserRole::Auditor    => $this->auditorDashboard(),
            UserRole::Applicant  => $this->applicantDashboard($user),
            UserRole::Approver   => $this->approverDashboard($user),
            default              => $this->picDashboard($user),
        };
    }

    // ─────────────────────────────────────────────────────────
    //  SUPERADMIN
    // ─────────────────────────────────────────────────────────
    private function superadminDashboard(): Response
    {
        // ❌ FIX: eager-load withTrashed relationships so deleted PIC/allocation
        //         names never throw "Attempt to read property on null".
        $recentDisbursements = Disbursement::with([
            'pic:id,name',               // withTrashed() is set on the relation itself
            'budgetAllocation:id,name',
        ])
            ->withoutTrashed()
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($d) => [
                'id'              => $d->id,
                'name'            => $d->name,
                // ✅ Null-safe: uses model accessor that returns fallback string
                'pic_name'        => $d->pic_name,
                'allocation_name' => $d->allocation_name,
                'amount'          => $d->amount,
                'remaining_funds' => $d->remaining_funds,
                'realization_pct' => $d->realization_percentage,
                'status'          => $d->status,
                'status_label'    => $d->status_label,
                'days_remaining'  => $d->days_remaining,
                'purpose'         => $d->purpose->label(),
                'purpose_value'   => $d->purpose->value,
            ]);

        $recentTransactions = Transaction::with([
            'disbursement:id,name',  // safe — doesn't null-crash if disb is soft-deleted
            'creator:id,name',
        ])
            ->latest('transaction_date')
            ->take(8)
            ->get()
            ->map(fn ($t) => [
                'id'               => $t->id,
                'type'             => $t->type->label(),
                'type_value'       => $t->type->value,
                'description'      => $t->description,
                'amount'           => $t->amount,
                'transaction_date' => $t->transaction_date->format('d/m/Y'),
                // ✅ Null-safe — disbursement may be soft-deleted
                'disbursement_name' => $t->disbursement?->name ?? '(Pencairan dihapus)',
                'created_by_name'  => $t->creator?->name ?? '(Pengguna dihapus)',
            ]);

        // Budget stats
        $totalBudget    = (float) BudgetAllocation::sum('amount');
        $totalDisbursed = (float) Disbursement::withoutTrashed()->sum('amount');
        $totalExpense   = (float) Transaction::where('type', 'expense')->sum('amount');
        $totalIncome    = (float) Transaction::where('type', 'income')->sum('amount');

        // Monthly expense chart (last 6 months)
        $monthlyExpenses = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $label = $month->format('M Y');
            $monthlyExpenses[$label] = (float) Transaction::where('type', 'expense')
                ->whereYear('transaction_date',  $month->year)
                ->whereMonth('transaction_date', $month->month)
                ->sum('amount');
        }

        // Disbursement count by status
        $activeCount = Disbursement::withoutTrashed()
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->count();

        // Low-budget allocations (already shared in HandleInertiaRequests, but keep here for dashboard card)
        $lowBudgetCount = BudgetAllocation::withoutTrashed()->get()
            ->filter(fn ($a) => $a->utilization_percentage >= 80)
            ->count();

        return Inertia::render('Dashboard', [
            'role'                => 'superadmin',
            'stats' => [
                'total_budget'     => $totalBudget,
                'total_disbursed'  => $totalDisbursed,
                'remaining_budget' => $totalBudget - $totalDisbursed,
                'total_expense'    => $totalExpense,
                'total_income'     => $totalIncome,
                'current_cash'     => $totalDisbursed - $totalExpense + $totalIncome,
                'active_count'     => $activeCount,
                'low_budget_count' => $lowBudgetCount,
                'user_count'       => User::whereIn('role', ['pic', 'auditor', 'applicant', 'approver'])->count(),
            ],
            'monthly_expenses'    => $monthlyExpenses,
            'recent_disbursements' => $recentDisbursements,
            'recent_transactions'  => $recentTransactions,
        ]);
    }

    // ─────────────────────────────────────────────────────────
    //  PIC
    // ─────────────────────────────────────────────────────────
    private function picDashboard(User $user): Response
    {
        $disbursements = Disbursement::with(['budgetAllocation:id,name'])
            ->withoutTrashed()
            ->where('pic_id', $user->id)
            ->latest()
            ->take(6)
            ->get()
            ->map(fn ($d) => [
                'id'              => $d->id,
                'name'            => $d->name,
                'allocation_name' => $d->allocation_name,
                'amount'          => $d->amount,
                'remaining_funds' => $d->remaining_funds,
                'realization_pct' => $d->realization_percentage,
                'status'          => $d->status,
                'status_label'    => $d->status_label,
                'days_remaining'  => $d->days_remaining,
                'purpose'         => $d->purpose->label(),
                'purpose_value'   => $d->purpose->value,
            ]);

        $recentTransactions = Transaction::with(['disbursement:id,name'])
            ->where('created_by', $user->id)
            ->latest('transaction_date')
            ->take(5)
            ->get()
            ->map(fn ($t) => [
                'id'               => $t->id,
                'type'             => $t->type->label(),
                'type_value'       => $t->type->value,
                'description'      => $t->description,
                'amount'           => $t->amount,
                'transaction_date' => $t->transaction_date->format('d/m/Y'),
                'disbursement_name' => $t->disbursement?->name ?? '(Pencairan dihapus)',
            ]);

        $totalDisbursed = (float) Disbursement::where('pic_id', $user->id)->sum('amount');
        $totalExpense   = (float) Transaction::where('created_by', $user->id)
                              ->where('type', 'expense')->sum('amount');
        $activeCount    = Disbursement::where('pic_id', $user->id)
                              ->whereDate('start_date', '<=', today())
                              ->whereDate('end_date', '>=', today())
                              ->count();

        return Inertia::render('Dashboard', [
            'role'                => 'pic',
            'stats' => [
                'total_disbursed' => $totalDisbursed,
                'total_expense'   => $totalExpense,
                'active_count'    => $activeCount,
                'total_count'     => Disbursement::where('pic_id', $user->id)->count(),
            ],
            'disbursements'       => $disbursements,
            'recent_transactions' => $recentTransactions,
        ]);
    }

    // ─────────────────────────────────────────────────────────
    //  AUDITOR
    // ─────────────────────────────────────────────────────────
    private function auditorDashboard(): Response
    {
        $totalBudget  = (float) BudgetAllocation::sum('amount');
        $totalExpense = (float) Transaction::where('type', 'expense')->sum('amount');
        $totalIncome  = (float) Transaction::where('type', 'income')->sum('amount');

        $recentDisbursements = Disbursement::with(['pic:id,name', 'budgetAllocation:id,name'])
            ->withoutTrashed()->latest()->take(5)->get()
            ->map(fn ($d) => [
                'id'              => $d->id,
                'name'            => $d->name,
                'pic_name'        => $d->pic_name,
                'amount'          => $d->amount,
                'realization_pct' => $d->realization_percentage,
                'status'          => $d->status,
                'status_label'    => $d->status_label,
            ]);

        return Inertia::render('Dashboard', [
            'role'  => 'auditor',
            'stats' => [
                'total_budget'   => $totalBudget,
                'total_expense'  => $totalExpense,
                'total_income'   => $totalIncome,
                'current_cash'   => $totalBudget - $totalExpense + $totalIncome,
                'disbursement_count' => Disbursement::withoutTrashed()->count(),
            ],
            'recent_disbursements' => $recentDisbursements,
        ]);
    }

    // ─────────────────────────────────────────────────────────
    //  APPLICANT
    // ─────────────────────────────────────────────────────────
    private function applicantDashboard(User $user): Response
    {
        $proposals = \App\Models\Proposal::where('applicant_id', $user->id)
            ->latest()->take(5)->get()
            ->map(fn ($p) => [
                'id'              => $p->id,
                'code'            => $p->code,
                'name'            => $p->name,
                'status'          => $p->status->value,
                'status_label'    => $p->status->label(),
                'status_color'    => $p->status->color(),
                'proposed_amount' => $p->proposed_amount,
                'approved_amount' => $p->approved_amount,
                'created_at'      => $p->created_at->format('d/m/Y'),
            ]);

        return Inertia::render('Dashboard', [
            'role'      => 'applicant',
            'stats' => [
                'total'    => \App\Models\Proposal::where('applicant_id', $user->id)->count(),
                'approved' => \App\Models\Proposal::where('applicant_id', $user->id)->where('status', 'approved')->count(),
                'pending'  => \App\Models\Proposal::where('applicant_id', $user->id)->whereIn('status', ['draft','forwarded'])->count(),
            ],
            'proposals' => $proposals,
        ]);
    }

    // ─────────────────────────────────────────────────────────
    //  APPROVER
    // ─────────────────────────────────────────────────────────
    private function approverDashboard(User $user): Response
    {
        $pending = \App\Models\ProposalApprover::with(['proposal.applicant'])
            ->where('user_id', $user->id)
            ->whereNull('approved_at')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($pa) => [
                'id'           => $pa->proposal->id,
                'code'         => $pa->proposal->code,
                'name'         => $pa->proposal->name,
                'applicant'    => $pa->proposal->applicant?->name ?? '-',
                'proposed_amount' => $pa->proposal->proposed_amount,
            ]);

        return Inertia::render('Dashboard', [
            'role'  => 'approver',
            'stats' => [
                'pending_count'  => \App\Models\ProposalApprover::where('user_id', $user->id)->whereNull('approved_at')->count(),
                'approved_count' => \App\Models\ProposalApprover::where('user_id', $user->id)->whereNotNull('approved_at')->count(),
            ],
            'pending_proposals' => $pending,
        ]);
    }
}
