# Kebijakan Keamanan — PT-MANAGEMENT-SISTEM

## 1. Perlindungan Akses & Data Sensitif
1. **Private File Storage:**
   - Foto selfie absensi karyawan, bukti kuitansi reimbursement, slip gaji, dokumen kontrak, dan bukti pengerjaan tugas disimpan di direktori privat (`storage/app/private/`).
   - Tidak pernah di-symlink ke direktori `public/` dan tidak memiliki URL akses langsung.
   - Akses file dilayani oleh stream controller terautentikasi dengan Policy: `Gate::authorize('view-attachment', $file)`.

2. **Otorisasi Server-Side:**
   - Navigasi frontend yang disembunyikan hanya merupakan lapisan UX, bukan pembatas keamanan.
   - Setiap endpoint controller, mutasi data, dan query database wajib memvalidasi otorisasi user aktif melalui Policy/Gate.
   - Pencegahan Insecure Direct Object References (IDOR): Karyawan dilarang mengakses ID slip gaji, tugas, atau data cuti orang lain.

3. **Integritas Kata Sandi & Sesi:**
   - Hashing menggunakan algoritma Bcrypt (cost 12).
   - Session cookies menggunakan flag `HttpOnly`, `SameSite=Lax`, dan `Secure` (saat HTTPS aktif).
   - Rate limiting diterapkan pada rute login untuk mencegah serangan brute force.

4. **Sanitisasi & Proteksi Injeksi:**
   - Seluruh query database memanfaatkan parameterized query melalui Eloquent ORM / PDO driver.
   - Seluruh output Blade menggunakan escaping bawaan `{{ $data }}` untuk mencegah serangan Cross-Site Scripting (XSS).
   - Seluruh form mutasi menyertakan token `@csrf`.

5. **Audit Trail Append-Only:**
   - Setiap aksi penting (perubahan role, edit data gaji, penghapusan data master, posting jurnal, approval dokumen) disimpan dalam tabel `audit_logs` dengan format snapshot JSONB (old & new values) yang tidak dapat dimanipulasi melalui antarmuka umum.
