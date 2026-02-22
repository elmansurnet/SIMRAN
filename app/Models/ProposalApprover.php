<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProposalApprover extends Model
{
    protected $fillable = ['proposal_id', 'user_id', 'approved_at', 'note'];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hasApproved(): bool
    {
        return $this->approved_at !== null;
    }
}
