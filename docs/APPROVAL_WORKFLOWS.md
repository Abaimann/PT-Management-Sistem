# Reusable Multi-Level Approval Engine — PT-MANAGEMENT-SISTEM

## 1. Prinsip Approval Engine
Untuk menghindari logika persetujuan yang terfragmentasi, sistem menggunakan **Unified Approval Engine**. Seluruh modul bisnis yang membutuhkan otorisasi (Cuti, Koreksi Absensi, Daily Tasks, Payroll, Purchase Request, Purchase Order, Reimbursement, Expense, Permintaan Aset) mengalir melalui entitas `approval_requests` dan `approval_steps`.

## 2. Status Transisi Dokumen
```
[ DRAFT ] 
    │
    ▼
[ SUBMITTED ] ──(Dibatalkan Requester)──► [ CANCELLED ]
    │
    ▼
[ IN_REVIEW ] (Tahap Persetujuan Berjenjang: Level 1 -> Level 2 -> Level N)
    ├──► [ REJECTED ] (Ditolak final)
    ├──► [ RETURNED ] (Dikembalikan ke pemohon untuk revisi)
    └──► [ APPROVED ] (Seluruh level menyetujui -> trigger business action)
```

## 3. Aturan Validasi Server-Side
- **Segregation of Duties (SoD):** Pemohon (`requester_id`) tidak boleh menyetujui permintaannya sendiri (`approver_id != requester_id`).
- **Urutan Berjenjang (`step_order`):** Persetujuan Level 2 tidak dapat dilakukan sebelum Level 1 berstatus `APPROVED`.
- **Audit Jejak Keputusan:** Setiap klik persetujuan/penolakan/pengembalian wajib mencatat:
  - `approver_id`
  - `decision` (`APPROVED`, `REJECTED`, `RETURNED`)
  - `notes` (Wajib diisi jika `REJECTED` atau `RETURNED`)
  - `decided_at`
  - `ip_address`
- **Tindakan Pasca-Approval:** Ketika status akhir menjadi `APPROVED`, engine memanggil handler spesifik:
  - *Leave Request:* Memotong saldo cuti (`leave_balances`) dan membuat jadwal libur karyawan.
  - *Attendance Correction:* Memperbarui log kehadiran dan mencatat riwayat koreksi.
  - *Daily Task:* Mengubah task state ke `APPROVED` dan menghitung KPI produktivitas.
  - *PR:* Mengubah PR menjadi status siap diterbitkan menjadi PO oleh Purchasing.
  - *PO:* Mengizinkan penerimaan barang (Goods Receipt).
  - *Expense / Reimbursement:* Menghasilkan draft jurnal otomatis di General Ledger (Finance).
  - *Payroll:* Mengaktifkan izin pencetakan slip gaji dan penguncian periode.
