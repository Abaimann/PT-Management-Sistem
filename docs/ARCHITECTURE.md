# Arsitektur Sistem — PT-MANAGEMENT-SISTEM

## 1. Arsitektur Umum (Modular Monolith)
Sistem dibangun menggunakan pola **Modular Monolith** berbasis Laravel 13 dan PostgreSQL 18. Pendekatan ini dipilih karena:
- Sistem ditujukan untuk operasional internal **satu perusahaan** (single-company).
- Menghindari overhead jaringan, distributed transaction, dan latensi mikroservis.
- Memungkinkan integritas referensial foreign key dan transaksi database ACID di seluruh domain (seperti Payroll yang menjurnal otomatis ke General Ledger).
- Memisahkan domain bisnis menjadi modul-modul logis yang teratur.

## 2. Struktur Modul & Layering
Setiap modul menerapkan prinsip **Separation of Concerns (SoC)**:
```
app/
├── Models/                     # Eloquent Models & Relasi Relasional
│   ├── Core/
│   ├── HRIS/
│   ├── Operations/
│   ├── Internship/
│   ├── Payroll/
│   ├── Finance/
│   ├── Procurement/
│   └── Inventory/
├── Http/
│   ├── Controllers/            # Hanya memproses HTTP Request & Response
│   │   ├── Auth/
│   │   ├── Core/
│   │   ├── HRIS/
│   │   ├── Operations/
│   │   ├── Internship/
│   │   ├── Payroll/
│   │   ├── Finance/
│   │   ├── Procurement/
│   │   ├── Inventory/
│   │   └── Executive/
│   ├── Requests/               # Form Request Validation (Validasi Server-Side)
│   ├── Middleware/             # RBAC, Scope Check, Activity Logging
│   └── Resources/              # JSON/API Resources jika diperlukan
├── Services/                   # Pure Business Logic Layer
│   ├── Core/
│   ├── Approval/               # Multi-level approval engine
│   ├── HRIS/
│   ├── Attendance/             # Selfie validation, Geofencing, Watermarking
│   ├── Operations/
│   ├── Payroll/                # Formula builder, calculation, closing guard
│   ├── Finance/                # Double-entry ledger balancer, journal posting
│   ├── Procurement/            # 3-way matching validation
│   └── Inventory/              # Concurrency-safe stock movement
├── Policies/                   # Laravel Authorization Policies
└── Console/Commands/           # CLI Commands (misal: bootstrap admin)
```

## 3. Komponen Aliran Data
1. **HTTP Request** -> Route -> Middleware (Auth, Session, RBAC, Active Check)
2. **Controller** -> Menerima Form Request yang tervalidasi.
3. **Service Layer** -> Menjalankan business logic, validasi aturan bisnis, otorisasi spesifik.
4. **Database Transaction** -> Membungkus mutasi multi-tabel untuk menjamin konsistensi.
5. **Audit / Activity Log** -> Mencatat perubahan state, pelaku, payload, IP, dan timestamp.
6. **Blade View + Tailwind CSS + Alpine.js** -> Menyajikan UI responsif enterprise kepada pengguna.

## 4. Keamanan & Konfigurasi Timezone
- Timezone terstandarisasi pada `Asia/Jakarta` (WIB).
- File sensitif (selfie absensi, slip gaji, KTP/dokumen karyawan, invoice PO) disimpan di private storage (`storage/app/private/`) dan diakses melalui route controller berotorisasi, bukan symlink public.
