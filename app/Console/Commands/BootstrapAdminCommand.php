<?php

namespace App\Console\Commands;

use App\Models\Core\Role;
use App\Models\Core\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class BootstrapAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'erp:bootstrap-admin
                            {--name= : Nama Lengkap Super Admin}
                            {--email= : Alamat Email Super Admin}
                            {--password= : Kata Sandi Baru}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inisialisasi akun Super Admin dan 10 master role enterprise PT Management Sistem';

    /**
     * The 10 standard enterprise roles from ROLE_PERMISSION_MATRIX.md.
     */
    protected array $standardRoles = [
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
            'description' => 'Akses profil mandiri, selfie check-in/out, pengajuan cuti/koreksi, pengerjaan tugas.',
        ],
        [
            'code' => 'intern',
            'name' => 'Anak Magang',
            'description' => 'Absensi mandiri, pengisian logbook harian, submit tugas magang.',
        ],
        [
            'code' => 'payroll_officer',
            'name' => 'Tim Payroll',
            'description' => 'Konfigurasi komponen gaji, pembuatan draft payroll bulanan, rekap kehadiran, kalkulasi slip gaji.',
        ],
        [
            'code' => 'finance_officer',
            'name' => 'Finance / Accounting',
            'description' => 'Chart of Accounts (CoA), pembukuan jurnal double-entry, kas masuk/keluar, approval pengeluaran/reimbursement.',
        ],
        [
            'code' => 'procurement_officer',
            'name' => 'Procurement / Purchasing',
            'description' => 'Manajemen vendor, pemrosesan Purchase Request (PR), Purchase Order (PO), verifikasi 3-way matching.',
        ],
        [
            'code' => 'inventory_ga',
            'name' => 'Inventory / General Affairs',
            'description' => 'Pengelolaan stok barang gudang, mutasi inventaris, register aset tetap, BAST, jadwal pemeliharaan fasilitas.',
        ],
        [
            'code' => 'director',
            'name' => 'Direksi / Management',
            'description' => 'Executive KPI Dashboard, pemantauan seluruh lini operasional, laporan keuangan, High-Value Approval.',
        ],
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('====================================================');
        $this->info('   PT MANAGEMENT SISTEM — BOOTSTRAP SUPER ADMIN     ');
        $this->info('====================================================');

        // 1. Ensure all standard roles exist
        $this->output->write('Memeriksa & memastikan 10 master role enterprise...');
        foreach ($this->standardRoles as $roleData) {
            Role::firstOrCreate(
                ['code' => $roleData['code']],
                [
                    'name' => $roleData['name'],
                    'description' => $roleData['description'],
                ]
            );
        }
        $this->info(' [OK]');

        $superAdminRole = Role::where('code', 'super_admin')->first();
        if (! $superAdminRole) {
            $this->error('Gagal memuat role super_admin.');

            return Command::FAILURE;
        }

        // 2. Obtain credentials
        $name = $this->option('name') ?: $this->ask('Masukkan Nama Lengkap Super Admin', 'Super Administrator');
        while (empty(trim($name))) {
            $this->error('Nama tidak boleh kosong.');
            $name = $this->ask('Masukkan Nama Lengkap Super Admin');
        }

        $email = $this->option('email');
        if (! $email) {
            $email = $this->ask('Masukkan Email Super Admin', 'admin@pt-management.test');
        }

        $validator = Validator::make(['email' => $email], [
            'email' => ['required', 'email'],
        ]);

        while ($validator->fails()) {
            $this->error($validator->errors()->first('email'));
            $email = $this->ask('Masukkan Email Super Admin yang valid');
            $validator = Validator::make(['email' => $email], ['email' => ['required', 'email']]);
        }

        $password = $this->option('password');
        if (! $password) {
            $password = $this->secret('Masukkan Password Super Admin (min. 8 karakter)');
            while (strlen($password ?? '') < 8) {
                $this->error('Password minimal harus 8 karakter.');
                $password = $this->secret('Masukkan Password Super Admin (min. 8 karakter)');
            }

            $passwordConfirmation = $this->secret('Konfirmasi Password');
            while ($password !== $passwordConfirmation) {
                $this->error('Konfirmasi kata sandi tidak cocok.');
                $password = $this->secret('Masukkan Password Super Admin (min. 8 karakter)');
                $passwordConfirmation = $this->secret('Konfirmasi Password');
            }
        } elseif (strlen($password) < 8) {
            $this->error('Password yang diberikan melalui opsi minimal harus 8 karakter.');

            return Command::FAILURE;
        }

        // 3. Create or update user
        $user = User::where('email', $email)->first();

        if ($user) {
            $this->line("Akun dengan email <fg=yellow>{$email}</> sudah ada. Memperbarui kredensial...");
            $user->name = $name;
            $user->password = Hash::make($password);
            $user->is_active = true;
            $user->save();
        } else {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }

        // 4. Attach super_admin role if not attached
        if (! $user->roles()->where('code', 'super_admin')->exists()) {
            $user->roles()->syncWithoutDetaching([$superAdminRole->id]);
        }

        $this->newLine();
        $this->info('✓ Akun Super Administrator berhasil dikonfigurasi!');
        $this->table(
            ['ID', 'Nama', 'Email', 'Role', 'Status'],
            [
                [
                    $user->id,
                    $user->name,
                    $user->email,
                    $superAdminRole->name.' ('.$superAdminRole->code.')',
                    $user->is_active ? 'AKTIF' : 'NONAKTIF',
                ],
            ]
        );

        $this->line('Anda sekarang dapat masuk ke sistem dengan kredensial di atas.');

        return Command::SUCCESS;
    }
}
