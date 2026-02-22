<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BudgetAllocation;
use App\Models\Disbursement;
use App\Models\Transaction;
use App\Services\ReportService;
use Illuminate\Http\Response;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function __construct(private ReportService $service) {}

    public function index(): \Inertia\Response
    {
        $totalBudget    = (float) BudgetAllocation::sum('amount');
        $totalDisbursed = (float) Disbursement::sum('amount');
        $totalExpense   = (float) Transaction::where('type', 'expense')->sum('amount');
        $totalIncome    = (float) Transaction::where('type', 'income')->sum('amount');

        // Monthly expense data for chart (last 6 months)
        $monthlyExpenses = Transaction::where('type', 'expense')
            ->selectRaw('DATE_FORMAT(transaction_date, "%Y-%m") as month, SUM(amount) as total')
            ->where('transaction_date', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->mapWithKeys(fn ($r) => [$r->month => (float) $r->total])
            ->toArray();

        // Top disbursements by expense
        $topDisbursements = Disbursement::with('pic')
            ->get()
            ->map(fn ($d) => [
                'name'            => $d->name,
                'pic_name'        => $d->pic->name,
                'amount'          => (float) $d->amount,
                'total_expense'   => $d->total_expense,
                'realization_pct' => $d->realization_percentage,
                'status'          => $d->status,
                'status_label'    => $d->status_label,
            ])
            ->sortByDesc('realization_pct')
            ->take(10)
            ->values()
            ->toArray();

        return Inertia::render('Admin/Reports/Index', [
            'stats' => [
                'total_budget'      => $totalBudget,
                'total_disbursed'   => $totalDisbursed,
                'remaining_budget'  => $totalBudget - $totalDisbursed,
                'total_expense'     => $totalExpense,
                'total_income'      => $totalIncome,
                'current_cash'      => $totalDisbursed + $totalIncome - $totalExpense,
            ],
            'monthly_expenses'   => $monthlyExpenses,
            'top_disbursements'  => $topDisbursements,
        ]);
    }

    public function globalPdf(): Response
    {
        $pdf = $this->service->generateGlobalPdf();
        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="laporan-global-' . now()->format('Ymd') . '.pdf"',
        ]);
    }
}
