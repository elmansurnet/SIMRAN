<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Disbursement;
use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    /**
     * PIC can create a transaction when:
     *   – the disbursement belongs to them
     *   – the disbursement is NOT in Phase 4 (allowsTransactions() is the gate)
     *
     * Phase 1 (Akan Datang), Phase 2 (Aktif), Phase 3 (Periode Pelaporan) all allow.
     */
    public function create(User $user, Disbursement $disbursement): bool
    {
        return $user->role === UserRole::Pic
            && $disbursement->pic_id === $user->id
            && $disbursement->allowsTransactions();
    }

    /**
     * PIC can update/delete only their own transactions
     * within phases that allow changes.
     */
    public function update(User $user, Transaction $transaction): bool
    {
        return $user->role === UserRole::Pic
            && $transaction->created_by === $user->id
            && $transaction->disbursement->allowsTransactions();
    }

    public function delete(User $user, Transaction $transaction): bool
    {
        return $this->update($user, $transaction);
    }

    /**
     * Viewing a single transaction:
     * – Superadmin and Auditor can always view
     * – PIC can view transactions of their own disbursements
     */
    public function view(User $user, Transaction $transaction): bool
    {
        if (in_array($user->role, [UserRole::Superadmin, UserRole::Auditor], true)) return true;
        return $user->role === UserRole::Pic
            && $transaction->disbursement->pic_id === $user->id;
    }

    /**
     * Downloading a proof file:
     * – Superadmin and Auditor can always download
     * – PIC can download proofs of transactions belonging to their disbursements
     *
     * Note: proof download has no phase restriction (you should be able to
     * view historical proofs even after the period is closed).
     */
    public function downloadProof(User $user, Transaction $transaction): bool
    {
        if (in_array($user->role, [UserRole::Superadmin, UserRole::Auditor], true)) return true;
        return $user->role === UserRole::Pic
            && $transaction->disbursement->pic_id === $user->id;
    }
}