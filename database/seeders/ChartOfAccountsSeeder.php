<?php

namespace Database\Seeders;

use App\Models\Finance\ChartOfAccount;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Level 1 - Header Categories
        $headers = [
            [
                'account_code' => '1000',
                'account_name' => 'ASET',
                'classification' => 'ASSET',
                'normal_balance' => 'DEBIT',
                'is_active' => true,
                'parent_id' => null,
            ],
            [
                'account_code' => '2000',
                'account_name' => 'KEWAJIBAN',
                'classification' => 'LIABILITY',
                'normal_balance' => 'CREDIT',
                'is_active' => true,
                'parent_id' => null,
            ],
            [
                'account_code' => '3000',
                'account_name' => 'EKUITAS',
                'classification' => 'EQUITY',
                'normal_balance' => 'CREDIT',
                'is_active' => true,
                'parent_id' => null,
            ],
            [
                'account_code' => '4000',
                'account_name' => 'PENDAPATAN',
                'classification' => 'REVENUE',
                'normal_balance' => 'CREDIT',
                'is_active' => true,
                'parent_id' => null,
            ],
            [
                'account_code' => '5000',
                'account_name' => 'BEBAN OPERASIONAL & UMUM',
                'classification' => 'EXPENSE',
                'normal_balance' => 'DEBIT',
                'is_active' => true,
                'parent_id' => null,
            ],
        ];

        $headerModels = [];
        foreach ($headers as $h) {
            $headerModels[$h['account_code']] = ChartOfAccount::updateOrCreate(
                ['account_code' => $h['account_code']],
                $h
            );
        }

        // 2. Level 2 - Sub Headers
        $subHeaders = [
            [
                'account_code' => '1100',
                'account_name' => 'Aset Lancar',
                'classification' => 'ASSET',
                'normal_balance' => 'DEBIT',
                'is_active' => true,
                'parent_id' => $headerModels['1000']->id,
            ],
            [
                'account_code' => '1200',
                'account_name' => 'Aset Tetap',
                'classification' => 'ASSET',
                'normal_balance' => 'DEBIT',
                'is_active' => true,
                'parent_id' => $headerModels['1000']->id,
            ],
            [
                'account_code' => '2100',
                'account_name' => 'Kewajiban Jangka Pendek',
                'classification' => 'LIABILITY',
                'normal_balance' => 'CREDIT',
                'is_active' => true,
                'parent_id' => $headerModels['2000']->id,
            ],
            [
                'account_code' => '3100',
                'account_name' => 'Modal Pemilik',
                'classification' => 'EQUITY',
                'normal_balance' => 'CREDIT',
                'is_active' => true,
                'parent_id' => $headerModels['3000']->id,
            ],
            [
                'account_code' => '4100',
                'account_name' => 'Pendapatan Jasa & Operasional',
                'classification' => 'REVENUE',
                'normal_balance' => 'CREDIT',
                'is_active' => true,
                'parent_id' => $headerModels['4000']->id,
            ],
            [
                'account_code' => '5100',
                'account_name' => 'Beban Karyawan & Personalia',
                'classification' => 'EXPENSE',
                'normal_balance' => 'DEBIT',
                'is_active' => true,
                'parent_id' => $headerModels['5000']->id,
            ],
            [
                'account_code' => '5200',
                'account_name' => 'Beban Kantor & Umum',
                'classification' => 'EXPENSE',
                'normal_balance' => 'DEBIT',
                'is_active' => true,
                'parent_id' => $headerModels['5000']->id,
            ],
        ];

        $subHeaderModels = [];
        foreach ($subHeaders as $sh) {
            $subHeaderModels[$sh['account_code']] = ChartOfAccount::updateOrCreate(
                ['account_code' => $sh['account_code']],
                $sh
            );
        }

        // 3. Level 3 - Postable Accounts
        $postableAccounts = [
            // Aset Lancar
            [
                'account_code' => '1101',
                'account_name' => 'Kas Operasional',
                'classification' => 'ASSET',
                'normal_balance' => 'DEBIT',
                'is_active' => true,
                'parent_id' => $subHeaderModels['1100']->id,
            ],
            [
                'account_code' => '1102',
                'account_name' => 'Bank Mandiri Giro Operasional',
                'classification' => 'ASSET',
                'normal_balance' => 'DEBIT',
                'is_active' => true,
                'parent_id' => $subHeaderModels['1100']->id,
            ],
            [
                'account_code' => '1103',
                'account_name' => 'Bank BCA Payroll',
                'classification' => 'ASSET',
                'normal_balance' => 'DEBIT',
                'is_active' => true,
                'parent_id' => $subHeaderModels['1100']->id,
            ],
            [
                'account_code' => '1104',
                'account_name' => 'Piutang Usaha',
                'classification' => 'ASSET',
                'normal_balance' => 'DEBIT',
                'is_active' => true,
                'parent_id' => $subHeaderModels['1100']->id,
            ],
            [
                'account_code' => '1105',
                'account_name' => 'Persediaan Barang Gudang & ATK',
                'classification' => 'ASSET',
                'normal_balance' => 'DEBIT',
                'is_active' => true,
                'parent_id' => $subHeaderModels['1100']->id,
            ],

            // Aset Tetap
            [
                'account_code' => '1201',
                'account_name' => 'Peralatan Komputer & IT',
                'classification' => 'ASSET',
                'normal_balance' => 'DEBIT',
                'is_active' => true,
                'parent_id' => $subHeaderModels['1200']->id,
            ],
            [
                'account_code' => '1202',
                'account_name' => 'Akumulasi Penyusutan Peralatan IT',
                'classification' => 'ASSET',
                'normal_balance' => 'CREDIT',
                'is_active' => true,
                'parent_id' => $subHeaderModels['1200']->id,
            ],
            [
                'account_code' => '1203',
                'account_name' => 'Kendaraan Operasional',
                'classification' => 'ASSET',
                'normal_balance' => 'DEBIT',
                'is_active' => true,
                'parent_id' => $subHeaderModels['1200']->id,
            ],

            // Kewajiban Jangka Pendek
            [
                'account_code' => '2101',
                'account_name' => 'Utang Usaha / Vendor',
                'classification' => 'LIABILITY',
                'normal_balance' => 'CREDIT',
                'is_active' => true,
                'parent_id' => $subHeaderModels['2100']->id,
            ],
            [
                'account_code' => '2102',
                'account_name' => 'Utang Gaji & Upah Karyawan',
                'classification' => 'LIABILITY',
                'normal_balance' => 'CREDIT',
                'is_active' => true,
                'parent_id' => $subHeaderModels['2100']->id,
            ],
            [
                'account_code' => '2103',
                'account_name' => 'Utang Pajak PPh 21',
                'classification' => 'LIABILITY',
                'normal_balance' => 'CREDIT',
                'is_active' => true,
                'parent_id' => $subHeaderModels['2100']->id,
            ],
            [
                'account_code' => '2104',
                'account_name' => 'Utang Iuran BPJS Ketenagakerjaan & Kesehatan',
                'classification' => 'LIABILITY',
                'normal_balance' => 'CREDIT',
                'is_active' => true,
                'parent_id' => $subHeaderModels['2100']->id,
            ],

            // Ekuitas
            [
                'account_code' => '3101',
                'account_name' => 'Modal Disetor Saham',
                'classification' => 'EQUITY',
                'normal_balance' => 'CREDIT',
                'is_active' => true,
                'parent_id' => $subHeaderModels['3100']->id,
            ],
            [
                'account_code' => '3201',
                'account_name' => 'Laba Ditahan (Retained Earnings)',
                'classification' => 'EQUITY',
                'normal_balance' => 'CREDIT',
                'is_active' => true,
                'parent_id' => $subHeaderModels['3100']->id,
            ],

            // Pendapatan
            [
                'account_code' => '4101',
                'account_name' => 'Pendapatan Kontrak Software & Solusi ERP',
                'classification' => 'REVENUE',
                'normal_balance' => 'CREDIT',
                'is_active' => true,
                'parent_id' => $subHeaderModels['4100']->id,
            ],

            // Beban Karyawan & Personalia
            [
                'account_code' => '5101',
                'account_name' => 'Beban Gaji Pokok & Tunjangan Tetap',
                'classification' => 'EXPENSE',
                'normal_balance' => 'DEBIT',
                'is_active' => true,
                'parent_id' => $subHeaderModels['5100']->id,
            ],
            [
                'account_code' => '5102',
                'account_name' => 'Beban Uang Lembur',
                'classification' => 'EXPENSE',
                'normal_balance' => 'DEBIT',
                'is_active' => true,
                'parent_id' => $subHeaderModels['5100']->id,
            ],
            [
                'account_code' => '5103',
                'account_name' => 'Beban Iuran BPJS Perusahaan',
                'classification' => 'EXPENSE',
                'normal_balance' => 'DEBIT',
                'is_active' => true,
                'parent_id' => $subHeaderModels['5100']->id,
            ],
            [
                'account_code' => '5104',
                'account_name' => 'Beban Reimbursement & Klaim Pengeluaran',
                'classification' => 'EXPENSE',
                'normal_balance' => 'DEBIT',
                'is_active' => true,
                'parent_id' => $subHeaderModels['5100']->id,
            ],

            // Beban Kantor & Umum
            [
                'account_code' => '5201',
                'account_name' => 'Beban Perlengkapan & ATK Kantor',
                'classification' => 'EXPENSE',
                'normal_balance' => 'DEBIT',
                'is_active' => true,
                'parent_id' => $subHeaderModels['5200']->id,
            ],
            [
                'account_code' => '5202',
                'account_name' => 'Beban Pemeliharaan & Perbaikan Sarana',
                'classification' => 'EXPENSE',
                'normal_balance' => 'DEBIT',
                'is_active' => true,
                'parent_id' => $subHeaderModels['5200']->id,
            ],
            [
                'account_code' => '5203',
                'account_name' => 'Beban Transportasi & Kendaraan Dinas',
                'classification' => 'EXPENSE',
                'normal_balance' => 'DEBIT',
                'is_active' => true,
                'parent_id' => $subHeaderModels['5200']->id,
            ],
        ];

        foreach ($postableAccounts as $acc) {
            ChartOfAccount::updateOrCreate(
                ['account_code' => $acc['account_code']],
                $acc
            );
        }
    }
}
