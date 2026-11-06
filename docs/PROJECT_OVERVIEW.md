# PT-MANAGEMENT-SISTEM — Enterprise Resource Planning (ERP)

## Ringkasan Proyek
PT-Management-Sistem adalah aplikasi Full Enterprise Resource Planning (ERP) berbasis web yang dirancang khusus untuk operasional internal **SATU perusahaan (single-company / non multi-tenant)**. Sistem ini mengintegrasikan seluruh lini bisnis inti perusahaan dari kehadiran karyawan, manajemen tugas operasional harian, program magang, penggajian (payroll), akuntansi keuangan double-entry, pengadaan (procurement), inventaris & aset, hingga analitik eksekutif.

## Prinsip Desain & Arsitektur
- **Model Arsitektur:** Modular Monolith (Laravel 13 + PostgreSQL)
- **Database Terpusat:** Satu basis data relasional PostgreSQL dengan foreign key, transactional integrity, concurrency check, dan audit logs.
- **Frontend Terpadu:** Laravel Blade didukung Tailwind CSS, Alpine.js untuk interaktivitas reaktif, dan Chart.js untuk visualisasi data eksekutif.
- **Keamanan Default:** RBAC (Role-Based Access Control) multi-role terpusat, Form Request Validation, Policy/Gate backend check, session security, private storage untuk dokumen & foto sensitif.
- **Reusable Approval Engine:** Mesin persetujuan berjenjang multi-level yang dapat dipakai bersama oleh seluruh modul (Cuti, Koreksi Absensi, Tugas, Payroll, PR, PO, Expense, Reimbursement, Aset).

## Modul-Modul Utama
1. **Core ERP & Administrasi Sistem:** Autentikasi sesi, RBAC (10 Roles), struktur organisasi, departemen, jabatan, profil perusahaan, pengaturan sistem, audit log.
2. **HRIS (Human Resource Information System):** Profil karyawan lengkap, kontrak kerja, shift, cuti, izin, sakit, riwayat kepegawaian.
3. **Smart Attendance:** Absensi selfie dengan timestamp server, koordinat GPS, validasi akurasi, watermarking metadata, koreksi kehadiran, rekapitulasi terintegrasi payroll.
4. **Work Management & Operational Timeline:** Sasaran bulanan, rencana mingguan, daily tasks, workflow submit -> review -> approve/revisi, audit bukti pengerjaan.
5. **Internship Management:** Profil anak magang, mentor pembimbing, logbook harian terverifikasi, tugas magang, uang saku.
6. **Payroll Management:** Komponen gaji dinamis, tunjangan, potongan, lembur, periode payroll, perhitungan draft, slip gaji terproteksi, payroll lock gate.
7. **Finance & Accounting:** Chart of Accounts (CoA), pembukuan double-entry berimbang, general ledger, kas masuk/keluar, petty cash, reimbursement, expense, budgeting.
8. **Procurement & Purchasing:** Master vendor, Purchase Request (PR), approval berjenjang, Purchase Order (PO), goods receipt (3-way matching), utang vendor.
9. **Inventory & Asset Management:** Master item & gudang, mutasi stok, stock card audit, register aset tetap, BAST penugasan aset, jadwal maintenance.
10. **General Affairs (GA):** Permintaan fasilitas/kebutuhan kantor, kendaraan operasional, request perbaikan.
11. **Management & Executive Dashboard:** Real-time KPI feed, grafik kehadiran, progres operasional, burn rate pengeluaran, peringatan risiko operasional.

## Tech Stack Aktif
| Komponen | Spesifikasi Terpasang |
|---|---|
| OS | Windows (NT 10.0 AMD64) |
| Backend Runtime | PHP 8.3.16 (cli) |
| Framework | Laravel Framework 13.40.0 |
| Database Engine | PostgreSQL 18 (Port 5432) |
| Database Name | `pt_management_sistem` |
| Driver DB PHP | `pdo_pgsql`, `pgsql` |
| Package Manager | Composer 2.8.10 & NPM 10.9.3 |
| Node.js Runtime | Node.js v22.20.0 |
| Styling & UI | Tailwind CSS v4, Alpine.js, Chart.js, Lucide Icons |
| Timezone | `Asia/Jakarta` (WIB, GMT+7) |
