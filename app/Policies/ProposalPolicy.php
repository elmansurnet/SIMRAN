<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Proposal;
use App\Models\User;

class ProposalPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [UserRole::Superadmin, UserRole::Auditor], true);
    }

    public function view(User $user, Proposal $proposal): bool
    {
        return match ($user->role) {
            UserRole::Superadmin, UserRole::Auditor => true,
            UserRole::Applicant => $proposal->applicant_id === $user->id,
            UserRole::Approver  => $proposal->approvers()->where('user_id', $user->id)->exists(),
            default             => false,
        };
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Applicant;
    }

    public function forward(User $user, Proposal $proposal): bool
    {
        return $user->role === UserRole::Superadmin && $proposal->isDraft();
    }

    public function reject(User $user, Proposal $proposal): bool
    {
        return $user->role === UserRole::Superadmin && $proposal->isDraft();
    }

    public function approve(User $user, Proposal $proposal): bool
    {
        if ($user->role !== UserRole::Approver) return false;
        if (! $proposal->isForwarded()) return false;
        return $proposal->approvers()->where('user_id', $user->id)->whereNull('approved_at')->exists();
    }
}
