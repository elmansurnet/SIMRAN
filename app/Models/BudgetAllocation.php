<?php

namespace App\Models;

use App\Enums\BudgetType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetAllocation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'type', 'start_date', 'end_date', 'amount',
        'description', 'auto_generate', 'parent_allocation_id', 'created_by',
    ];

    protected $casts = [
        'type'          => BudgetType::class,
        'start_date'    => 'date',
        'end_date'      => 'date',
        'amount'        => 'decimal:2',
        'auto_generate' => 'boolean',
    ];

    /* ── Relations ────────────────────────────────────── */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function disbursements(): HasMany
    {
        return $this->hasMany(Disbursement::class, 'budget_allocation_id');
    }

    public function parentAllocation(): BelongsTo
    {
        return $this->belongsTo(BudgetAllocation::class, 'parent_allocation_id');
    }

    /* ── Computed Attributes ──────────────────────────── */
    public function getTotalDisbursedAttribute(): float
    {
        return (float) $this->disbursements()->whereNull('deleted_at')->sum('amount');
    }

    public function getRemainingAmountAttribute(): float
    {
        return (float) $this->amount - $this->total_disbursed;
    }

    public function getUtilizationPercentageAttribute(): float
    {
        if ((float) $this->amount === 0.0) return 0.0;
        return round(($this->total_disbursed / (float) $this->amount) * 100, 2);
    }

    public function isLowBudget(): bool
    {
        return $this->remaining_amount < ($this->amount * 0.15);
    }
}
