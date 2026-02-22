<?php

namespace App\Models;

use App\Enums\DisbursementPurpose;
use App\Services\SettingService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Disbursement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'purpose', 'name', 'start_date', 'end_date',
        'pic_id', 'chairperson',
        'budget_allocation_id', 'amount',
        'auto_generate', 'parent_disbursement_id',
        'created_by', 'proposal_id',
    ];

    protected $casts = [
        'start_date'    => 'date',
        'end_date'      => 'date',
        'amount'        => 'decimal:2',
        'auto_generate' => 'boolean',
        'purpose'       => DisbursementPurpose::class,
    ];

    // ── Relationships ─────────────────────────────────

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id')->withTrashed();
    }

    public function budgetAllocation(): BelongsTo
    {
        return $this->belongsTo(BudgetAllocation::class)->withTrashed();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function parentDisbursement(): BelongsTo
    {
        return $this->belongsTo(Disbursement::class, 'parent_disbursement_id')->withTrashed();
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class)->withTrashed();
    }

    // ── Status helpers ────────────────────────────────

    /** Within the strict activity period */
    public function isActive(): bool
    {
        return now()->between($this->start_date, $this->end_date);
    }

    public function isExpired(): bool
    {
        return now()->isAfter($this->end_date);
    }

    public function isUpcoming(): bool
    {
        return now()->isBefore($this->start_date);
    }

    /**
     * Within the grace period (extra_transaction_days after end_date).
     * Transactions are still allowed.
     */
    public function isInGracePeriod(): bool
    {
        if (! $this->isExpired()) {
            return false;
        }
        $extra    = Setting::extraTransactionDays();
        $deadline = $this->end_date->copy()->addDays($extra);
        return now()->lte($deadline);
    }

    /**
     * Transactions are allowed during active OR grace period.
     */
    public function allowsTransactions(): bool
    {
        return $this->isActive() || $this->isInGracePeriod();
    }

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
            'upcoming' => 'Akan Datang',
            'active'   => 'Aktif',
            'grace'    => 'Persiapan Laporan',
            default    => 'Selesai',
        };
    }

    public function getDaysRemainingAttribute(): int
    {
        if ($this->isActive()) {
            return (int) now()->diffInDays($this->end_date, false);
        }
        return 0;
    }

    // ── Financial computations ────────────────────────

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
        return (float) $this->amount - $this->total_expense + $this->total_income;
    }

    public function getRealizationPercentageAttribute(): float
    {
        if ($this->amount == 0) return 0;
        return round(($this->total_expense / $this->amount) * 100, 2);
    }

    // ── Safe accessors for null-safe dashboard use ────

    public function getPicNameAttribute(): string
    {
        return $this->pic?->name ?? '(PIC dihapus)';
    }

    public function getAllocationNameAttribute(): string
    {
        return $this->budgetAllocation?->name ?? '(Alokasi dihapus)';
    }
}
