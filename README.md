# PT Management Sistem — Enterprise ERP & Operational System

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-18.x-316192?style=for-the-badge&logo=postgresql&logoColor=white)](https://www.postgresql.org)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.4%2B-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Alpine.js](https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=for-the-badge&logo=alpinedotjs&logoColor=white)](https://alpinejs.dev)
[![Tests](https://img.shields.io/badge/Tests-71%20Passed%20(100%25)-success?style=for-the-badge&logo=phpunit&logoColor=white)](tests/)

**PT Management Sistem** adalah sistem Enterprise Resource Planning (ERP) dan manajemen operasional modern terpadu yang dirancang untuk mendukung operasional bisnis berstandar Indonesia. Sistem ini mengintegrasikan seluruh fungsi korporasi mulai dari Human Resource Information System (HRIS), presensi cerdas berbasis WebRTC dan GPS Geofencing, manajemen tugas operasional dan magang, payroll kalkulasi PPh 21 & BPJS, pembukuan akuntansi double-entry (Chart of Accounts, Jurnal, Buku Besar, Neraca Saldo, Reimbursement), pengadaan barang (PR, PO, 3-Way Matching), manajemen persediaan multi-gudang dan aset tetap, hingga Executive KPI Dashboard dan multi-level approval engine.

---

## Daftar Isi
- [Fitur Utama & Arsitektur Modul](#fitur-utama--arsitektur-modul)
- [Tech Stack](#tech-stack)
- [Prasyarat Sistem](#prasyarat-sistem)
- [Panduan Instalasi & Konfigurasi](#panduan-instalasi--konfigurasi)
- [Migrasi Database & Database Seeder](#migrasi-database--database-seeder)
- [Tabel Akun Default (10 Role Enterprise)](#tabel-akun-default-10-role-enterprise)
- [Pengujian Otomatis (Automated Testing)](#pengujian-otomatis-automated-testing)
- [Menjalankan Server Lokal](#menjalankan-server-lokal)
- [Struktur Direktori Proyek](#struktur-direktori-proyek)
- [Keamanan & Standar Kepatuhan](#keamanan--standar-kepatuhan)

---

## Fitur Utama & Arsitektur Modul

Sistem dibangun dengan prinsip modularitas tinggi, memisahkan domain model ke dalam namespace `App\Models\*` dan business service layer di `App\Services\*`:

### 1. Core System & Role-Based Access Control (RBAC)
- Autentikasi aman berbasis sesi dengan proteksi CSRF, throttle login, dan audit logging polymorphic (`audit_logs`).
- 10 Enterprise Roles dengan 40+ granular permissions yang membatasi hak akses tiap modul (lihat [ROLE_PERMISSION_MATRIX.md](docs/ROLE_PERMISSION_MATRIX.md)).
- Multi-role support, manajemen departemen dengan cost center, dan profil perusahaan (`company_settings`).

### 2. HRIS & Smart Attendance (WebRTC + GPS Geofencing)
- **WebRTC Camera Stream**: Pengambilan foto selfie kehadiran secara real-time via peramban tanpa plug-in eksternal.
- **GPS Geofence (Haversine Formula)**: Validasi jarak presisi dalam radius meter dari titik koordinat kantor pusat. Status kehadiran otomatis: `ON_TIME`, `LATE`, atau `OUT_OF_RADIUS`.
- **Private Storage**: Foto selfie absensi disimpan terenkripsi di private path (`storage/app/private/attendance/selfies/`) dan diakses hanya lewat otorisasi controller.
- **Cuti & Koreksi Kehadiran**: Manajemen cuti berjenjang dengan pemotongan kuota cuti tahunan secara atomik dan workflow koreksi absensi.

### 3. Operations & Task Management
- Manajemen target bulanan per divisi dan penugasan harian (Daily Tasks) kepada staf.
- Unggah berkas bukti kerja (*work evidence attachments*) ke private storage.
- Siklus review supervisor: `APPROVED`, `REVISION` (dengan catatan perbaikan wajib), atau `REJECTED`.

### 4. Internship Program Management
- Direktori peserta magang aktif terisolasi dengan penunjukan mentor senior dari divisi terkait.
- Pengisian logbook harian oleh pemagang dengan review dan verifikasi bertahap oleh mentor.
- Formulir evaluasi dan penilaian akhir kompetensi magang berbobot angka & predikat kelulusan.

### 5. Payroll Management (Standar Ketenagakerjaan Indonesia)
- Konfigurasi komponen gaji: Penghasilan Tetap, Tunjangan Kehadiran, Tunjangan Transport, Lembur, dan Potongan Absensi.
- Kalkulasi otomatis Pajak Penghasilan (PPh 21) dan iuran BPJS Ketenagakerjaan (JKK, JKM, JHT, JP) & BPJS Kesehatan.
- Siklus payroll period: Draft kalkulasi masal, penyesuaian nominal, persetujuan direksi, penguncian periode (*locked*), dan cetak slip gaji karyawan format resmi.

### 6. Finance & Double-Entry Accounting
- **Chart of Accounts (CoA)**: Standar bagan akun Indonesia dengan struktur hierarki 5 kelompok utama (Aset, Kewajiban, Ekuitas, Pendapatan, Beban).
- **Double-Entry Journal Engine**: Validasi ketat keseimbangan akuntansi (`sum(debit) == sum(credit)`). Sistem menolak posting jika jurnal tidak berimbang atau bernilai negatif.
- **Laporan Keuangan**: Buku Besar (*General Ledger*) per akun dengan saldo berjalan dan Neraca Saldo (*Trial Balance*).
- **Reimbursement Otomatis**: Pengajuan klaim biaya operasional karyawan dengan bukti nota, approval berjenjang, dan *auto-journaling* langsung ke akun Kas/Bank dan Beban Operasional saat disetujui.

### 7. Procurement & Purchasing
- Direktori master vendor terdaftar beserta evaluasi performa dan NPWP.
- Siklus pengadaan barang: Purchase Request (PR) dari divisi pengguna, persetujuan manajerial, penerbitan Purchase Order (PO), dan *Goods Receipt* (GR) dengan verifikasi *3-way matching* kuantitas pesanan.

### 8. Warehouse Inventory & Fixed Asset Management
- Multi-gudang dan pencatatan master barang inventaris/bahan baku dengan minimum buffer stock.
- Kartu stok otomatis mencatat mutasi stok: barang masuk (`IN`), barang keluar (`OUT`), dan penyesuaian opname (`ADJUSTMENT`).
- Register aset tetap perusahaan, Berita Acara Serah Terima (BAST) peminjaman aset karyawan, dan riwayat pemeliharaan berkala (*maintenance records*).

### 9. Executive KPI Dashboard & Multi-Level Approvals
- **Executive Command Center**: Dashboard eksekutif real-time khusus Direksi dan Super Admin yang mengagregasi KPI dari 10 modul bisnis.
- **Operational Risk Radar**: Peringatan dini otomatis untuk keterlambatan presensi tinggi, tugas mendekati/melewati tenggat, stok di bawah batas minimum, dan Purchase Request yang belum diproses.
- **Multi-Level Approval Engine**: Penanganan persetujuan terpusat lintas modul (Cuti, Reimbursement, Purchase Request, Payroll Period) dengan riwayat jejak audit lengkap.

---

## Tech Stack

| Komponen | Teknologi | Keterangan |
|---|---|---|
| **Backend Framework** | Laravel 12.x / 13.x | Arsitektur MVC, Eloquent ORM, Service Layer, Middleware Auth |
| **Bahasa Pemrograman** | PHP 8.3+ / 8.4+ | Type hints ketat, typed properties, constructor property promotion |
| **Database** | PostgreSQL 18.x | Transaksional ACID, JSONB, native indexing, geometry & Haversine support |
| **Frontend Styling** | Tailwind CSS 3.4+ | Antarmuka modern bernuansa korporat, responsive layout, dark sidebar |
| **Frontend Reaktivitas** | Alpine.js 3.x | Interaktivitas live (kalkulator jurnal, form dinamis, modal dialog) |
| **Visualisasi Data** | Chart.js 4.x | Grafik tren realisasi anggaran vs beban, distribusi status kehadiran |
| **Penyimpanan Berkas** | Laravel Private Storage | Akses berkas privat melalui controller streaming terautentikasi |
| **Unit & Feature Testing** | PHPUnit 11.x | 71 Test cases komprehensif, 364 assertions, 100% lulus pengujian |

---

## Prasyarat Sistem

Sebelum melakukan instalasi, pastikan lingkungan pengembangan Anda telah memenuhi prasyarat berikut:

1. **PHP 8.3** atau versi lebih tinggi dengan ekstensi:
   - `pdo_pgsql` & `pgsql`
   - `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `curl`
   - `fileinfo` & `gd` (untuk verifikasi upload berkas gambar & slip)
2. **Composer 2.x** ([getcomposer.org](https://getcomposer.org))
3. **Node.js 18.x+** & **NPM 9.x+** ([nodejs.org](https://nodejs.org))
4. **PostgreSQL 18.x** (atau PostgreSQL minimal versi 14.x) aktif berjalan di port `5432`

---

## Panduan Instalasi & Konfigurasi

### 1. Clone Repositori
```bash
git clone https://github.com/your-org/Management-Sistem.git
cd Management-Sistem
```

### 2. Instalasi Dependensi PHP & Node
```bash
composer install
npm install
npm run build
```

### 3. Konfigurasi Lingkungan (`.env`)
Salin file konfigurasi contoh dan buat file `.env`:
```bash
cp .env.example .env
```

Buka file `.env` dan sesuaikan kredensial koneksi PostgreSQL:
```env
APP_NAME="PT Management Sistem"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=Asia/Jakarta
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=pt_management_sistem
DB_USERNAME=postgres
DB_PASSWORD=your_postgres_password
```

> **Catatan Database:** Pastikan database `pt_management_sistem` telah dibuat di server PostgreSQL Anda sebelum menjalankan migrasi:
> ```sql
> CREATE DATABASE pt_management_sistem;
> ```

### 4. Generate Application Key & Storage Link
```bash
php artisan key:generate
php artisan storage:link
```

---

## Migrasi Database & Database Seeder

Sistem menggunakan 11 migrasi PostgreSQL yang bersih dan berurutan secara transaksional dengan 5 seeder terintegrasi.

### Menjalankan Migrasi Bersih & Seeding Lengkap
Jalankan perintah berikut untuk menginisialisasi skema database dari awal beserta seluruh data master organisasi, bagan akun standar Indonesia, shift kerja, komponen gaji, dan 10 akun default:

```bash
php artisan migrate:fresh --seed
```

Hasil migrasi dan seeder mencakup:
- 11 File migrasi struktural tanpa konflik relasi foreign key.
- 62 Tabel sistem di PostgreSQL.
- Master Roles & Hak Akses (10 roles, 40+ permissions terpetakan).
- Profil Korporasi PT Management Sistem, 5 Departemen, dan 10 Jabatan.
- Master Shift Kerja (Normal, Pagi, Siang).
- Bagan Akun Standar Indonesia (Chart of Accounts) dengan kode akun 1000 s/d 5999.
- Master Barang Gudang, Gudang Utama, Jenis Cuti, dan Komponen Gaji Dasar.
- 10 Akun Default terverifikasi dengan data karyawan dan saldo cuti awal.

### Perintah Khusus: Bootstrap Super Admin CLI
Untuk menginisialisasi atau mereset akun Super Administrator secara mandiri melalui terminal:

```bash
# Menggunakan nilai default sistem (admin@managementsistem.co.id)
php artisan erp:bootstrap-admin

# Atau menentukan nama, email, dan password kustom
php artisan erp:bootstrap-admin --name="Chief Technology Officer" --email="cto@perusahaan.com" --password="SuperSecretPassword!"
```

---

## Tabel Akun Default (10 Role Enterprise)

Seluruh 10 akun default di bawah ini telah di-seed secara otomatis oleh `DatabaseSeeder.php` dengan kata sandi seragam:

> **Kata Sandi Default Semua Akun:** `password123`

| No | Nama Lengkap | Role Code | Alamat Email | Departemen / Jabatan | NIK | Modul Utama & Hak Akses |
|:--:|---|---|---|---|---|---|
| **1** | **Super Administrator** | `super_admin` | `admin@managementsistem.co.id` | Direksi / Direktur Utama | `EMP-0001` | **Full Root Access**: Kelola User, Role, Permissions, Audit Trail, Pengaturan Sistem Global. |
| **2** | **Direktur Operasional** | `director` | `direksi@managementsistem.co.id` | Direksi / Direktur Ops & Keuangan | `EMP-0002` | **Executive Command Center**: Monitoring KPI lintas divisi, High-Value Approval, Laporan Finansial. |
| **3** | **HR Administrator** | `hr_admin` | `hr@managementsistem.co.id` | HRD / HR Manager | `EMP-0003` | **SDM & HRIS**: Kelola Pegawai, Kontrak, Approval Cuti/Izin, Rekap Absensi, Jadwal Shift. |
| **4** | **Supervisor Operasional**| `supervisor` | `supervisor@managementsistem.co.id` | Ops / Lead Software Engineer | `EMP-0004` | **Operasional & Tim**: Delegasi Target & Task, Review Bukti Kerja, Verifikasi Logbook Magang. |
| **5** | **Budi Santoso** | `employee` | `karyawan@managementsistem.co.id` | Ops / Fullstack Software Engineer | `EMP-0005` | **Employee Self-Service (ESS)**: Presensi WebRTC/GPS, Submit Tugas, Permohonan Cuti, Slip Gaji. |
| **6** | **Ahmad Fauzi Magang** | `intern` | `magang@managementsistem.co.id` | Ops / Intern Software Engineer | `EMP-0006` | **Intern Portal**: Presensi mandiri, Pengisian Logbook harian, Pengerjaan tugas magang (Terisolasi). |
| **7** | **Petugas Payroll** | `payroll_officer` | `payroll@managementsistem.co.id` | HRD / HR & Payroll Specialist | `EMP-0007` | **Payroll Engine**: Komponen Gaji, Draft Payroll Periode, Kalkulasi PPh 21 & BPJS, Terbitkan Slip. |
| **8** | **Petugas Keuangan** | `finance_officer` | `finance@managementsistem.co.id` | Finance / Senior Accounting | `EMP-0008` | **Finance & Accounting**: Chart of Accounts, Jurnal Double-Entry, Buku Besar, Neraca Saldo, Reimbursement. |
| **9** | **Petugas Pengadaan** | `procurement_officer`| `procurement@managementsistem.co.id` | Pengadaan / Procurement Specialist| `EMP-0009` | **Procurement**: Master Vendor, Purchase Request (PR), Purchase Order (PO), Verifikasi Penerimaan Barang (GR). |
| **10**| **Petugas Gudang & GA** | `inventory_ga` | `inventory@managementsistem.co.id` | Gudang / Warehouse Supervisor | `EMP-0010` | **Logistik & GA**: Stok Gudang, Mutasi Barang, Register Aset Tetap, BAST Penugasan Aset, Pemeliharaan. |

---

## Pengujian Otomatis (Automated Testing)

Proyek ini dilengkapi dengan suite pengujian otomatis fitur dan integrasi (*Automated Feature Tests*) berbasis PHPUnit. Seluruh pengujian berjalan secara transaksional dengan database PostgreSQL:

```bash
# Menjalankan seluruh pengujian (71 Feature Tests)
php artisan test
```

### Menjalankan Pengujian per Modul Spesifik
```bash
# Pengujian Core, Auth & RBAC
php artisan test tests/Feature/CoreAndRbacTest.php

# Pengujian HRIS, WebRTC Selfie & Smart Attendance
php artisan test tests/Feature/HrisAndAttendanceTest.php

# Pengujian Operations, Daily Tasks & Logbook Magang
php artisan test tests/Feature/OperationsAndInternshipTest.php

# Pengujian Payroll, Pajak PPh 21 & Slip Gaji
php artisan test tests/Feature/PayrollManagementTest.php

# Pengujian Finance, Double-Entry Accounting & Reimbursement
php artisan test tests/Feature/FinanceAndAccountingTest.php

# Pengujian Procurement, Multi-Warehouse & Register Aset
php artisan test tests/Feature/ProcurementAndInventoryTest.php

# Pengujian Executive Dashboard & Cross-Module Integration
php artisan test tests/Feature/ExecutiveAndIntegrationTest.php
```

### Metrik Pengujian Saat Ini
```
Tests:    71 passed (100%)
Asserts:  364 passed
Duration: ~9.8 detik
Status:   STABLE & VERIFIED
```

---

## Menjalankan Server Lokal

Untuk menjalankan aplikasi pada lingkungan pengembangan lokal:

### 1. Jalankan Backend Server Laravel
```bash
php artisan serve
```
Server web lokal akan aktif di: `http://localhost:8000`

### 2. Jalankan Vite Hot-Reload (Terminal Terpisah)
Untuk pengembangan antarmuka secara live dengan hot module replacement (HMR):
```bash
npm run dev
```

Buka peramban favorit Anda dan kunjungi `http://localhost:8000/login`. Masuk menggunakan salah satu dari [10 Akun Default](#tabel-akun-default-10-role-enterprise) di atas.

---

## Struktur Direktori Proyek

```
Management-Sistem/
├── app/
│   ├── Console/Commands/       # Custom Artisan CLI (erp:bootstrap-admin)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Attendance/     # WebRTC Selfie & Geofence GPS Controller
│   │   │   ├── Executive/      # Executive KPI Command Center Controller
│   │   │   ├── Finance/        # Accounting, CoA, Journal & Reimbursement Controller
│   │   │   ├── HRIS/           # Pegawai, Cuti & Shift Controller
│   │   │   ├── Internship/     # Logbook & Evaluasi Magang Controller
│   │   │   ├── Inventory/      # Gudang, Stok & Register Aset Controller
│   │   │   ├── Operations/     # Target & Daily Tasks Controller
│   │   │   ├── Payroll/        # Kalkulasi Gaji & Slip Controller
│   │   │   └── Procurement/    # Vendor, PR & PO Controller
│   │   └── Middleware/         # Auth, RBAC & Role Check Middlewares
│   ├── Models/
│   │   ├── Approval/           # ApprovalRequest, ApprovalStep
│   │   ├── Attendance/         # AttendanceRecord, AttendanceCorrection
│   │   ├── Core/               # User, Role, Permission, Department, AuditLog
│   │   ├── Finance/            # ChartOfAccount, JournalEntry, Reimbursement
│   │   ├── HRIS/               # Employee, LeaveRequest, LeaveBalance, Shift
│   │   ├── Internship/         # Intern, InternshipLogbook, Evaluation
│   │   ├── Inventory/          # Warehouse, Item, StockMovement, Asset
│   │   ├── Operations/         # WorkTarget, Task, TaskAttachment, Review
│   │   ├── Payroll/            # SalaryComponent, PayrollPeriod, Payslip
│   │   └── Procurement/        # Vendor, PurchaseRequest, PurchaseOrder, GR
│   └── Services/               # Business Logic Services (Accounting, Attendance, etc.)
├── database/
│   ├── migrations/             # 11 Transaksional PostgreSQL Migrations
│   └── seeders/                # Database, Role, Org, CoA & Master Seeders
├── docs/                       # Dokumentasi Teknis, ERD, Matriks & Panduan Arsitektur
├── resources/
│   ├── css/                    # Tailwind CSS source files
│   ├── js/                     # Alpine.js & JavaScript modules
│   └── views/                  # Blade Templates terstruktur per modul
├── routes/
│   └── web.php                 # Rute terproteksi middleware auth & modular prefix
└── tests/
    └── Feature/                # 71 Automated Feature Tests (100% Passing)
```

---

## Keamanan & Standar Kepatuhan

- **Data Privacy & Storage Protection**: Berkas sensitif (foto selfie presensi, bukti pengerjaan tugas, kuitansi reimbursement, dan slip gaji) tidak disimpan di direktori `public`, melainkan di private storage `storage/app/private/` dengan akses aman melalui streaming controller yang mengecek hak otorisasi pengguna.
- **Double-Entry Invariance**: Modul akuntansi mengunci integritas buku besar dengan menolak mutlak setiap posting jurnal yang tidak memenuhi kaidah matematika debit = kredit.
- **Geofencing GPS Spoof Prevention**: Titik koordinat absensi diperiksa menggunakan formula trigonometri Haversine di sisi server untuk mencegah manipulasi lokasi klien.
- **Audit Trails**: Perubahan krusial pada data sistem dicatat ke dalam tabel audit log polimorfik untuk akuntabilitas operasional enterprise.

---

## Lisensi
Sistem ini dikembangkan khusus untuk **PT Management Sistem Indonesia** sebagai solusi Enterprise Resource Planning terintegrasi. Hak Cipta dilindungi undang-undang.
