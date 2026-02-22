<?php

namespace App\Http\Requests;

use App\Services\DisbursementService;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDisbursementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isSuperadmin();
    }

    public function rules(): array
    {
        return [
            'purpose'              => ['required', Rule::in(['activity', 'operational'])],
            'name'                 => ['required', 'string', 'max:255'],
            'start_date'           => ['required', 'date'],
            'end_date'             => ['required', 'date', 'after_or_equal:start_date'],
            'pic_id'               => [
                'required',
                Rule::exists('users', 'id')->where('role', 'pic')->whereNull('deleted_at'),
            ],
            'chairperson'          => ['required', 'string', 'max:255'],
            'budget_allocation_id' => ['required', 'exists:budget_allocations,id'],
            'amount'               => ['required', 'numeric', 'min:1'],
            'auto_generate'        => ['boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            if ($v->errors()->any()) return;

            $remaining = app(DisbursementService::class)
                ->remainingForAllocation((int) $this->budget_allocation_id);

            if ((float) $this->amount > $remaining) {
                $formatted = 'Rp ' . number_format($remaining, 0, ',', '.');
                $v->errors()->add('amount', "Jumlah melebihi sisa anggaran tersedia: {$formatted}");
            }

            if ($this->boolean('auto_generate') && $this->filled('start_date', 'end_date')) {
                $days = Carbon::parse($this->start_date)->diffInDays(Carbon::parse($this->end_date));
                if ($days < 28 || $days > 31) {
                    $v->errors()->add('auto_generate', 'Auto-generate hanya tersedia untuk periode 28–31 hari.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'pic_id.required'               => 'PIC wajib dipilih.',
            'pic_id.exists'                 => 'PIC yang dipilih tidak valid atau bukan role PIC.',
            'budget_allocation_id.required' => 'Alokasi anggaran wajib dipilih.',
            'budget_allocation_id.exists'   => 'Alokasi anggaran tidak ditemukan.',
            'amount.min'                    => 'Jumlah pencairan minimal Rp 1.',
            'end_date.after_or_equal'       => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
        ];
    }
}
