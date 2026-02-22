<?php

namespace App\Services;

use App\Models\BudgetAllocation;
use Carbon\Carbon;

class BudgetAllocationService
{
    public function create(array $data, int $userId): BudgetAllocation
    {
        $allocation = BudgetAllocation::create(array_merge($data, ['created_by' => $userId]));

        if ($allocation->auto_generate) {
            $this->autoGenerateNext($allocation);
        }

        return $allocation;
    }

    public function update(BudgetAllocation $allocation, array $data): BudgetAllocation
    {
        $allocation->update($data);
        return $allocation->fresh();
    }

    public function delete(BudgetAllocation $allocation): void
    {
        $allocation->delete();
    }

    public function remainingBudget(int $allocationId, ?int $excludeDisbursementId = null): float
    {
        $allocation = BudgetAllocation::findOrFail($allocationId);

        $used = $allocation->disbursements()
            ->whereNull('deleted_at')
            ->when($excludeDisbursementId, fn ($q) => $q->where('id', '!=', $excludeDisbursementId))
            ->sum('amount');

        return max(0.0, (float) $allocation->amount - (float) $used);
    }

    public function autoGenerateNext(BudgetAllocation $allocation): BudgetAllocation
    {
        $start = $allocation->start_date;
        $end   = $allocation->end_date;
        $days  = $start->diffInDays($end) + 1;

        return BudgetAllocation::create([
            'name'                 => $allocation->name,
            'type'                 => $allocation->type->value,
            'start_date'           => $end->copy()->addDay(),
            'end_date'             => $end->copy()->addDays($days),
            'amount'               => $allocation->amount,
            'description'          => $allocation->description,
            'auto_generate'        => false,
            'parent_allocation_id' => $allocation->id,
            'created_by'           => $allocation->created_by,
        ]);
    }
}
