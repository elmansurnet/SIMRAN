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
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        // ✅ @var annotation: $request->user() returns Authenticatable|null.
        //    Without this, Intelephense reports "Undefined property 'role'" because
        //    the Authenticatable interface has no typed properties.
        /** @var \App\Models\User $user */
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
        $recentDisbursements = Disbursement::with([
            'pic:id,name',
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
                'remaining_funds' => $d->remaining_funds,
                // ✅ Both keys: 'realization_pct' for active-disbursements section,
                //    'realization' for the recent-disbursements list (dashboard crash fix).
                'realization_pct' => $d->realization_percentage,
                'realization'     => $d->realization_percentage,
                'status'          => $d->status,
                'status_label'    => $d->status_label,
                'days_remaining'  => $d->days_remaining,
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
                'id'               => $t->id,
                'type'             => $t->type->label(),
                'type_value'       => $t->type->value,
                'description'      => $t->description,
                'amount'           => (float) $t->amount,
                // ✅ 'date' key: Dashboard.vue accesses t.date (not t.transaction_date)
                'date'             => $t->transaction_date->format('d/m/Y'),
                'transaction_date' => $t->transaction_date->format('d/m/Y'),
                'disbursement_name' => $t->disbursement?->name ?? '(Pencairan dihapus)',
                'created_by_name'  => $t->creator?->name ?? '(Pengguna dihapus)',
            ]);

        $totalBudget    = (float) BudgetAllocation::sum('amount');
        $totalDisbursed = (float) Disbursement::withoutTrashed()->sum('amount');
        $totalExpense   = (float) Transaction::where('type', 'expense')->sum('amount');
        $totalIncome    = (float) Transaction::where('type', 'income')->sum('amount');

        // ✅ chart_data: Vue expects array of {month, expense, income}.
        //    Previously sent as a keyed object ('monthly_expenses') which Vue
        //    cannot iterate with .map(d => d.month).
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $chartData[] = [
                'month'   => $month->format('M Y'),
                'expense' => (float) Transaction::where('type', 'expense')
                    ->whereYear('transaction_date', $month->year)
                    ->whereMonth('transaction_date', $month->month)
                    ->sum('amount'),
                'income'  => (float) Transaction::where('type', 'income')
                    ->whereYear('transaction_date', $month->year)
                    ->whereMonth('transaction_date', $month->month)
                    ->sum('amount'),
            ];
        }

        $activeCount = Disbursement::withoutTrashed()
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->count();

        $totalDisbursements = Disbursement::withoutTrashed()->count();

        return Inertia::render('Dashboard', [
            'role'       => 'superadmin',
            // ✅ Stat keys now match Dashboard.vue exactly
            'stats' => [
                'total_budget'        => $totalBudget,
                'total_disbursed'     => $totalDisbursed,
                'remaining_budget'    => $totalBudget - $totalDisbursed,
                'total_expense'       => $totalExpense,
                'total_income'        => $totalIncome,
                'current_cash'        => $totalDisbursed - $totalExpense + $totalIncome,
                // ✅ renamed: active_count → active_disbursements (Vue expects this key)
                'active_disbursements' => $activeCount,
                // ✅ renamed: user_count → total_users (Vue expects this key)
                'total_users'         => User::whereIn('role', ['pic', 'auditor', 'applicant', 'approver'])->count(),
                // ✅ added: Vue shows stats.total_disbursements in third StatCard row
                'total_disbursements' => $totalDisbursements,
            ],
            // ✅ renamed: monthly_expenses → chart_data with array format
            'chart_data'           => $chartData,
            'recent_disbursements' => $recentDisbursements,
            'recent_transactions'  => $recentTransactions,
        ]);
    }

    // ─────────────────────────────────────────────────────────
    //  PIC
    // ─────────────────────────────────────────────────────────
    private function picDashboard(User $user): Response
    {
        // ✅ renamed key: disbursements → active_disbursements (Vue uses active_disbursements)
        $activeDisbursements = Disbursement::with(['budgetAllocation:id,name'])
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
                'remaining_funds' => $d->remaining_funds,
                'realization_pct' => $d->realization_percentage,
                'realization'     => $d->realization_percentage,
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
                'amount'           => (float) $t->amount,
                'date'             => $t->transaction_date->format('d/m/Y'),
                'transaction_date' => $t->transaction_date->format('d/m/Y'),
                'disbursement_name' => $t->disbursement?->name ?? '(Pencairan dihapus)',
            ]);

        $totalDisbursed = (float) Disbursement::where('pic_id', $user->id)->sum('amount');
        $totalExpense   = (float) Transaction::where('created_by', $user->id)->where('type', 'expense')->sum('amount');
        // ✅ added: total_income was missing, Vue uses it for StatCard
        $totalIncome    = (float) Transaction::where('created_by', $user->id)->where('type', 'income')->sum('amount');
        $activeCount    = Disbursement::where('pic_id', $user->id)
                              ->whereDate('start_date', '<=', today())
                              ->whereDate('end_date', '>=', today())
                              ->count();

        // ✅ added: chart_data for PIC line chart (6-month expense trend)
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $chartData[] = [
                'month'   => $month->format('M Y'),
                'expense' => (float) Transaction::where('created_by', $user->id)
                    ->where('type', 'expense')
                    ->whereYear('transaction_date', $month->year)
                    ->whereMonth('transaction_date', $month->month)
                    ->sum('amount'),
                'income'  => 0.0,
            ];
        }

        return Inertia::render('Dashboard', [
            'role'       => 'pic',
            'stats' => [
                'total_disbursed'     => $totalDisbursed,
                'total_expense'       => $totalExpense,
                // ✅ added: total_income missing previously
                'total_income'        => $totalIncome,
                // ✅ added: remaining_funds was missing, Vue uses it
                'remaining_funds'     => $totalDisbursed - $totalExpense + $totalIncome,
                // ✅ renamed: active_count → active_disbursements
                'active_disbursements' => $activeCount,
                // ✅ renamed: total_count → total_disbursements
                'total_disbursements' => Disbursement::where('pic_id', $user->id)->count(),
            ],
            'chart_data'           => $chartData,
            // ✅ renamed: disbursements → active_disbursements
            'active_disbursements' => $activeDisbursements,
            'recent_transactions'  => $recentTransactions,
        ]);
    }

    // ─────────────────────────────────────────────────────────
    //  AUDITOR
    // ─────────────────────────────────────────────────────────
    private function auditorDashboard(): Response
    {
        $totalBudget    = (float) BudgetAllocation::sum('amount');
        $totalDisbursed = (float) Disbursement::withoutTrashed()->sum('amount');
        $totalExpense   = (float) Transaction::where('type', 'expense')->sum('amount');
        $totalIncome    = (float) Transaction::where('type', 'income')->sum('amount');

        $recentDisbursements = Disbursement::with(['pic:id,name', 'budgetAllocation:id,name'])
            ->withoutTrashed()->latest()->take(5)->get()
            ->map(fn ($d) => [
                'id'              => $d->id,
                'name'            => $d->name,
                'pic_name'        => $d->pic_name,
                'amount'          => (float) $d->amount,
                // ✅ both keys for auditor dashboard
                'realization_pct' => $d->realization_percentage,
                'realization'     => $d->realization_percentage,
                'status'          => $d->status,
                'status_label'    => $d->status_label,
            ]);

        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $chartData[] = [
                'month'   => $month->format('M Y'),
                'expense' => (float) Transaction::where('type', 'expense')
                    ->whereYear('transaction_date', $month->year)
                    ->whereMonth('transaction_date', $month->month)
                    ->sum('amount'),
                'income'  => (float) Transaction::where('type', 'income')
                    ->whereYear('transaction_date', $month->year)
                    ->whereMonth('transaction_date', $month->month)
                    ->sum('amount'),
            ];
        }

        return Inertia::render('Dashboard', [
            'role'  => 'auditor',
            'stats' => [
                'total_budget'        => $totalBudget,
                // ✅ added: total_disbursed and remaining_budget were missing for auditor
                'total_disbursed'     => $totalDisbursed,
                'remaining_budget'    => $totalBudget - $totalDisbursed,
                'total_expense'       => $totalExpense,
                'total_income'        => $totalIncome,
                'current_cash'        => $totalBudget - $totalExpense + $totalIncome,
                'disbursement_count'  => Disbursement::withoutTrashed()->count(),
            ],
            'chart_data'           => $chartData,
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
                'approved_amount' => $p->approved_amount ? (float) $p->approved_amount : null,
                'created_at'      => $p->created_at->format('d/m/Y'),
            ]);

        return Inertia::render('Dashboard', [
            'role'      => 'applicant',
            'stats' => [
                'total'    => Proposal::where('applicant_id', $user->id)->count(),
                'approved' => Proposal::where('applicant_id', $user->id)->where('status', 'approved')->count(),
                'pending'  => Proposal::where('applicant_id', $user->id)->whereIn('status', ['draft', 'forwarded'])->count(),
                'rejected' => Proposal::where('applicant_id', $user->id)->where('status', 'rejected')->count(),
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
                'pending_count'  => ProposalApprover::where('user_id', $user->id)->whereNull('approved_at')->count(),
                'approved_count' => ProposalApprover::where('user_id', $user->id)->whereNotNull('approved_at')->count(),
                'total_count'    => ProposalApprover::where('user_id', $user->id)->count(),
            ],
            'pending_proposals' => $pending,
        ]);
    }
}