<?php

namespace App\Services;

use App\Models\Disbursement;
use App\Models\Transaction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TransactionService
{
    /**
     * Create a new transaction.
     * Signature preserved from the original codebase.
     */
    public function create(
        array $data,
        int $userId,
        Disbursement $disbursement,
        ?UploadedFile $proof = null
    ): Transaction {
        [$proofPath, $proofOriginalName] = $this->storeProof($proof, $disbursement->id);

        return Transaction::create([
            'disbursement_id'     => $disbursement->id,
            'created_by'          => $userId,
            'type'                => $data['type'],
            'transaction_date'    => $data['transaction_date'],
            'description'         => $data['description'],
            'amount'              => $data['amount'],
            'proof_path'          => $proofPath,
            'proof_original_name' => $proofOriginalName,
        ]);
    }

    /**
     * Update an existing transaction.
     * Replaces the proof file only when a new file is uploaded.
     */
    public function update(
        Transaction $transaction,
        array $data,
        ?UploadedFile $proof = null
    ): Transaction {
        $proofPath         = $transaction->proof_path;
        $proofOriginalName = $transaction->proof_original_name;

        if ($proof) {
            // Delete the old proof from disk before replacing
            if ($proofPath && Storage::disk('local')->exists($proofPath)) {
                Storage::disk('local')->delete($proofPath);
            }
            [$proofPath, $proofOriginalName] = $this->storeProof($proof, $transaction->disbursement_id);
        }

        $transaction->update([
            'type'                => $data['type'],
            'transaction_date'    => $data['transaction_date'],
            'description'         => $data['description'],
            'amount'              => $data['amount'],
            'proof_path'          => $proofPath,
            'proof_original_name' => $proofOriginalName,
        ]);

        return $transaction->fresh();
    }

    /**
     * Delete a transaction and remove its proof file from disk.
     */
    public function delete(Transaction $transaction): void
    {
        if ($transaction->proof_path
            && Storage::disk('local')->exists($transaction->proof_path)) {
            Storage::disk('local')->delete($transaction->proof_path);
        }
        $transaction->delete();
    }

    // ── Private helpers ────────────────────────────────

    /** @return array{0: string|null, 1: string|null} */
    private function storeProof(?UploadedFile $file, int $disbursementId): array
    {
        if (! $file) {
            return [null, null];
        }

        $ext          = $file->getClientOriginalExtension();
        $storedName   = Str::uuid() . '.' . $ext;
        $path         = $file->storeAs('proofs/' . $disbursementId, $storedName, 'local');
        $originalName = $file->getClientOriginalName();

        return [$path, $originalName];
    }
}