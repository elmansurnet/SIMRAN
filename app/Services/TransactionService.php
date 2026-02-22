<?php

namespace App\Services;

use App\Models\Disbursement;
use App\Models\Transaction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TransactionService
{
    public function create(Disbursement $disbursement, array $data, ?UploadedFile $proof = null): Transaction
    {
        $proofPath = null;
        $proofName = null;

        if ($proof) {
            [$proofPath, $proofName] = $this->storeProof($proof, $disbursement->id);
        }

        return $disbursement->transactions()->create([
            'created_by'       => auth()->id(),
            'type'             => $data['type'],
            'transaction_date' => $data['transaction_date'],
            'description'      => $data['description'],
            'amount'           => $data['amount'],
            'proof_path'       => $proofPath,
            'proof_original_name' => $proofName,
        ]);
    }

    public function update(Transaction $transaction, array $data, ?UploadedFile $proof = null): Transaction
    {
        $proofPath = $transaction->proof_path;
        $proofName = $transaction->proof_original_name;

        if ($proof) {
            // Delete old proof file if it exists
            if ($proofPath && Storage::exists($proofPath)) {
                Storage::delete($proofPath);
            }
            [$proofPath, $proofName] = $this->storeProof($proof, $transaction->disbursement_id);
        }

        $transaction->update([
            'type'             => $data['type'],
            'transaction_date' => $data['transaction_date'],
            'description'      => $data['description'],
            'amount'           => $data['amount'],
            'proof_path'       => $proofPath,
            'proof_original_name' => $proofName,
        ]);

        return $transaction->fresh();
    }

    public function delete(Transaction $transaction): void
    {
        // Clean up proof file
        if ($transaction->proof_path && Storage::exists($transaction->proof_path)) {
            Storage::delete($transaction->proof_path);
        }
        $transaction->delete();
    }

    private function storeProof(UploadedFile $file, int $disbursementId): array
    {
        $extension    = $file->getClientOriginalExtension();
        $originalName = $file->getClientOriginalName();
        $storedName   = Str::uuid() . '.' . $extension;
        $path         = "proofs/{$disbursementId}/{$storedName}";

        Storage::put($path, file_get_contents($file));

        return [$path, $originalName];
    }
}
