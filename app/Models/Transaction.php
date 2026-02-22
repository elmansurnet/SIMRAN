<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'disbursement_id', 'created_by', 'type',
        'transaction_date', 'description', 'amount',
        'proof_path', 'proof_original_name',
    ];

    protected $casts = [
        'type'             => TransactionType::class,
        'transaction_date' => 'date',
        'amount'           => 'decimal:2',
    ];

    /* ── Relations ────────────────────────────────────── */
    public function disbursement(): BelongsTo
    {
        return $this->belongsTo(Disbursement::class, 'disbursement_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /* ── Helpers ──────────────────────────────────────── */
    public function hasProof(): bool
    {
        return ! is_null($this->proof_path);
    }

    public function getSignedAmountAttribute(): float
    {
        return $this->type === TransactionType::Expense
            ? -(float) $this->amount
            : (float) $this->amount;
    }
}
