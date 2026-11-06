<?php

namespace Database\Seeders;

use App\Models\Core\Department;
use App\Models\Core\Position;
use App\Models\Core\Role;
use App\Models\Core\User;
use App\Models\HRIS\Employee;
use App\Models\HRIS\LeaveBalance;
use App\Models\HRIS\LeaveType;
use App\Models\Internship\Intern;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Run Foundation & Master Data Seeders
        $this->call([
            RolePermissionSeeder::class,
            OrganizationSeeder::class,
            ShiftSeeder::class,
            ChartOfAccountsSeeder::class,
            MasterDataSeeder::class,
        ]);

        // 2. Define 10 Canonical Default Enterprise Accounts
        $defaultAccounts = [
            [
                'email' => 'admin@managementsistem.co.id',
                'name' => 'Super Administrator',
                'role' => 'super_admin',
                'nik' => 'EMP-0001',
                'dept_code' => 'DIR',
                'pos_name' => 'Direktur Utama',
                'gender' => 'MALE',
                'status' => 'PERMANENT',
            ],
            [
                'email' => 'direksi@managementsistem.co.id',
                'name' => 'Direktur Operasional',
                'role' => 'director',
                'nik' => 'EMP-0002',
                'dept_code' => 'DIR',
                'pos_name' => 'Direktur Operasional & Keuangan',
                'gender' => 'MALE',
                'status' => 'PERMANENT',
            ],
            [
                'email' => 'hr@managementsistem.co.id',
                'name' => 'HR Administrator',
                'role' => 'hr_admin',
                'nik' => 'EMP-0003',
                'dept_code' => 'HRD',
                'pos_name' => 'HR Manager',
                'gender' => 'FEMALE',
                'status' => 'PERMANENT',
            ],
            [
                'email' => 'supervisor@managementsistem.co.id',
                'name' => 'Supervisor Operasional',
                'role' => 'supervisor',
                'nik' => 'EMP-0004',
                'dept_code' => 'OPS',
                'pos_name' => 'Lead Software Engineer',
                'gender' => 'MALE',
                'status' => 'PERMANENT',
            ],
            [
                'email' => 'karyawan@managementsistem.co.id',
                'name' => 'Budi Santoso',
                'role' => 'employee',
                'nik' => 'EMP-0005',
                'dept_code' => 'OPS',
                'pos_name' => 'Fullstack Software Engineer',
                'gender' => 'MALE',
                'status' => 'PERMANENT',
            ],
            [
                'email' => 'magang@managementsistem.co.id',
                'name' => 'Ahmad Fauzi Magang',
                'role' => 'intern',
                'nik' => 'EMP-0006',
                'dept_code' => 'OPS',
                'pos_name' => 'Fullstack Software Engineer',
                'gender' => 'MALE',
                'status' => 'INTERN',
                'is_intern' => true,
            ],
            [
                'email' => 'payroll@managementsistem.co.id',
                'name' => 'Petugas Payroll',
                'role' => 'payroll_officer',
                'nik' => 'EMP-0007',
                'dept_code' => 'HRD',
                'pos_name' => 'HR & Payroll Specialist',
                'gender' => 'FEMALE',
                'status' => 'PERMANENT',
            ],
            [
                'email' => 'finance@managementsistem.co.id',
                'name' => 'Petugas Keuangan',
                'role' => 'finance_officer',
                'nik' => 'EMP-0008',
                'dept_code' => 'FIN',
                'pos_name' => 'Senior Accounting Officer',
                'gender' => 'FEMALE',
                'status' => 'PERMANENT',
            ],
            [
                'email' => 'procurement@managementsistem.co.id',
                'name' => 'Petugas Pengadaan',
                'role' => 'procurement_officer',
                'nik' => 'EMP-0009',
                'dept_code' => 'PROC',
                'pos_name' => 'Procurement Specialist',
                'gender' => 'MALE',
                'status' => 'PERMANENT',
            ],
            [
                'email' => 'inventory@managementsistem.co.id',
                'name' => 'Petugas Gudang & GA',
                'role' => 'inventory_ga',
                'nik' => 'EMP-0010',
                'dept_code' => 'INV',
                'pos_name' => 'Warehouse Supervisor',
                'gender' => 'MALE',
                'status' => 'PERMANENT',
            ],
        ];

        $leaveTypes = LeaveType::all();
        $supervisorEmployee = null;

        foreach ($defaultAccounts as $acc) {
            $role = Role::where('code', $acc['role'])->first();
            $dept = Department::where('code', $acc['dept_code'])->first();
            $position = Position::where('department_id', $dept?->id)
                ->where('name', $acc['pos_name'])
                ->first() ?? Position::where('department_id', $dept?->id)->first();

            $user = User::updateOrCreate(
                ['email' => $acc['email']],
                [
                    'name' => $acc['name'],
                    'password' => Hash::make('password123'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            if ($role) {
                $user->roles()->sync([$role->id]);
            }

            $employee = Employee::updateOrCreate(
                ['nik' => $acc['nik']],
                [
                    'user_id' => $user->id,
                    'full_name' => $acc['name'],
                    'gender' => $acc['gender'],
                    'birth_place' => 'Jakarta',
                    'birth_date' => '1992-06-15',
                    'phone' => '+62 812-5555-' . rand(1000, 9999),
                    'address' => 'Jl. Jendral Sudirman No. ' . rand(10, 99) . ', Jakarta',
                    'department_id' => $dept?->id,
                    'position_id' => $position?->id,
                    'supervisor_id' => $supervisorEmployee?->id,
                    'employment_status' => $acc['status'],
                    'join_date' => '2023-01-01',
                    'is_active' => true,
                ]
            );

            if ($acc['role'] === 'supervisor') {
                $supervisorEmployee = $employee;
            }

            // Create leave balances for regular employees
            foreach ($leaveTypes as $lt) {
                LeaveBalance::firstOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'leave_type_id' => $lt->id,
                        'year' => (int) date('Y'),
                    ],
                    [
                        'total_quota' => $lt->default_quota,
                        'used_quota' => 0,
                        'remaining_quota' => $lt->default_quota,
                    ]
                );
            }

            // If intern, register into interns table
            if (!empty($acc['is_intern'])) {
                Intern::updateOrCreate(
                    ['intern_code' => 'INT-2026-001'],
                    [
                        'user_id' => $user->id,
                        'mentor_id' => $supervisorEmployee?->id ?? $employee->id,
                        'department_id' => $dept?->id,
                        'full_name' => $acc['name'],
                        'institution' => 'Universitas Indonesia',
                        'major' => 'Ilmu Komputer & Sistem Informasi',
                        'phone' => '+62 813-8888-9999',
                        'start_date' => '2026-09-01',
                        'end_date' => '2027-02-28',
                        'status' => 'ACTIVE',
                    ]
                );
            }
        }
    }
}
