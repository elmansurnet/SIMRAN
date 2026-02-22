<?php

namespace App\Services;

use App\Models\Disbursement;

class DisbursementService
{
    public function __construct(private BudgetAllocationService $budgetService) {}

    public function create(array $data, int $userId): Disbursement
    {
        $disbursement = Disbursement::create(array_merge($data, ['created_by' => $userId]));

        if ($disbursement->auto_generate) {
            $this->autoGenerateNext($disbursement);
        }

        return $disbursement;
    }

    public function update(Disbursement $disbursement, array $data): Disbursement
    {
        $disbursement->update($data);
        return $disbursement->fresh();
    }

    public function delete(Disbursement $disbursement): void
    {
        $disbursement->delete();
    }

    public function remainingForAllocation(int $allocationId, ?int $excludeId = null): float
    {
        return $this->budgetService->remainingBudget($allocationId, $excludeId);
    }

    public function autoGenerateNext(Disbursement $d): Disbursement
    {
        $days = $d->start_date->diffInDays($d->end_date) + 1;

        return Disbursement::create([
            'purpose'                => $d->purpose->value,
            'name'                   => $d->name,
            'start_date'             => $d->end_date->copy()->addDay(),
            'end_date'               => $d->end_date->copy()->addDays($days),
            'pic_id'                 => $d->pic_id,
            'chairperson'            => $d->chairperson,
            'budget_allocation_id'   => $d->budget_allocation_id,
            'amount'                 => $d->amount,
            'auto_generate'          => false,
            'parent_disbursement_id' => $d->id,
            'created_by'             => $d->created_by,
        ]);
    }
}
