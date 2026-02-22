<?php

namespace App\Models;

use App\Enums\ProposalStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proposal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'status', 'applicant_id', 'purpose', 'name',
        'start_date', 'end_date', 'pic_id', 'chairperson',
        'proposed_amount', 'approved_amount',
        'proposal_pdf_path', 'certificate_pdf_path',
        'reviewed_by', 'forwarded_at', 'superadmin_note', 'approved_at',
    ];

    protected $casts = [
        'status'          => ProposalStatus::class,
        'start_date'      => 'date',
        'end_date'        => 'date',
        'proposed_amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'forwarded_at'    => 'datetime',
        'approved_at'     => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approvers(): HasMany
    {
        return $this->hasMany(ProposalApprover::class);
    }

    public function disbursement(): HasOne
    {
        return $this->hasOne(Disbursement::class);
    }

    // ── Helpers ───────────────────────────────────────

    public function isDraft(): bool     { return $this->status === ProposalStatus::Draft; }
    public function isForwarded(): bool { return $this->status === ProposalStatus::Forwarded; }
    public function isApproved(): bool  { return $this->status === ProposalStatus::Approved; }
    public function isRejected(): bool  { return $this->status === ProposalStatus::Rejected; }

    /** All assigned approvers have approved */
    public function allApproved(): bool
    {
        return $this->approvers()->whereNull('approved_at')->doesntExist()
            && $this->approvers()->exists();
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status->label();
    }

    public function getPurposeLabelAttribute(): string
    {
        return match ($this->purpose) {
            'activity'    => 'Kegiatan',
            'operational' => 'Operasional',
            default       => $this->purpose,
        };
    }

    /**
     * Auto-generate proposal code: PROP-{YEAR}-{SEQUENCE}
     */
    public static function generateCode(): string
    {
        $year  = now()->format('Y');
        $count = static::whereYear('created_at', $year)->withTrashed()->count() + 1;
        return sprintf('PROP-%s-%04d', $year, $count);
    }
}
