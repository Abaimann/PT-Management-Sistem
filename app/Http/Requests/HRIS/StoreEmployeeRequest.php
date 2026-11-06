<?php

namespace App\Http\Requests\HRIS;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
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
        return [
            'nik' => ['required', 'string', 'max:30', 'unique:employees,nik'],
            'full_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:MALE,FEMALE'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['required', 'date'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'position_id' => ['required', 'integer', 'exists:positions,id'],
            'supervisor_id' => ['nullable', 'integer', 'exists:employees,id'],
            'employment_status' => ['required', 'in:PERMANENT,CONTRACT,INTERN,PROBATION'],
            'join_date' => ['required', 'date'],
            'resign_date' => ['nullable', 'date', 'after_or_equal:join_date'],
            'is_active' => ['nullable', 'boolean'],

            // User account creation options
            'create_user_account' => ['nullable', 'boolean'],
            'email' => ['nullable', 'required_if:create_user_account,true', 'email', 'max:255', 'unique:users,email'],
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
            'is_active' => $this->boolean('is_active', true),
            'create_user_account' => $this->boolean('create_user_account'),
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
            'password' => 'Kata Sandi Akun Pengguna',
            'role_id' => 'Peran Sistem (Role)',
        ];
    }
}
