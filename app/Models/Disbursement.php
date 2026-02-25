<?php

namespace App\Models;

use App\Enums\DisbursementPurpose;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Proposal;

class Disbursement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'purpose', 'name', 'start_date', 'end_date', 'pic_id',
        'chairperson', 'budget_allocation_id', 'amount',
        'auto_generate', 'parent_disbursement_id', 'created_by',
    ];

    protected $casts = [
        'purpose'       => DisbursementPurpose::class,
        'start_date'    => 'date',
        'end_date'      => 'date',
        'amount'        => 'decimal:2',
        'auto_generate' => 'boolean',
    ];

    /* ── Relations ────────────────────────────────────── */

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    public function budgetAllocation(): BelongsTo
    {
        return $this->belongsTo(BudgetAllocation::class, 'budget_allocation_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'disbursement_id');
    }

    public function parentDisbursement(): BelongsTo
    {
        return $this->belongsTo(Disbursement::class, 'parent_disbursement_id');
    }
    
    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class, 'proposal_id');
    }

    /* ── 4-Phase Status Lifecycle ─────────────────────────
     *
     *  NEW BUSINESS RULES (from task spec):
     *
     *  Phase 1 — Persiapan     : today < start_date
     *            Transactions ALLOWED (PIC can pre-register expenses)
     *
     *  Phase 2 — Aktif           : start_date ≤ today ≤ end_date
     *            Transactions ALLOWED
     *
     *  Phase 3 — Pelaporan (grace)
     *            end_date < today ≤ end_date + extra_transaction_days
     *            Transactions ALLOWED — time to finalise the report
     *
     *  Phase 4 — Selesai         : today > end_date + extra_transaction_days
     *            Transactions DISABLED — period fully closed
     *
     *  allowsTransactions() is the SINGLE SOURCE OF TRUTH used by:
     *    • EnsureActivityActive middleware
     *    • TransactionPolicy::create / update / delete
     *    • Pic\TransactionController (all write actions)
     *    • Pic\DisbursementController (is_active prop to Vue)
     *    • TransactionRequest (date validation upper-bound)
     *
     * ─────────────────────────────────────────────────── */

    /** Phase 1 */
    public function isUpcoming(): bool
    {
        return now()->toDateString() < $this->start_date->toDateString();
    }

    /** Phase 2 */
    public function isActive(): bool
    {
        $today = now()->toDateString();
        return $today >= $this->start_date->toDateString()
            && $today <= $this->end_date->toDateString();
    }

    /** Phase 3 — grace period after end_date */
    public function isInGracePeriod(): bool
    {
        $today = now()->toDateString();

        if ($today <= $this->end_date->toDateString()) {
            return false; // still active, not in grace
        }

        $extra = Setting::extraTransactionDays();
        if ($extra <= 0) {
            return false; // no grace period configured
        }

        return $today <= $this->end_date->copy()->addDays($extra)->toDateString();
    }

    /** Phase 4 — fully closed; all three earlier phases have passed */
    public function isFullyExpired(): bool
    {
        return ! $this->isUpcoming()
            && ! $this->isActive()
            && ! $this->isInGracePeriod();
    }

    /**
     * The single authoritative gate:
     * TRUE  → Phases 1, 2, 3  (transactions allowed)
     * FALSE → Phase 4 only    (Selesai)
     *
     * Every controller / middleware / policy delegates here.
     * No other code should repeat this logic.
     */
    public function allowsTransactions(): bool
    {
        return ! $this->isFullyExpired();
    }

    /* ── Status Accessors ─────────────────────────────── */

    public function getStatusAttribute(): string
    {
        if ($this->isUpcoming())      return 'upcoming';
        if ($this->isActive())        return 'active';
        if ($this->isInGracePeriod()) return 'grace';
        return 'expired';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'upcoming' => 'Persiapan',
            'active'   => 'Aktif',
            'grace'    => 'Pelaporan',
            'expired'  => 'Selesai',
            default    => '-',
        };
    }

    /**
     * Days remaining in the active phase (0 outside that phase).
     */
    public function getDaysRemainingAttribute(): int
    {
        $today = now()->startOfDay();

        // Phase 1 — Persiapan
        if ($this->isUpcoming()) {
            return max(0, $today->diffInDays($this->start_date));
        }

        // Phase 2 — Aktif
        if ($this->isActive()) {
            return max(0, $today->diffInDays($this->end_date));
        }

        // Phase 3 — Pelaporan (grace)
        if ($this->isInGracePeriod()) {
            $deadline = $this->end_date
                ->copy()
                ->addDays(Setting::extraTransactionDays());

            return max(0, $today->diffInDays($deadline));
        }

        // Phase 4 — Selesai
        return 0;
    }

    /**
     * Earliest allowed transaction date.
     * - Allows real transactions during upcoming phase
     * - Prevents arbitrary backdating
     */
    public function getTransactionStartDateAttribute(): string
    {
        // Paling aman & audit-friendly:
        // transaksi tidak boleh sebelum pencairan dibuat
        return $this->created_at->toDateString();
    }

    /**
     * The last calendar date on which a transaction may be submitted.
     * = end_date + extra_transaction_days
     *
     * Used by TransactionRequest and by the Create/Edit Vue forms (:max on date input).
     */
    public function getTransactionDeadlineAttribute(): string
    {
        return $this->end_date
            ->copy()
            ->addDays(Setting::extraTransactionDays())
            ->toDateString();
    }

    /* ── Computed Financials ──────────────────────────── */

    public function getTotalExpenseAttribute(): float
    {
        return (float) $this->transactions()->where('type', 'expense')->sum('amount');
    }

    public function getTotalIncomeAttribute(): float
    {
        return (float) $this->transactions()->where('type', 'income')->sum('amount');
    }

    public function getRemainingFundsAttribute(): float
    {
        return (float) $this->amount + $this->total_income - $this->total_expense;
    }

    public function getRealizationPercentageAttribute(): float
    {
        if ((float) $this->amount === 0.0) return 0.0;
        return round(($this->total_expense / (float) $this->amount) * 100, 2);
    }

    public function getTransactionCountAttribute(): int
    {
        return $this->transactions()->count();
    }
}