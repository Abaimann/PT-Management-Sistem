# Spesifikasi Modul — PT-MANAGEMENT-SISTEM

## Modul 1: Core ERP & Administrasi
- **Autentikasi:** Berbasis sesi aman HTTP-only cookies, proteksi brute force login throttling, proteksi CSRF pada setiap form mutasi data.
- **RBAC:** 10 Role independen yang dapat digabungkan. Setiap route, controller method, dan action menu divalidasi oleh Gate/Policy di sisi server.
- **Master Organisasi:** Pengelolaan struktur bertingkat: Perusahaan -> Departemen -> Jabatan (Positions) -> Karyawan.
- **Audit Logging:** Listener otomatis untuk Model Events (created, updated, deleted) yang merekam ID aktor, alamat IP, timestamp, old values, dan new values.
- **Bootstrap Command:** Artisan command `php artisan erp:bootstrap-admin` untuk inisialisasi akun Super Admin awal secara interaktif dan aman tanpa password default hardcoded.

## Modul 2: HRIS (Human Resource Information System)
- **Data Karyawan:** NIK unik, biodata lengkap, tanggal lahir, kontak darurat, jabatan, departemen, supervisor langsung, status kepegawaian (Tetap, PKWT/Kontrak, Magang).
- **Kontrak Kerja:** Manajemen arsip masa berlaku kontrak, pengingat jatuh tempo kontrak, penyimpanan berkas PDF di private storage.
- **Shift & Jadwal:** Konfigurasi multi-shift (Normal, Pagi, Siang, Malam, Fleksibel). Pemetaan jadwal per individu atau per departemen.
- **Manajemen Cuti & Izin:** Saldo cuti tahunan, pengajuan izin sakit (dengan lampiran surat dokter), validasi sisa saldo sebelum submit, integrasi ke approval engine.

## Modul 3: Smart Attendance
- **Absensi Selfie:** Pengambilan foto wajah langsung via kamera perangkat (WebRTC kamera browser) saat check-in dan check-out.
- **Geolokasi GPS:** Pengambilan koordinat lintang & bujur perangkat beserta nilai akurasi (meter). Validasi jarak terhadap geofence kantor pusat / cabang.
- **Watermarking Otomatis:** Sistem membubuhkan tanggal, jam server WIB, koordinat GPS, dan nama karyawan pada foto sebagai metadata pendukung.
- **Koreksi Kehadiran:** Karyawan dapat mengajukan koreksi jam (misal lupa absen atau kendala jaringan) disertai alasan valid untuk direview HR/Supervisor.
- **Locking Cutoff:** Saat periode payroll ditutup atau dikunci HR, seluruh log absensi periode tersebut otomatis terkunci (`is_locked = true`) dan kebal terhadap perubahan langsung.

## Modul 4: Work Management & Operational Timeline
- **Sasaran Kerja (Targets):** Target bulanan tingkat departemen yang didekomposisi ke target anggota tim.
- **Tugas Harian (Daily Tasks):** Dibuat oleh supervisor atau karyawan sendiri. Memiliki deadline, tingkat prioritas, status pengerjaan, dan riwayat revisi.
- **Bukti Pengerjaan (Evidence):** Karyawan wajib mengunggah file bukti atau tautan hasil kerja sebelum mengajukan status `WAITING_VERIFICATION`.
- **Review Supervisor:** Supervisor dapat menyetujui (`APPROVED`), menolak (`REJECTED`), atau meminta revisi (`REVISION`) dengan catatan wajib.

## Modul 5: Internship Management
- **Data Anak Magang:** Pendataan asal institusi/kampus, durasi program magang, penugasan mentor senior.
- **Logbook Harian:** Pengisian aktivitas harian oleh anak magang dan verifikasi harian oleh mentor.
- **Evaluasi Akhir:** Rekapitulasi penilaian performa dan kehadiran untuk penerbitan sertifikat/surat selesai magang.
- **Perhitungan Uang Saku:** Rekap kehadiran valid magang dikalikan tarif uang saku harian perusahaan.

## Modul 6: Payroll Management
- **Master Komponen Gaji:** Gaji pokok, tunjangan jabatan, tunjangan kehadiran, lembur (SPL disetujui), potongan pinjaman/kasbon, PPh21, BPJS.
- **Payroll Draft & Kalkulasi:** Perhitungan otomatis didasarkan pada kehadiran riil yang telah terkunci.
- **Payroll Closing Guard:** Fitur closing hanya dapat dieksekusi setelah formula diverifikasi dan approval direksi terpenuhi. Tidak ada eksekusi transfer dana otomatis.
- **Slip Gaji Digital:** Generator slip gaji yang dilindungi otorisasi (karyawan hanya dapat mengunduh slip miliknya sendiri).

## Modul 7: Finance & Accounting
- **Chart of Accounts (CoA):** Struktur akun 5 digit standar Indonesia (1. Aset, 2. Kewajiban, 3. Ekuitas, 4. Pendapatan, 5. Beban).
- **Double-Entry Journaling:** Setiap transaksi menghasilkan ayat jurnal debit dan kredit yang harus seimbang sebelum berstatus `POSTED`.
- **Reimbursement & Kasbon:** Alur pengajuan klaim biaya operasional, verifikasi bukti nota, approval finance, dan auto-journaling saat dicairkan.
- **Laporan Finansial:** General Ledger, Neraca Saldo, Laba Rugi Sederhana, dan Arus Kas Operasional.

## Modul 8: Procurement & Purchasing
- **Master Vendor:** Database rekanan vendor, NPWP, syarat pembayaran, kontak.
- **Purchase Request (PR):** Pengajuan pembelian dari user unit kerja beserta kode anggaran.
- **Purchase Order (PO):** Dokumen PO resmi diterbitkan kepada vendor setelah PR disetujui.
- **Goods Receipt & Three-Way Matching:** Pencatatan penerimaan fisik barang sebelum approval tagihan pembayaran (PO vs Penerimaan vs Tagihan).

## Modul 9: Inventory & Asset Management
- **Gudang & Stok Barang:** Pencatatan stok multi-lokasi, kartu stok mutasi masuk/keluar, proteksi stok minus.
- **Asset Register:** Pencatatan aset tetap perusahaan, kode inventaris, harga perolehan, penanggung jawab, lokasi fisik.
- **BAST Penugasan Aset:** Berita Acara Serah Terima digital penyerahan laptop/alat kerja kepada karyawan dan clearance pengembalian saat offboarding.

## Modul 10: General Affairs (GA)
- **Kebutuhan Kantor:** Pengajuan ATK dan fasilitas kerja.
- **Kendaraan Operasional:** Jadwal peminjaman mobil/motor perusahaan dan log pemeliharaan/BBM.

## Modul 11: Executive Dashboard
- **Real-Time KPI:** Total karyawan aktif, tingkat kehadiran hari ini, total tugas pending approval, estimasi nilai payroll bulan berjalan, ringkasan pengeluaran vs anggaran.
- **Chart Visual:** Grafik tren kehadiran mingguan dan utilisasi anggaran departemen via Chart.js.
