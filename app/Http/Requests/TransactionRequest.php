<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isPic();
    }

    public function rules(): array
    {
        /** @var \App\Models\Disbursement $disbursement */
        $disbursement = $this->route('disbursement');

        // Upper bound = end_date + grace days (the model accessor).
        // This is correct for both create AND update.
        // During Phase 1 (Persiapan) the lower bound still prevents dates before
        // start_date, but the transaction button is shown and the form is reachable.
        $maxDate = $disbursement->transaction_deadline;

        $proofRule = $this->isMethod('PUT') || $this->isMethod('PATCH')
            ? ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120']  // optional on update
            : ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120']; // already nullable on create

        return [
            'type'             => ['required', Rule::in(['expense', 'income'])],
            'transaction_date' => [
                'required',
                'date',
                // boleh transaksi dengan menggunakan tanggal realtime saat phase 1 (upcoming)
                'after_or_equal:' . $disbursement->transaction_start_date,
                // transaksi dikunci jika masuk transaction_deadline
                'before_or_equal:' . $maxDate,
            ],
            'description' => ['required', 'string', 'max:500'],
            'amount'      => ['required', 'numeric', 'min:1'],
            'proof'       => $proofRule,
        ];
    }

    public function messages(): array
    {
        /** @var \App\Models\Disbursement $disbursement */
        $disbursement = $this->route('disbursement');
        $deadline = $disbursement->end_date
            ->copy()
            ->addDays(Setting::extraTransactionDays())
            ->format('d/m/Y');

        return [
            'transaction_date.after_or_equal'  => 'Tanggal transaksi tidak boleh sebelum pencairan anggaran dibuat.',
            'transaction_date.before_or_equal'  => "Tanggal transaksi tidak boleh melebihi batas pelaporan ({$deadline}).",
            'proof.mimes' => 'Bukti transaksi harus berformat JPG, JPEG, PNG, atau PDF.',
            'proof.max'   => 'Ukuran file bukti transaksi maksimal 5MB.',
        ];
    }
}