# Panduan Deployment & Instalasi — PT-MANAGEMENT-SISTEM

## Kebutuhan Sistem
- Windows Server / Windows 10/11 atau Linux (Ubuntu 22.04 LTS / Debian 12)
- PHP >= 8.2 (Disarankan PHP 8.3) dengan ekstensi: `pdo_pgsql`, `pgsql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd`
- PostgreSQL >= 15 (Disarankan PostgreSQL 18)
- Composer >= 2.6
- Node.js >= 18 & NPM >= 9

## Langkah Instalasi di Lingkungan Lokal / Windows
1. **Clone / Buka Direktori Proyek:**
   ```bash
   cd "c:\SAVING\GAWEAN GW\Management-Sistem"
   ```
2. **Konfigurasi Environment `.env`:**
   Salin file `.env.example` ke `.env`:
   ```bash
   cp .env.example .env
   ```
   Pastikan parameter PostgreSQL sesuai dengan instance lokal Anda:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=pt_management_sistem
   DB_USERNAME=postgres
   DB_PASSWORD=password
   ```
3. **Instal Dependensi Backend:**
   ```bash
   composer install --no-interaction
   ```
4. **Generate Application Key:**
   ```bash
   php artisan key:generate
   ```
5. **Jalankan Database Migration & Seeders:**
   ```bash
   php artisan migrate --force
   php artisan db:seed --force
   ```
6. **Inisialisasi Akun Super Admin Pertama:**
   Jalankan artisan command interaktif berikut untuk membuat akun Super Admin aman:
   ```bash
   php artisan erp:bootstrap-admin
   ```
7. **Instal & Build Aset Frontend:**
   ```bash
   npm install
   npm run build
   ```
8. **Jalankan Server Lokal:**
   ```bash
   php artisan serve --port=8000
   ```
   Aplikasi dapat diakses melalui browser pada `http://localhost:8000`.
