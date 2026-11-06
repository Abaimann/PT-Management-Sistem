<?php

namespace Database\Seeders;

use App\Models\Core\Permission;
use App\Models\Core\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Define 10 Canonical Roles
        $rolesData = [
            [
                'code' => 'super_admin',
                'name' => 'Super Admin',
                'description' => 'Pengelolaan sistem, user, roles, permissions, konfigurasi global, audit logs.',
            ],
            [
                'code' => 'hr_admin',
                'name' => 'Admin / HR',
                'description' => 'Pengelolaan data karyawan, kontrak, cuti/izin/sakit, absensi, jadwal shift.',
            ],
            [
                'code' => 'supervisor',
                'name' => 'Senior / Supervisor',
                'description' => 'Pengelolaan penugasan target & tugas harian tim, approval review pekerjaan, evaluasi intern.',
            ],
            [
                'code' => 'employee',
                'name' => 'Karyawan',
                'description' => 'Akses profil mandiri, selfie check-in/out, pengajuan cuti/koreksi, pengerjaan tugas, submit bukti kerja.',
            ],
            [
                'code' => 'intern',
                'name' => 'Anak Magang',
                'description' => 'Absensi mandiri, pengisian logbook harian, submit tugas magang. Terisolasi dari data pegawai & keuangan.',
            ],
            [
                'code' => 'payroll_officer',
                'name' => 'Tim Payroll',
                'description' => 'Konfigurasi komponen gaji, pembuatan draft payroll bulanan, rekap kehadiran, kalkulasi slip gaji.',
            ],
            [
                'code' => 'finance_officer',
                'name' => 'Finance / Accounting',
                'description' => 'Pengelolaan Chart of Accounts (CoA), pembukuan jurnal double-entry, kas masuk/keluar, approval pengeluaran/reimbursement, budgeting.',
            ],
            [
                'code' => 'procurement_officer',
                'name' => 'Procurement / Purchasing',
                'description' => 'Manajemen vendor, pemrosesan Purchase Request (PR), penerbitan Purchase Order (PO), verifikasi penerimaan barang (3-way matching).',
            ],
            [
                'code' => 'inventory_ga',
                'name' => 'Inventory / General Affairs',
                'description' => 'Pengelolaan stok barang gudang, mutasi inventaris, register aset tetap, BAST penugasan aset, jadwal pemeliharaan fasilitas.',
            ],
            [
                'code' => 'director',
                'name' => 'Direksi / Management',
                'description' => 'Executive KPI Dashboard, pemantauan seluruh lini operasional, laporan keuangan tingkat tinggi, High-Value Approval.',
            ],
        ];

        $roles = [];
        foreach ($rolesData as $data) {
            $roles[$data['code']] = Role::updateOrCreate(['code' => $data['code']], $data);
        }

        // 2. Define Permissions according to ROLE_PERMISSION_MATRIX.md
        $permissionsData = [
            // Settings
            ['code' => 'settings.view', 'module' => 'core', 'name' => 'View System Settings', 'description' => 'Melihat pengaturan sistem'],
            ['code' => 'settings.manage', 'module' => 'core', 'name' => 'Manage System Settings', 'description' => 'Mengelola pengaturan sistem & konfigurasi'],

            // Audit
            ['code' => 'audit.view', 'module' => 'core', 'name' => 'View Audit Logs', 'description' => 'Melihat jejak audit aktivitas sistem'],
            ['code' => 'audit.manage', 'module' => 'core', 'name' => 'Manage Audit Logs', 'description' => 'Mengelola jejak audit sistem'],

            // Organization
            ['code' => 'organization.view', 'module' => 'core', 'name' => 'View Organization Data', 'description' => 'Melihat data departemen dan jabatan'],
            ['code' => 'organization.manage', 'module' => 'core', 'name' => 'Manage Organization Data', 'description' => 'Mengelola departemen dan jabatan'],

            // Employee & Contract
            ['code' => 'employee.view_self', 'module' => 'hris', 'name' => 'View Self Profile', 'description' => 'Melihat profil mandiri'],
            ['code' => 'employee.view_team', 'module' => 'hris', 'name' => 'View Team Profile', 'description' => 'Melihat profil anggota tim'],
            ['code' => 'employee.view_all', 'module' => 'hris', 'name' => 'View All Employees', 'description' => 'Melihat seluruh data pegawai'],
            ['code' => 'employee.manage', 'module' => 'hris', 'name' => 'Manage Employees & Contracts', 'description' => 'Mengelola data pegawai dan kontrak'],

            // Attendance
            ['code' => 'attendance.record_self', 'module' => 'attendance', 'name' => 'Record Self Attendance', 'description' => 'Absensi selfie & GPS mandiri'],
            ['code' => 'attendance.view_team', 'module' => 'attendance', 'name' => 'View Team Attendance', 'description' => 'Melihat absensi tim'],
            ['code' => 'attendance.approve_team', 'module' => 'attendance', 'name' => 'Approve Team Attendance', 'description' => 'Verifikasi absensi tim'],
            ['code' => 'attendance.view_all', 'module' => 'attendance', 'name' => 'View All Attendance', 'description' => 'Melihat semua riwayat absensi'],
            ['code' => 'attendance.manage', 'module' => 'attendance', 'name' => 'Manage Attendance & Shifts', 'description' => 'Mengelola absensi dan jadwal shift'],

            // Leave & Corrections
            ['code' => 'leave.create_self', 'module' => 'hris', 'name' => 'Request Leave & Correction', 'description' => 'Pengajuan cuti dan koreksi absensi'],
            ['code' => 'leave.approve_team', 'module' => 'hris', 'name' => 'Approve Team Leave', 'description' => 'Persetujuan cuti / koreksi tim'],
            ['code' => 'leave.approve_all', 'module' => 'hris', 'name' => 'Approve All Leave', 'description' => 'Persetujuan cuti tingkat perusahaan (HR)'],
            ['code' => 'leave.view_all', 'module' => 'hris', 'name' => 'View All Leave Requests', 'description' => 'Melihat seluruh permohonan cuti'],

            // Operations & Tasks
            ['code' => 'tasks.update_self', 'module' => 'operations', 'name' => 'Update Self Tasks', 'description' => 'Update progres tugas pribadi'],
            ['code' => 'tasks.manage_team', 'module' => 'operations', 'name' => 'Manage Team Tasks', 'description' => 'Membuat target dan tugas harian tim'],
            ['code' => 'tasks.view_all', 'module' => 'operations', 'name' => 'View All Tasks', 'description' => 'Melihat seluruh target dan penugasan'],

            // Internship
            ['code' => 'internship.create_self', 'module' => 'internship', 'name' => 'Submit Intern Logbook', 'description' => 'Mengisi logbook magang harian'],
            ['code' => 'internship.approve_team', 'module' => 'internship', 'name' => 'Approve Intern Logbook & Review', 'description' => 'Evaluasi dan verifikasi logbook magang'],
            ['code' => 'internship.view_all', 'module' => 'internship', 'name' => 'View All Internships', 'description' => 'Melihat seluruh program magang'],
            ['code' => 'internship.manage', 'module' => 'internship', 'name' => 'Manage Internship Program', 'description' => 'Mengelola data dan penugasan magang'],

            // Payroll Components
            ['code' => 'payroll_component.view', 'module' => 'payroll', 'name' => 'View Salary Components', 'description' => 'Melihat master komponen gaji'],
            ['code' => 'payroll_component.manage', 'module' => 'payroll', 'name' => 'Manage Salary Components', 'description' => 'Mengelola master komponen gaji'],

            // Payroll Calculation
            ['code' => 'payroll.view', 'module' => 'payroll', 'name' => 'View Payroll Runs', 'description' => 'Melihat draft penggajian'],
            ['code' => 'payroll.review', 'module' => 'payroll', 'name' => 'Review Payroll Calculation', 'description' => 'Review dan rekonsiliasi payroll'],
            ['code' => 'payroll.manage', 'module' => 'payroll', 'name' => 'Execute Payroll Calculation', 'description' => 'Kalkulasi batch payroll bulanan'],
            ['code' => 'payroll.approve', 'module' => 'payroll', 'name' => 'Approve Payroll Final', 'description' => 'Persetujuan eksekutif penggajian'],

            // Payslips
            ['code' => 'payslip.view_self', 'module' => 'payroll', 'name' => 'View Self Payslip', 'description' => 'Melihat slip gaji mandiri'],
            ['code' => 'payslip.view_all', 'module' => 'payroll', 'name' => 'View All Payslips', 'description' => 'Melihat slip gaji seluruh karyawan'],

            // Finance & CoA
            ['code' => 'finance.view', 'module' => 'finance', 'name' => 'View Chart of Accounts & Journals', 'description' => 'Melihat CoA dan jurnal keuangan'],
            ['code' => 'finance.manage', 'module' => 'finance', 'name' => 'Manage CoA & Journal Entries', 'description' => 'Mengelola CoA, pembukuan jurnal, budget'],

            // Reimbursement
            ['code' => 'reimbursement.create_self', 'module' => 'finance', 'name' => 'Submit Reimbursement', 'description' => 'Mengajukan klaim reimbursement'],
            ['code' => 'reimbursement.approve_team', 'module' => 'finance', 'name' => 'Approve Team Reimbursement', 'description' => 'Persetujuan reimbursement tim'],
            ['code' => 'reimbursement.post_finance', 'module' => 'finance', 'name' => 'Finance Verification & Post', 'description' => 'Verifikasi keuangan dan posting jurnal kas'],
            ['code' => 'reimbursement.approve_director', 'module' => 'finance', 'name' => 'Approve High-Value Reimbursement', 'description' => 'Persetujuan klaim nominal tinggi oleh direksi'],
            ['code' => 'reimbursement.view_all', 'module' => 'finance', 'name' => 'View All Reimbursements', 'description' => 'Melihat semua klaim pengeluaran'],

            // Procurement
            ['code' => 'procurement.view', 'module' => 'procurement', 'name' => 'View Vendors & Orders', 'description' => 'Melihat vendor, PR, dan PO'],
            ['code' => 'procurement.manage', 'module' => 'procurement', 'name' => 'Manage Procurement Operations', 'description' => 'Mengelola vendor, PR, PO, dan penerimaan'],
            ['code' => 'procurement.approve_dept', 'module' => 'procurement', 'name' => 'Approve Dept Purchase Request', 'description' => 'Persetujuan PR departemen'],
            ['code' => 'procurement.approve_director', 'module' => 'procurement', 'name' => 'Approve High-Value PO', 'description' => 'Persetujuan PO bernilai tinggi oleh direksi'],
            ['code' => 'procurement.payment_review', 'module' => 'procurement', 'name' => 'Payment Verification', 'description' => 'Review PO untuk pembayaran kas keluar'],

            // Inventory
            ['code' => 'inventory.view', 'module' => 'inventory', 'name' => 'View Warehouse & Stock', 'description' => 'Melihat stok dan mutasi barang gudang'],
            ['code' => 'inventory.manage', 'module' => 'inventory', 'name' => 'Manage Warehouse & Stock', 'description' => 'Mengelola stok, mutasi kartu stok gudang'],

            // Assets & BAST
            ['code' => 'asset.view_assigned', 'module' => 'inventory', 'name' => 'View Assigned Assets', 'description' => 'Melihat daftar aset pinjaman pribadi'],
            ['code' => 'asset.view_all', 'module' => 'inventory', 'name' => 'View All Assets & Maintenance', 'description' => 'Melihat seluruh register aset & log servis'],
            ['code' => 'asset.manage', 'module' => 'inventory', 'name' => 'Manage Assets, BAST & Maintenance', 'description' => 'Mengelola register aset tetap, serah terima BAST & servis'],

            // General Affairs & Facility
            ['code' => 'facility.create_self', 'module' => 'inventory', 'name' => 'Request Facility & ATK', 'description' => 'Pengajuan ATK, perbaikan sarana kantor'],
            ['code' => 'facility.approve_dept', 'module' => 'inventory', 'name' => 'Approve Dept Facility Request', 'description' => 'Persetujuan pengajuan sarana tingkat departemen'],
            ['code' => 'facility.view_all', 'module' => 'inventory', 'name' => 'View All Facility Requests', 'description' => 'Melihat semua pengajuan sarana operasional'],
            ['code' => 'facility.manage', 'module' => 'inventory', 'name' => 'Manage General Affairs Requests', 'description' => 'Penyelesaian permohonan ATK dan fasilitas'],

            // Dashboard
            ['code' => 'dashboard.full', 'module' => 'core', 'name' => 'Full System Dashboard', 'description' => 'Akses dashboard keseluruhan sistem'],
            ['code' => 'dashboard.hr', 'module' => 'hris', 'name' => 'HR KPI Dashboard', 'description' => 'Akses dashboard HRIS'],
            ['code' => 'dashboard.team', 'module' => 'operations', 'name' => 'Team KPI Dashboard', 'description' => 'Akses dashboard performa tim'],
            ['code' => 'dashboard.self', 'module' => 'core', 'name' => 'Self Dashboard', 'description' => 'Akses dashboard personal'],
            ['code' => 'dashboard.payroll', 'module' => 'payroll', 'name' => 'Payroll Dashboard', 'description' => 'Akses dashboard payroll'],
            ['code' => 'dashboard.finance', 'module' => 'finance', 'name' => 'Finance Dashboard', 'description' => 'Akses dashboard keuangan'],
            ['code' => 'dashboard.procurement', 'module' => 'procurement', 'name' => 'Procurement Dashboard', 'description' => 'Akses dashboard pengadaan'],
            ['code' => 'dashboard.inventory', 'module' => 'inventory', 'name' => 'Inventory/GA Dashboard', 'description' => 'Akses dashboard inventaris & aset'],
            ['code' => 'dashboard.director', 'module' => 'core', 'name' => 'Executive KPI Dashboard', 'description' => 'Akses dashboard eksekutif direksi'],
        ];

        $permissions = [];
        foreach ($permissionsData as $perm) {
            $permissions[$perm['code']] = Permission::updateOrCreate(['code' => $perm['code']], $perm);
        }

        // 3. Map Roles to Permissions based on Matrix
        $rolePermissionsMap = [
            'super_admin' => array_keys($permissions), // Super Admin has all permissions

            'hr_admin' => [
                'audit.view',
                'organization.view', 'organization.manage',
                'employee.view_self', 'employee.view_team', 'employee.view_all', 'employee.manage',
                'attendance.record_self', 'attendance.view_team', 'attendance.approve_team', 'attendance.view_all', 'attendance.manage',
                'leave.create_self', 'leave.approve_team', 'leave.approve_all', 'leave.view_all',
                'internship.view_all',
                'payroll_component.view',
                'payroll.view',
                'asset.view_all',
                'dashboard.self', 'dashboard.hr',
            ],

            'supervisor' => [
                'organization.view',
                'employee.view_self', 'employee.view_team',
                'attendance.record_self', 'attendance.view_team', 'attendance.approve_team',
                'leave.create_self', 'leave.approve_team',
                'tasks.update_self', 'tasks.manage_team',
                'internship.approve_team',
                'reimbursement.create_self', 'reimbursement.approve_team',
                'procurement.approve_dept',
                'asset.view_assigned',
                'facility.create_self', 'facility.approve_dept',
                'dashboard.self', 'dashboard.team',
            ],

            'employee' => [
                'organization.view',
                'employee.view_self',
                'attendance.record_self',
                'leave.create_self',
                'tasks.update_self',
                'payslip.view_self',
                'reimbursement.create_self',
                'asset.view_assigned',
                'facility.create_self',
                'dashboard.self',
            ],

            'intern' => [
                'attendance.record_self',
                'tasks.update_self',
                'internship.create_self',
                'asset.view_assigned',
                'facility.create_self',
                'dashboard.self',
            ],

            'payroll_officer' => [
                'organization.view',
                'employee.view_all',
                'attendance.view_all',
                'payroll_component.view', 'payroll_component.manage',
                'payroll.view', 'payroll.manage',
                'payslip.view_all',
                'dashboard.self', 'dashboard.payroll',
            ],

            'finance_officer' => [
                'organization.view',
                'payroll_component.view',
                'payroll.view', 'payroll.review',
                'payslip.view_all',
                'finance.view', 'finance.manage',
                'reimbursement.view_all', 'reimbursement.post_finance',
                'procurement.view', 'procurement.payment_review',
                'asset.view_all',
                'dashboard.self', 'dashboard.finance',
            ],

            'procurement_officer' => [
                'organization.view',
                'procurement.view', 'procurement.manage',
                'inventory.view',
                'dashboard.self', 'dashboard.procurement',
            ],

            'inventory_ga' => [
                'organization.view',
                'inventory.view', 'inventory.manage',
                'asset.view_all', 'asset.manage',
                'facility.view_all', 'facility.manage',
                'dashboard.self', 'dashboard.inventory',
            ],

            'director' => [
                'settings.view',
                'audit.view',
                'organization.view',
                'employee.view_all',
                'attendance.view_all',
                'leave.view_all',
                'tasks.view_all',
                'internship.view_all',
                'payroll_component.view',
                'payroll.view', 'payroll.approve',
                'payslip.view_all',
                'finance.view',
                'reimbursement.view_all', 'reimbursement.approve_director',
                'procurement.view', 'procurement.approve_director',
                'inventory.view',
                'asset.view_all',
                'facility.view_all',
                'dashboard.director',
            ],
        ];

        foreach ($rolePermissionsMap as $roleCode => $permCodes) {
            $role = $roles[$roleCode];
            $permIds = Permission::whereIn('code', $permCodes)->pluck('id')->toArray();
            $role->permissions()->sync($permIds);
        }
    }
}
