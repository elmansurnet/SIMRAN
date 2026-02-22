<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Disbursement;
use App\Models\User;

class DisbursementPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [UserRole::Superadmin, UserRole::Auditor], true);
    }

    public function view(User $user, Disbursement $disbursement): bool
    {
        if (in_array($user->role, [UserRole::Superadmin, UserRole::Auditor], true)) {
            return true;
        }
        return $user->role === UserRole::Pic && $disbursement->pic_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Superadmin;
    }

    public function update(User $user, Disbursement $disbursement): bool
    {
        return $user->role === UserRole::Superadmin;
    }

    public function delete(User $user, Disbursement $disbursement): bool
    {
        return $user->role === UserRole::Superadmin;
    }

    /**
     * Superadmin  → always allowed
     * Auditor     → always allowed (read-only audit access)
     * PIC         → only their own disbursement
     */
    public function printReport(User $user, Disbursement $disbursement): bool
    {
        return match ($user->role) {
            UserRole::Superadmin => true,
            UserRole::Auditor    => true,
            UserRole::Pic        => $disbursement->pic_id === $user->id,
            default              => false,
        };
    }
}