<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BudgetAllocationRequest;
use App\Models\BudgetAllocation;
use App\Services\BudgetAllocationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BudgetAllocationController extends Controller
{
    public function __construct(private BudgetAllocationService $service) {}

    public function index(Request $request): Response
    {
        $query = BudgetAllocation::with('creator')
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn ($qb) => $qb->where('name', 'like', "%{$q}%")
                ->orWhere('description', 'like', "%{$q}%"));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $allocations = $query->paginate(15)->withQueryString()
            ->through(fn ($a) => [
                'id'               => $a->id,
                'name'             => $a->name,
                'type'             => $a->type->label(),
                'type_value'       => $a->type->value,
                'start_date'       => $a->start_date->format('d/m/Y'),
                'end_date'         => $a->end_date->format('d/m/Y'),
                'amount'           => (float) $a->amount,
                'total_disbursed'  => $a->total_disbursed,
                'remaining'        => $a->remaining_amount,
                'utilization_pct'  => $a->utilization_percentage,
                'is_low_budget'    => $a->isLowBudget(),
                'description'      => $a->description,
                'auto_generate'    => $a->auto_generate,
                'creator_name'     => $a->creator->name,
                'created_at'       => $a->created_at->format('d/m/Y'),
            ]);

        return Inertia::render('Admin/BudgetAllocations/Index', [
            'allocations' => $allocations,
            'filters'     => $request->only(['search', 'type']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/BudgetAllocations/Form');
    }

    public function store(BudgetAllocationRequest $request): RedirectResponse
    {
        $this->service->create($request->validated(), $request->user()->id);
        return redirect()->route('admin.budget-allocations.index')
            ->with('success', 'Alokasi anggaran berhasil dibuat.');
    }

    public function show(BudgetAllocation $budgetAllocation): Response
    {
        $budgetAllocation->load(['creator', 'disbursements.pic']);

        $disbursements = $budgetAllocation->disbursements->map(fn ($d) => [
            'id'              => $d->id,
            'name'            => $d->name,
            'purpose'         => $d->purpose->label(),
            'pic_name'        => $d->pic->name,
            'start_date'      => $d->start_date->format('d/m/Y'),
            'end_date'        => $d->end_date->format('d/m/Y'),
            'amount'          => (float) $d->amount,
            'remaining_funds' => $d->remaining_funds,
            'realization_pct' => $d->realization_percentage,
            'status'          => $d->status,
            'status_label'    => $d->status_label,
        ]);

        return Inertia::render('Admin/BudgetAllocations/Show', [
            'allocation' => [
                'id'              => $budgetAllocation->id,
                'name'            => $budgetAllocation->name,
                'type'            => $budgetAllocation->type->label(),
                'type_value'      => $budgetAllocation->type->value,
                'start_date'      => $budgetAllocation->start_date->format('d/m/Y'),
                'end_date'        => $budgetAllocation->end_date->format('d/m/Y'),
                'amount'          => (float) $budgetAllocation->amount,
                'total_disbursed' => $budgetAllocation->total_disbursed,
                'remaining'       => $budgetAllocation->remaining_amount,
                'utilization_pct' => $budgetAllocation->utilization_percentage,
                'description'     => $budgetAllocation->description,
                'auto_generate'   => $budgetAllocation->auto_generate,
                'creator_name'    => $budgetAllocation->creator->name,
                'created_at'      => $budgetAllocation->created_at->format('d/m/Y'),
                'disbursements'   => $disbursements,
            ],
        ]);
    }

    public function edit(BudgetAllocation $budgetAllocation): Response
    {
        return Inertia::render('Admin/BudgetAllocations/Form', [
            'allocation' => [
                'id'            => $budgetAllocation->id,
                'name'          => $budgetAllocation->name,
                'type'          => $budgetAllocation->type->value,
                'start_date'    => $budgetAllocation->start_date->format('Y-m-d'),
                'end_date'      => $budgetAllocation->end_date->format('Y-m-d'),
                'amount'        => (float) $budgetAllocation->amount,
                'description'   => $budgetAllocation->description,
                'auto_generate' => $budgetAllocation->auto_generate,
            ],
        ]);
    }

    public function update(BudgetAllocationRequest $request, BudgetAllocation $budgetAllocation): RedirectResponse
    {
        $this->service->update($budgetAllocation, $request->validated());
        return redirect()->route('admin.budget-allocations.index')
            ->with('success', 'Alokasi anggaran berhasil diperbarui.');
    }

    public function destroy(BudgetAllocation $budgetAllocation): RedirectResponse
    {
        $this->service->delete($budgetAllocation);
        return redirect()->route('admin.budget-allocations.index')
            ->with('success', 'Alokasi anggaran berhasil dihapus.');
    }
}
