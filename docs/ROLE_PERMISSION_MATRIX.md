# Matriks Role & Permission — PT-MANAGEMENT-SISTEM

## 1. Daftar 10 Role Utama
1. **Super Admin (`super_admin`)**: Pengelolaan sistem, user, roles, permissions, konfigurasi global, audit logs.
2. **Admin / HR (`hr_admin`)**: Pengelolaan data karyawan, kontrak, cuti/izin/sakit, absensi, jadwal shift.
3. **Senior / Supervisor (`supervisor`)**: Pengelolaan penugasan target & tugas harian tim, approval review pekerjaan, evaluasi intern.
4. **Karyawan (`employee`)**: Akses profil mandiri, selfie check-in/out, pengajuan cuti/koreksi, pengerjaan tugas, submit bukti kerja.
5. **Anak Magang (`intern`)**: Absensi mandiri, pengisian logbook harian, submit tugas magang. Terisolasi dari data pegawai & keuangan.
6. **Tim Payroll (`payroll_officer`)**: Konfigurasi komponen gaji, pembuatan draft payroll bulanan, rekap kehadiran, kalkulasi slip gaji.
7. **Finance / Accounting (`finance_officer`)**: Pengelolaan Chart of Accounts (CoA), pembukuan jurnal double-entry, kas masuk/keluar, approval pengeluaran/reimbursement, budgeting.
8. **Procurement / Purchasing (`procurement_officer`)**: Manajemen vendor, pemrosesan Purchase Request (PR), penerbitan Purchase Order (PO), verifikasi penerimaan barang (3-way matching).
9. **Inventory / General Affairs (`inventory_ga`)**: Pengelolaan stok barang gudang, mutasi inventaris, register aset tetap, BAST penugasan aset, jadwal pemeliharaan fasilitas.
10. **Direksi / Management (`director`)**: Executive KPI Dashboard, pemantauan seluruh lini operasional, laporan keuangan tingkat tinggi, High-Value Approval.

## 2. Multi-Role Support
Sistem mengizinkan satu user memiliki lebih dari satu role melalui tabel relasi pivot `user_roles`. Scope otorisasi dievaluasi secara kumulatif atau berbasis konteks divisi.

## 3. Matriks Hak Akses Modul
| Modul / Kemampuan | Super Admin | HR Admin | Supervisor | Karyawan | Anak Magang | Tim Payroll | Finance | Procurement | Inventory/GA | Direksi |
|---|---|---|---|---|---|---|---|---|---|---|
| User & System Settings | CRUD | - | - | - | - | - | - | - | - | R |
| Audit Trail | CRUD | R | - | - | - | - | - | - | - | R |
| Master Departemen & Jabatan | CRUD | CRUD | R | R | - | R | R | R | R | R |
| Profil Karyawan & Kontrak | R | CRUD | R (tim) | Self | - | R | - | - | - | R |
| Absensi Selfie & GPS | R | CRUD | R/Approve (tim) | Self | Self | R | - | - | - | R |
| Koreksi Absensi & Cuti | - | Approve | Approve (tim) | Create (self) | - | - | - | - | - | R |
| Target & Daily Tasks | R | - | CRUD (tim) | Update (self) | Update (self) | - | - | - | - | R |
| Logbook Magang | R | R | Approve (tim) | - | Create (self) | - | - | - | - | R |
| Konfigurasi Komponen Gaji | CRUD | R | - | - | - | CRUD | R | - | - | R |
| Draft Payroll & Perhitungan | R | R | - | - | - | CRUD | R/Review | - | - | Approve |
| Slip Gaji | - | - | - | Self | - | R | R | - | - | R |
| Chart of Accounts & Jurnal | R | - | - | - | - | - | CRUD | - | - | R |
| Reimbursement & Expense | R | - | Approve (tim) | Create (self) | - | - | Approve/Post | - | - | Approve (high) |
| Vendor & PO / PR | R | - | Approve (dept) | - | - | - | R/Payment | CRUD | - | Approve (high) |
| Barang Gudang & Mutasi Stok | R | - | - | - | - | - | - | R | CRUD | R |
| Register Aset Tetap & BAST | R | R | - | Self (view pinjam) | Self (view pinjam) | - | R | - | CRUD | R |
| General Affairs & Fasilitas | R | - | Approve (dept) | Create (self) | Create (self) | - | - | - | CRUD | R |
| Executive KPI Dashboard | Full | HR Only | Team Only | Self Only | Self Only | Payroll Only | Fin Only | Proc Only | Inv Only | Full High-Level |
