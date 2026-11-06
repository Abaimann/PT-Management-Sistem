<?php

namespace App\Http\Requests\Payroll;

use Illuminate\Foundation\Http\FormRequest;

class StorePayrollPeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:30', 'unique:payroll_periods,code'],
            'name' => ['required', 'string', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'cutoff_start_date' => ['nullable', 'date'],
            'cutoff_end_date' => ['nullable', 'date', 'after_or_equal:cutoff_start_date'],
            'cutoff_date' => ['nullable', 'date'],
            'payment_date' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode periode payroll wajib diisi.',
            'code.unique' => 'Kode periode payroll sudah terdaftar.',
            'name.required' => 'Nama periode payroll wajib diisi.',
            'start_date.required' => 'Tanggal awal periode wajib diisi.',
            'end_date.required' => 'Tanggal akhir periode wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal akhir harus sama atau setelah tanggal awal.',
            'payment_date.required' => 'Tanggal pembayaran wajib diisi.',
        ];
    }
}
