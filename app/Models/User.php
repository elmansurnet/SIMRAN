<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = ['name', 'email', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'role'              => UserRole::class,
    ];

    /* ── Role helpers ─────────────────────────────────── */
    public function isSuperadmin(): bool { return $this->role === UserRole::Superadmin; }
    public function isPic(): bool        { return $this->role === UserRole::Pic; }
    public function isAuditor(): bool    { return $this->role === UserRole::Auditor; }

    /* ── Relations ────────────────────────────────────── */
    public function disbursementsAsPic(): HasMany
    {
        return $this->hasMany(Disbursement::class, 'pic_id');
    }

    public function createdBudgetAllocations(): HasMany
    {
        return $this->hasMany(BudgetAllocation::class, 'created_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'created_by');
    }
}
