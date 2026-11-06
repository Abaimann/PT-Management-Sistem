# Panduan Pengujian & Quality Control — PT-MANAGEMENT-SISTEM

## 1. Strategi Pengujian
Pengujian dilakukan bertingkat mencakup:
- **Linting & Syntax Analysis:** Menggunakan PHP CLI check dan Pint.
- **Migration & Schema Integrity:** Pengujian foreign key, cascade/restrict rule, unique constraints, dan database transactions.
- **Unit Testing:** Menguji service layer terisolasi (misal: validasi keseimbangan debit-kredit jurnal, kalkulasi formula payroll, formula geofencing jarak meter).
- **Feature & Integration Testing:** Pengujian HTTP request, otorisasi role, validasi form, pengujian IDOR, dan alur approval lintas level.

## 2. Test Cases Kritis Wajib
1. **Otorisasi Data Karyawan:** User role `employee` dilarang melihat slip gaji atau data sensitif karyawan lain (IDOR test).
2. **Supervisor Scope:** Supervisor hanya boleh melihat, menugaskan, dan menyetujui tugas anggota tim di bawah supervisinya.
3. **Absensi Check-out:** Absensi keluar dilarang sebelum absensi masuk tercatat pada hari yang sama.
4. **Approval Task:** Transisi ke status `APPROVED` dilarang bila tidak ada bukti kerja (`task_attachments`) dan bukan dilakukan oleh supervisor berwenang.
5. **Double-Entry Balance:** Jurnal umum berstatus `POSTED` wajib melempar pengecualian / ditolak jika `SUM(debit) != SUM(credit)`.
6. **Stock Integrity:** Pengurangan kuantitas stok barang tidak boleh mengakibatkan saldo stok menjadi negatif pada gudang terkait.
7. **Anti-Duplikasi Approval:** Percobaan klik ganda (double-submission) pada persetujuan dokumen tidak boleh menimbulkan duplikasi mutasi keuangan atau log.
8. **Payroll Closing Guard:** Penutupan periode payroll dilarang jika belum berstatus kalkulasi valid dan disetujui direksi.
