# Peta Jalan Implementasi (Roadmap) — PT-MANAGEMENT-SISTEM

## Rencana Tahapan Eksekusi Bertahap (7 Phases)

### PHASE 01 — Foundation (Core ERP, Auth & RBAC)
- [x] Inisialisasi arsitektur Laravel 13 & Vite + Tailwind CSS v4 + Alpine.js + Chart.js.
- [x] Konfigurasi koneksi PostgreSQL 18 & Database `pt_management_sistem`.
- [x] Dokumentasi komprehensif (`docs/`).
- [ ] Database Migrations: Core, Organisasi, RBAC, Approval Engine.
- [ ] Seeders: 10 Roles, Permissions inti, Departemen & Jabatan standar PT Indonesia.
- [ ] Artisan Command: `php artisan erp:bootstrap-admin` untuk pembuatan akun Super Admin pertama secara interaktif dan aman.
- [ ] Autentikasi Sesi (Login, Logout, Profil, Ganti Password, Session Timeout).
- [ ] Layout Dashboard Enterprise Responsif (Sidebar collapsible, header, breadcrumbs, user dropdown, flash notifications).
- [ ] CRUD Departemen, Jabatan, dan Master Karyawan.
- [ ] Audit Logging Middleware & Listener.

### PHASE 02 — HRIS & Smart Attendance
- [ ] Migrations: HRIS, Shift, Jadwal, Absensi, Cuti, Koreksi Kehadiran.
- [ ] Manajemen Karyawan Lengkap (Biodata, NIK, Status Kerja, Supervisor).
- [ ] Shift & Penjadwalan Kerja.
- [ ] Antarmuka Absensi Selfie WebRTC Kamera + GPS Geolocation + Watermark Metadata.
- [ ] Riwayat Kehadiran Karyawan & Dashboard Rekap HR.
- [ ] Pengajuan Cuti & Izin (dengan integrasi Approval Engine).
- [ ] Pengajuan & Review Koreksi Absensi.

### PHASE 03 — Operations & Internship
- [ ] Migrations: Sasaran Kerja, Tugas Harian, Bukti Kerja, Interns, Logbook.
- [ ] Manajemen Target Bulanan & Mingguan Departemen.
- [ ] Tugas Harian: Pembuatan tugas oleh supervisor, pengerjaan, unggah bukti kerja.
- [ ] Workflow Review Tugas: Supervisor verifikasi, revisi, approval.
- [ ] Modul Anak Magang: Penugasan pembimbing, logbook harian, evaluasi magang, rekap kehadiran uang saku.

### PHASE 04 — Payroll
- [ ] Migrations: Komponen Gaji, Periode Payroll, Payroll Runs, Payslips, Slip Items.
- [ ] Konfigurasi Komponen Gaji Dinamis (Gaji Pokok, Tunjangan, Potongan, Lembur).
- [ ] Generator Draft Payroll Bulanan berbasis data absensi valid terkunci.
- [ ] Penyesuaian (Adjustment) Payroll & Verifikasi.
- [ ] Workflow Approval Payroll Direksi & Payroll Closing Guard.
- [ ] Halaman Slip Gaji Karyawan (Proteksi data sensitif, cetak slip).

### PHASE 05 — Finance & Accounting
- [ ] Migrations: Chart of Accounts (CoA), Journal Entries, Journal Lines, Budgets, Reimbursements.
- [ ] Master Chart of Accounts standar akuntansi Indonesia.
- [ ] Double-Entry Journal Engine (Validasi mutlak Debit = Credit sebelum posting).
- [ ] Modul Pengeluaran (Expense) & Reimbursement dengan alur approval.
- [ ] Auto-journaling dari pencairan reimbursement dan payroll closing.
- [ ] Laporan Buku Besar (General Ledger) & Neraca Saldo.

### PHASE 06 — Procurement, Inventory & General Affairs
- [ ] Migrations: Vendors, PR, PO, Goods Receipts, Warehouses, Items, Stock Movements, Assets, BAST, GA.
- [ ] Manajemen Rekanan Vendor.
- [ ] Purchase Request (PR) & Approval Pengadaan.
- [ ] Penerbitan Purchase Order (PO).
- [ ] Pencatatan Penerimaan Barang (Goods Receipt) & Three-Way Matching.
- [ ] Gudang & Kartu Stok Inventaris (Stok masuk, keluar, penyesuaian).
- [ ] Register Aset Tetap, BAST Penugasan Aset ke Pegawai, Jadwal Maintenance.
- [ ] General Affairs: Pengajuan kebutuhan ATK/fasilitas kantor.

### PHASE 07 — Executive Dashboard & Integration Testing
- [ ] Dashboard Direksi / Eksekutif Terpadu: Real-time KPI feed.
- [ ] Visualisasi Grafik Kehadiran, Target Kerja, dan Keuangan via Chart.js.
- [ ] Filter dan Export Laporan Lintas Modul.
- [ ] Pengujian Otomatis (PHPUnit / Pest Feature & Unit Tests).
- [ ] Security Audit, Validasi Hak Akses & UAT Final Checklist.
