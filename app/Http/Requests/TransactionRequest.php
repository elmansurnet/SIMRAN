<?php

namespace App\Http\Requests;

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
        $disbursement = $this->route('disbursement');
        return [
            'type'             => ['required', Rule::in(['expense', 'income'])],
            'transaction_date' => [
                'required', 'date',
                'after_or_equal:' . $disbursement->start_date->toDateString(),
                'before_or_equal:' . $disbursement->end_date->toDateString(),
            ],
            'description'      => ['required', 'string', 'max:500'],
            'amount'           => ['required', 'numeric', 'min:1'],
            'proof'            => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'transaction_date.after_or_equal'  => 'Tanggal transaksi tidak boleh sebelum periode kegiatan.',
            'transaction_date.before_or_equal'  => 'Tanggal transaksi tidak boleh setelah periode kegiatan.',
            'proof.mimes' => 'Bukti transaksi harus berformat JPG, JPEG, PNG, atau PDF.',
            'proof.max'   => 'Ukuran file bukti transaksi maksimal 5MB.',
        ];
    }
}
