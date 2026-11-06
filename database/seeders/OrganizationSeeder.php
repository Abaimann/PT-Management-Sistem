<?php

namespace Database\Seeders;

use App\Models\Core\CompanySetting;
use App\Models\Core\Department;
use App\Models\Core\Position;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Company Profile Settings
        CompanySetting::updateOrCreate(
            ['id' => 1],
            [
                'company_name' => 'PT Management Sistem',
                'legal_name' => 'PT Management Sistem Indonesia',
                'npwp' => '01.234.567.8-012.000',
                'email' => 'corporate@managementsistem.co.id',
                'phone' => '+62 21 555-0199',
                'address' => 'Gedung Menara Sistem, Lantai 18, Jl. Jendral Sudirman Kav. 52-53',
                'city' => 'Jakarta Selatan',
                'postal_code' => '12190',
                'latitude' => -6.22501400,
                'longitude' => 106.80965700,
                'geofence_radius_meters' => 100,
            ]
        );

        // 2. Departments
        $departmentsData = [
            [
                'code' => 'DIR',
                'name' => 'Dewan Direksi & Eksekutif',
                'cost_center' => 'CC-100',
                'is_active' => true,
            ],
            [
                'code' => 'HRD',
                'name' => 'Human Resources & General Affairs',
                'cost_center' => 'CC-200',
                'is_active' => true,
            ],
            [
                'code' => 'FIN',
                'name' => 'Finance & Accounting',
                'cost_center' => 'CC-300',
                'is_active' => true,
            ],
            [
                'code' => 'OPS',
                'name' => 'Operasional & Teknologi Informasi',
                'cost_center' => 'CC-400',
                'is_active' => true,
            ],
            [
                'code' => 'PROC',
                'name' => 'Procurement & Logistik',
                'cost_center' => 'CC-500',
                'is_active' => true,
            ],
            [
                'code' => 'INV',
                'name' => 'Inventaris & Pergudangan',
                'cost_center' => 'CC-600',
                'is_active' => true,
            ],
        ];

        $departments = [];
        foreach ($departmentsData as $deptData) {
            $departments[$deptData['code']] = Department::updateOrCreate(['code' => $deptData['code']], $deptData);
        }

        // 3. Positions categorized by Department
        $positionsData = [
            // DIR
            [
                'department_id' => $departments['DIR']->id,
                'name' => 'Direktur Utama',
                'grade_level' => 'Director',
                'is_active' => true,
            ],
            [
                'department_id' => $departments['DIR']->id,
                'name' => 'Direktur Operasional & Keuangan',
                'grade_level' => 'Director',
                'is_active' => true,
            ],

            // HRD
            [
                'department_id' => $departments['HRD']->id,
                'name' => 'HR Manager',
                'grade_level' => 'Manager',
                'is_active' => true,
            ],
            [
                'department_id' => $departments['HRD']->id,
                'name' => 'HR & Payroll Specialist',
                'grade_level' => 'Officer',
                'is_active' => true,
            ],
            [
                'department_id' => $departments['HRD']->id,
                'name' => 'Talent Acquisition & People Ops',
                'grade_level' => 'Staff',
                'is_active' => true,
            ],

            // FIN
            [
                'department_id' => $departments['FIN']->id,
                'name' => 'Finance Manager',
                'grade_level' => 'Manager',
                'is_active' => true,
            ],
            [
                'department_id' => $departments['FIN']->id,
                'name' => 'Senior Accounting Officer',
                'grade_level' => 'Supervisor',
                'is_active' => true,
            ],
            [
                'department_id' => $departments['FIN']->id,
                'name' => 'Finance & Cashier Staff',
                'grade_level' => 'Staff',
                'is_active' => true,
            ],

            // OPS
            [
                'department_id' => $departments['OPS']->id,
                'name' => 'Operations & IT Manager',
                'grade_level' => 'Manager',
                'is_active' => true,
            ],
            [
                'department_id' => $departments['OPS']->id,
                'name' => 'Lead Software Engineer',
                'grade_level' => 'Supervisor',
                'is_active' => true,
            ],
            [
                'department_id' => $departments['OPS']->id,
                'name' => 'Fullstack Software Engineer',
                'grade_level' => 'Staff',
                'is_active' => true,
            ],

            // PROC
            [
                'department_id' => $departments['PROC']->id,
                'name' => 'Procurement Specialist',
                'grade_level' => 'Officer',
                'is_active' => true,
            ],

            // INV
            [
                'department_id' => $departments['INV']->id,
                'name' => 'Warehouse Supervisor',
                'grade_level' => 'Supervisor',
                'is_active' => true,
            ],
            [
                'department_id' => $departments['INV']->id,
                'name' => 'General Affairs & Warehouse Staff',
                'grade_level' => 'Staff',
                'is_active' => true,
            ],
        ];

        foreach ($positionsData as $pos) {
            Position::updateOrCreate(
                [
                    'department_id' => $pos['department_id'],
                    'name' => $pos['name'],
                ],
                $pos
            );
        }
    }
}
