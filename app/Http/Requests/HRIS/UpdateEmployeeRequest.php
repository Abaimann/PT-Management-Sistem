<?php

namespace App\Http\Requests\HRIS;

use App\Models\HRIS\Employee;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $employee = $this->route('employee');
        $employeeId = $employee instanceof Employee ? $employee->id : $employee;
        $userId = $employee instanceof Employee ? $employee->user_id : null;

        return [
            'nik' => [
                'required',
                'string',
                'max:30',
                Rule::unique('employees', 'nik')->ignore($employeeId),
            ],
            'full_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:MALE,FEMALE'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['required', 'date'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'position_id' => ['required', 'integer', 'exists:positions,id'],
            'supervisor_id' => [
                'nullable',
                'integer',
                'exists:employees,id',
                Rule::notIn([$employeeId]),
            ],
            'employment_status' => ['required', 'in:PERMANENT,CONTRACT,INTERN,PROBATION'],
            'join_date' => ['required', 'date'],
            'resign_date' => ['nullable', 'date', 'after_or_equal:join_date'],
            'is_active' => ['nullable', 'boolean'],

            // Optional User Account Updates
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => ['nullable', 'string', 'min:8'],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
        ];
    }

    /**
     * Prepare inputs for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'nik' => strtoupper(trim((string) $this->nik)),
            'full_name' => trim((string) $this->full_name),
            'email' => $this->email ? strtolower(trim((string) $this->email)) : null,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * Custom attribute names.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nik' => 'Nomor Induk Karyawan (NIK)',
            'full_name' => 'Nama Lengkap',
            'gender' => 'Jenis Kelamin',
            'birth_place' => 'Tempat Lahir',
            'birth_date' => 'Tanggal Lahir',
            'phone' => 'Nomor Telepon',
            'address' => 'Alamat Lengkap',
            'department_id' => 'Departemen',
            'position_id' => 'Jabatan',
            'supervisor_id' => 'Atasan Langsung',
            'employment_status' => 'Status Kepegawaian',
            'join_date' => 'Tanggal Masuk',
            'resign_date' => 'Tanggal Berhenti',
            'is_active' => 'Status Aktif',
            'email' => 'Email Akun Pengguna',
            'password' => 'Kata Sandi Baru',
            'role_id' => 'Peran Sistem (Role)',
        ];
    }
}
