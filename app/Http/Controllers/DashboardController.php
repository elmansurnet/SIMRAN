<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\BudgetAllocation;
use App\Models\Disbursement;
use App\Models\Proposal;
use App\Models\ProposalApprover;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        /**
         * WHY @var annotation:
         *   $request->user() is typed as Authenticatable|null on Request.
         *   Intelephense sees only the interface and cannot resolve ->role (a User
         *   property). The @var tells the analyser the concrete type, enabling
         *   the match expression below to resolve all UserRole cases.
         *
         * @var User $user
         */
        $user = $request->user();

        // Auth middleware guarantees $user is non-null here, but guard it anyway.
        abort_if(! $user, 401);

        return match ($user->role) {
            UserRole::Superadmin => $this->superadminDashboard(),
            UserRole::Pic        => $this->picDashboard($user),
            UserRole::Auditor    => $this->auditorDashboard(),
            UserRole::Applicant  => $this->applicantDashboard($user),
            UserRole::Approver   => $this->approverDashboard($user),
        };
    }

    // ─────────────────────────────────────────────────────────
    //  SUPERADMIN
    // ─────────────────────────────────────────────────────────
    private function superadminDashboard(): Response
    {
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
                'pic_name'        => $d->pic_name,
                'allocation_name' => $d->allocation_name,
                'amount'          => (float) $d->amount,
                'remaining_funds' => (float) $d->remaining_funds,
                'realization_pct' => (float) $d->realization_percentage,
                'status'          => $d->status,
                'status_label'    => $d->status_label,
                'days_remaining'  => (int) $d->days_remaining,
                'purpose'         => $d->purpose->label(),
                'purpose_value'   => $d->purpose->value,
            ]);

        $recentTransactions = Transaction::with([
            'disbursement:id,name',
            'creator:id,name',
        ])
            ->latest('transaction_date')
            ->take(8)
            ->get()
            ->map(fn ($t) => [
                'id'                => $t->id,
                'type'              => $t->type->label(),
                'type_value'        => $t->type->value,
                'description'       => $t->description,
                'amount'            => (float) $t->amount,
                'transaction_date'  => $t->transaction_date->format('d/m/Y'),
                'disbursement_name' => $t->disbursement?->name ?? '(Pencairan dihapus)',
                'created_by_name'   => $t->creator?->name    ?? '(Pengguna dihapus)',
            ]);

        $totalBudget    = (float) BudgetAllocation::sum('amount');
        $totalDisbursed = (float) Disbursement::withoutTrashed()->sum('amount');
        $totalExpense   = (float) Transaction::where('type', 'expense')->sum('amount');
        $totalIncome    = (float) Transaction::where('type', 'income')->sum('amount');

        // Monthly expense chart (last 6 months) — always return floats, never null
        $monthlyExpenses = [];
        for ($i = 5; $i >= 0; $i--) {
            $month   = now()->subMonths($i);
            $label   = $month->format('M Y');
            $monthlyExpenses[$label] = (float) Transaction::where('type', 'expense')
                ->whereYear('transaction_date',  $month->year)
                ->whereMonth('transaction_date', $month->month)
                ->sum('amount');
        }

        $activeCount    = (int) Disbursement::withoutTrashed()
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->count();

        $lowBudgetCount = (int) BudgetAllocation::withoutTrashed()->get()
            ->filter(fn ($a) => $a->utilization_percentage >= 80)
            ->count();

        return Inertia::render('Dashboard', [
            'role'  => 'superadmin',
            'stats' => [
                'total_budget'     => $totalBudget,
                'total_disbursed'  => $totalDisbursed,
                'remaining_budget' => $totalBudget - $totalDisbursed,
                'total_expense'    => $totalExpense,
                'total_income'     => $totalIncome,
                'current_cash'     => $totalDisbursed - $totalExpense + $totalIncome,
                'active_count'     => $activeCount,
                'low_budget_count' => $lowBudgetCount,
                'user_count'       => (int) User::whereIn('role', ['pic', 'auditor', 'applicant', 'approver'])->count(),
            ],
            'monthly_expenses'     => $monthlyExpenses,
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
                'amount'          => (float) $d->amount,
                'remaining_funds' => (float) $d->remaining_funds,
                'realization_pct' => (float) $d->realization_percentage,
                'status'          => $d->status,
                'status_label'    => $d->status_label,
                'days_remaining'  => (int) $d->days_remaining,
                'purpose'         => $d->purpose->label(),
                'purpose_value'   => $d->purpose->value,
            ]);

        $recentTransactions = Transaction::with(['disbursement:id,name'])
            ->where('created_by', $user->id)
            ->latest('transaction_date')
            ->take(5)
            ->get()
            ->map(fn ($t) => [
                'id'                => $t->id,
                'type'              => $t->type->label(),
                'type_value'        => $t->type->value,
                'description'       => $t->description,
                'amount'            => (float) $t->amount,
                'transaction_date'  => $t->transaction_date->format('d/m/Y'),
                'disbursement_name' => $t->disbursement?->name ?? '(Pencairan dihapus)',
            ]);

        $totalDisbursed = (float) Disbursement::where('pic_id', $user->id)->sum('amount');
        $totalExpense   = (float) Transaction::where('created_by', $user->id)->where('type', 'expense')->sum('amount');
        $activeCount    = (int) Disbursement::where('pic_id', $user->id)
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->count();

        return Inertia::render('Dashboard', [
            'role'  => 'pic',
            'stats' => [
                'total_disbursed' => $totalDisbursed,
                'total_expense'   => $totalExpense,
                'active_count'    => $activeCount,
                'total_count'     => (int) Disbursement::where('pic_id', $user->id)->count(),
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
                'amount'          => (float) $d->amount,
                'realization_pct' => (float) $d->realization_percentage,
                'status'          => $d->status,
                'status_label'    => $d->status_label,
            ]);

        return Inertia::render('Dashboard', [
            'role'  => 'auditor',
            'stats' => [
                'total_budget'       => $totalBudget,
                'total_expense'      => $totalExpense,
                'total_income'       => $totalIncome,
                'current_cash'       => $totalBudget - $totalExpense + $totalIncome,
                'disbursement_count' => (int) Disbursement::withoutTrashed()->count(),
            ],
            'recent_disbursements' => $recentDisbursements,
        ]);
    }

    // ─────────────────────────────────────────────────────────
    //  APPLICANT
    // ─────────────────────────────────────────────────────────
    private function applicantDashboard(User $user): Response
    {
        $proposals = Proposal::where('applicant_id', $user->id)
            ->latest()->take(5)->get()
            ->map(fn ($p) => [
                'id'              => $p->id,
                'code'            => $p->code,
                'name'            => $p->name,
                'status'          => $p->status->value,
                'status_label'    => $p->status->label(),
                'status_color'    => $p->status->color(),
                'proposed_amount' => (float) $p->proposed_amount,
                'approved_amount' => $p->approved_amount !== null ? (float) $p->approved_amount : null,
                'created_at'      => $p->created_at->format('d/m/Y'),
            ]);

        return Inertia::render('Dashboard', [
            'role'  => 'applicant',
            'stats' => [
                'total'    => (int) Proposal::where('applicant_id', $user->id)->count(),
                'approved' => (int) Proposal::where('applicant_id', $user->id)->where('status', 'approved')->count(),
                'pending'  => (int) Proposal::where('applicant_id', $user->id)->whereIn('status', ['draft', 'forwarded'])->count(),
            ],
            'proposals' => $proposals,
        ]);
    }

    // ─────────────────────────────────────────────────────────
    //  APPROVER
    // ─────────────────────────────────────────────────────────
    private function approverDashboard(User $user): Response
    {
        $pending = ProposalApprover::with(['proposal.applicant'])
            ->where('user_id', $user->id)
            ->whereNull('approved_at')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($pa) => [
                'id'              => $pa->proposal->id,
                'code'            => $pa->proposal->code,
                'name'            => $pa->proposal->name,
                'applicant'       => $pa->proposal->applicant?->name ?? '-',
                'proposed_amount' => (float) $pa->proposal->proposed_amount,
            ]);

        return Inertia::render('Dashboard', [
            'role'  => 'approver',
            'stats' => [
                'pending_count'  => (int) ProposalApprover::where('user_id', $user->id)->whereNull('approved_at')->count(),
                'approved_count' => (int) ProposalApprover::where('user_id', $user->id)->whereNotNull('approved_at')->count(),
            ],
            'pending_proposals' => $pending,
        ]);
    }
}