<?php

namespace Database\Seeders;

use App\Models\HRIS\LeaveType;
use App\Models\Inventory\Item;
use App\Models\Inventory\Warehouse;
use App\Models\Payroll\SalaryComponent;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Warehouses
        $warehouses = [
            [
                'code' => 'WH-MAIN',
                'name' => 'Gudang Utama Jakarta',
                'location' => 'Gedung Menara Sistem, Basement B2, Jakarta Selatan',
                'is_active' => true,
            ],
            [
                'code' => 'WH-IT',
                'name' => 'Ruang Server & Perlengkapan IT',
                'location' => 'Gedung Menara Sistem, Lantai 18, Jakarta Selatan',
                'is_active' => true,
            ],
            [
                'code' => 'WH-ATK',
                'name' => 'Depo ATK & Keperluan Umum',
                'location' => 'Gedung Menara Sistem, Lantai 17, Ruang GA, Jakarta Selatan',
                'is_active' => true,
            ],
        ];

        foreach ($warehouses as $wh) {
            Warehouse::updateOrCreate(['code' => $wh['code']], $wh);
        }

        // 2. Items
        $items = [
            [
                'sku' => 'SKU-ATK-001',
                'barcode' => '899123400001',
                'name' => 'Kertas PaperOne HVS A4 80gr',
                'category' => 'ATK',
                'unit' => 'RIM',
                'minimum_stock' => 15,
                'is_active' => true,
            ],
            [
                'sku' => 'SKU-ATK-002',
                'barcode' => '899123400002',
                'name' => 'Bolpoin Gel Pilot G2 0.5 Hitam',
                'category' => 'ATK',
                'unit' => 'BOX',
                'minimum_stock' => 5,
                'is_active' => true,
            ],
            [
                'sku' => 'SKU-IT-001',
                'barcode' => '899123400003',
                'name' => 'Mouse Wireless Logitech B170',
                'category' => 'HARDWARE_IT',
                'unit' => 'PCS',
                'minimum_stock' => 5,
                'is_active' => true,
            ],
            [
                'sku' => 'SKU-IT-002',
                'barcode' => '899123400004',
                'name' => 'Kabel UTP Cat6 Patch Cord Belden 3m',
                'category' => 'NETWORKING',
                'unit' => 'PCS',
                'minimum_stock' => 20,
                'is_active' => true,
            ],
            [
                'sku' => 'SKU-GA-001',
                'barcode' => '899123400005',
                'name' => 'Hand Sanitizer Gel Antis 500ml Pump',
                'category' => 'KEBERSIHAN',
                'unit' => 'BTL',
                'minimum_stock' => 10,
                'is_active' => true,
            ],
        ];

        foreach ($items as $item) {
            Item::updateOrCreate(['sku' => $item['sku']], $item);
        }

        // 3. Leave Types
        $leaveTypes = [
            [
                'code' => 'ANNUAL',
                'name' => 'Cuti Tahunan',
                'default_quota' => 12,
                'is_paid' => true,
                'requires_attachment' => false,
                'is_active' => true,
            ],
            [
                'code' => 'SICK',
                'name' => 'Cuti Sakit',
                'default_quota' => 14,
                'is_paid' => true,
                'requires_attachment' => true,
                'is_active' => true,
            ],
            [
                'code' => 'MATERNITY',
                'name' => 'Cuti Melahirkan',
                'default_quota' => 90,
                'is_paid' => true,
                'requires_attachment' => true,
                'is_active' => true,
            ],
            [
                'code' => 'SPECIAL',
                'name' => 'Izin Penting / Keperluan Khusus',
                'default_quota' => 3,
                'is_paid' => true,
                'requires_attachment' => false,
                'is_active' => true,
            ],
            [
                'code' => 'UNPAID',
                'name' => 'Cuti Diluar Tanggungan (Unpaid Leave)',
                'default_quota' => 30,
                'is_paid' => false,
                'requires_attachment' => false,
                'is_active' => true,
            ],
        ];

        foreach ($leaveTypes as $lt) {
            LeaveType::updateOrCreate(['code' => $lt['code']], $lt);
        }

        // 4. Salary Components
        $salaryComponents = [
            [
                'code' => 'BASIC_SALARY',
                'name' => 'Gaji Pokok',
                'type' => 'EARNING',
                'is_taxable' => true,
                'is_fixed' => true,
                'default_amount' => 5000000.00,
                'is_active' => true,
            ],
            [
                'code' => 'MEAL_ALLOWANCE',
                'name' => 'Tunjangan Uang Makan',
                'type' => 'EARNING',
                'is_taxable' => true,
                'is_fixed' => true,
                'default_amount' => 800000.00,
                'is_active' => true,
            ],
            [
                'code' => 'TRANSPORT_ALLOWANCE',
                'name' => 'Tunjangan Transportasi',
                'type' => 'EARNING',
                'is_taxable' => true,
                'is_fixed' => true,
                'default_amount' => 600000.00,
                'is_active' => true,
            ],
            [
                'code' => 'POSITION_ALLOWANCE',
                'name' => 'Tunjangan Jabatan',
                'type' => 'EARNING',
                'is_taxable' => true,
                'is_fixed' => true,
                'default_amount' => 1500000.00,
                'is_active' => true,
            ],
            [
                'code' => 'OVERTIME_PAY',
                'name' => 'Uang Lembur',
                'type' => 'EARNING',
                'is_taxable' => true,
                'is_fixed' => false,
                'default_amount' => 0.00,
                'is_active' => true,
            ],
            [
                'code' => 'BPJS_TK_DED',
                'name' => 'Potongan BPJS Ketenagakerjaan (JHT & JP)',
                'type' => 'DEDUCTION',
                'is_taxable' => false,
                'is_fixed' => true,
                'default_amount' => 150000.00,
                'is_active' => true,
            ],
            [
                'code' => 'BPJS_KES_DED',
                'name' => 'Potongan BPJS Kesehatan',
                'type' => 'DEDUCTION',
                'is_taxable' => false,
                'is_fixed' => true,
                'default_amount' => 100000.00,
                'is_active' => true,
            ],
            [
                'code' => 'PPH21_DED',
                'name' => 'Potongan Pajak PPh 21 Terutang',
                'type' => 'DEDUCTION',
                'is_taxable' => false,
                'is_fixed' => false,
                'default_amount' => 0.00,
                'is_active' => true,
            ],
            [
                'code' => 'LOAN_DED',
                'name' => 'Potongan Pinjaman / Kasbon Karyawan',
                'type' => 'DEDUCTION',
                'is_taxable' => false,
                'is_fixed' => false,
                'default_amount' => 0.00,
                'is_active' => true,
            ],
        ];

        foreach ($salaryComponents as $sc) {
            SalaryComponent::updateOrCreate(['code' => $sc['code']], $sc);
        }
    }
}
