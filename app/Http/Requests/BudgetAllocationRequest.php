<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BudgetAllocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isSuperadmin();
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'type'          => ['required', Rule::in(['monthly', 'yearly'])],
            'start_date'    => ['required', 'date'],
            'end_date'      => ['required', 'date', 'after_or_equal:start_date'],
            'amount'        => ['required', 'numeric', 'min:1'],
            'description'   => ['nullable', 'string', 'max:1000'],
            'auto_generate' => ['boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            if (! $this->filled('start_date') || ! $this->filled('end_date')) return;
            $days = Carbon::parse($this->start_date)->diffInDays(Carbon::parse($this->end_date));
            if ($this->boolean('auto_generate') && ($days < 28 || $days > 31)) {
                $v->errors()->add('auto_generate', 'Auto-generate hanya tersedia untuk periode 28–31 hari.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.required'       => 'Nama anggaran wajib diisi.',
            'type.required'       => 'Tipe anggaran wajib dipilih.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'end_date.required'   => 'Tanggal selesai wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'amount.required'     => 'Jumlah anggaran wajib diisi.',
            'amount.numeric'      => 'Jumlah anggaran harus berupa angka.',
            'amount.min'          => 'Jumlah anggaran minimal Rp 1.',
        ];
    }
}
