<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Disbursement;
use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    /**
     * Create a transaction — PIC must own the disbursement AND it must allow transactions.
     * (Second argument is the Disbursement, passed via [Transaction::class, $disbursement])
     */
    public function create(User $user, ?Disbursement $disbursement = null): bool
    {
        if ($user->role !== UserRole::Pic) {
            return false;
        }
        if ($disbursement === null || $disbursement->pic_id !== $user->id) {
            return false;
        }
        return $disbursement->allowsTransactions();
    }

    /**
     * Edit own transaction — allowed during active or grace period only.
     */
    public function update(User $user, Transaction $transaction): bool
    {
        if ($user->role !== UserRole::Pic) {
            return false;
        }
        if ($transaction->created_by !== $user->id) {
            return false;
        }
        return $transaction->disbursement?->allowsTransactions() ?? false;
    }

    /**
     * Delete own transaction — same window as update.
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        return $this->update($user, $transaction);
    }

    /**
     * View / download proof — owner PIC, Superadmin, or Auditor.
     */
    public function downloadProof(User $user, Transaction $transaction): bool
    {
        return match ($user->role) {
            UserRole::Superadmin => true,
            UserRole::Auditor    => true,
            UserRole::Pic        => $transaction->created_by === $user->id
                                    || $transaction->disbursement?->pic_id === $user->id,
            default              => false,
        };
    }
}
