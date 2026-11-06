@extends('layouts.app')

@section('title', 'Dashboard Enterprise')
@section('page_title', 'Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Top Greeting & Role Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 border border-slate-800 p-6 sm:p-8 text-white shadow-xl">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 border border-indigo-400/30 text-indigo-300 text-xs font-semibold mb-3">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    Portal Enterprise PT-Management-Sistem
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                    Selamat Datang, {{ $user->name }}!
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-2xl">
                    Anda masuk dengan peran <strong class="text-indigo-400 uppercase font-mono">{{ $user->roles->first()?->name ?? $primaryRole }}</strong>. 
                    Semua operasional master data, SDM, absensi, dan alur kerja siap dipantau secara real-time.
                </p>
            </div>

            <!-- Quick Stats in Banner -->
            <div class="flex items-center gap-3 shrink-0">
                <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl px-4 py-3 text-center">
                    <div class="text-2xl font-black text-emerald-400">{{ $attendanceRate }}%</div>
                    <div class="text-[11px] text-slate-300">Tingkat Kehadiran</div>
                </div>
                <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl px-4 py-3 text-center">
                    <div class="text-2xl font-black text-indigo-300">{{ $totalCheckedIn }}/{{ $activeEmployees }}</div>
                    <div class="text-[11px] text-slate-300">Hadir Hari Ini</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action & Module Shortcuts Hub (Direct Navigation to all 10 ERP Modules) -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-sm space-y-5">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-indigo-600 text-white shadow-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    </span>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 tracking-tight">Pusat Navigasi & Pintasan Modul ERP</h2>
                        <p class="text-xs text-slate-500">Pilih modul di bawah untuk langsung menuju halaman operasional dan data terkait.</p>
                    </div>
                </div>
            </div>

            <!-- Quick Document & Spec Jump Links -->
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ url('/update-rancangan.html') }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition border border-indigo-200/70">
                    <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Blueprint V2</span>
                </a>
                <a href="{{ url('/docs/ARCHITECTURE.md') }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200 transition border border-slate-200">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>Arsitektur</span>
                </a>
                <a href="{{ url('/docs/ROLE_PERMISSION_MATRIX.md') }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200 transition border border-slate-200">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Matrix Role</span>
                </a>
            </div>
        </div>

        <!-- 10 Module Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3.5">
            <!-- 1. Data Karyawan (HRIS) -->
            <div class="group relative flex flex-col justify-between p-4 rounded-2xl border border-slate-200/90 bg-white hover:border-indigo-400 hover:shadow-md transition-all duration-200">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700">HRIS</span>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 group-hover:text-indigo-600 transition">Data Karyawan</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">Profil karyawan, jabatan, departemen & berkas SDM.</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-2">
                    <a href="{{ route('employees.index') }}" class="flex-1 text-center py-1.5 px-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs transition">
                        Buka Data
                    </a>
                    <a href="{{ route('employees.create') }}" title="Tambah Karyawan" class="p-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </a>
                </div>
            </div>

            <!-- 2. Smart Attendance -->
            <div class="group relative flex flex-col justify-between p-4 rounded-2xl border border-slate-200/90 bg-white hover:border-emerald-400 hover:shadow-md transition-all duration-200">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700">Presensi</span>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 group-hover:text-emerald-600 transition">Smart Attendance</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">Absensi QR, geofencing GPS, koreksi & monitoring hadir.</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-2">
                    <a href="{{ route('attendance.index') }}" class="flex-1 text-center py-1.5 px-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition">
                        Data Presensi
                    </a>
                    <a href="{{ route('attendance.create') }}" title="Scan Absensi" class="p-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3" stroke-width="2"/></svg>
                    </a>
                </div>
            </div>

            <!-- 3. Manajemen Tugas -->
            <div class="group relative flex flex-col justify-between p-4 rounded-2xl border border-slate-200/90 bg-white hover:border-sky-400 hover:shadow-md transition-all duration-200">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-9 h-9 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center group-hover:bg-sky-600 group-hover:text-white transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-sky-50 text-sky-700">Operasi</span>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 group-hover:text-sky-600 transition">Manajemen Tugas</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">Pendelegasian tugas tim, progres kerja & review bukti.</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-2">
                    <a href="{{ route('tasks.index') }}" class="flex-1 text-center py-1.5 px-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold shadow-xs transition">
                        Daftar Tugas
                    </a>
                    <a href="{{ route('tasks.create') }}" title="Buat Tugas Baru" class="p-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </a>
                </div>
            </div>

            <!-- 4. Program Magang -->
            <div class="group relative flex flex-col justify-between p-4 rounded-2xl border border-slate-200/90 bg-white hover:border-amber-400 hover:shadow-md transition-all duration-200">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:bg-amber-600 group-hover:text-white transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-amber-50 text-amber-700">Magang</span>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 group-hover:text-amber-600 transition">Program Magang</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">Registrasi peserta magang, logbook harian & evaluasi mentor.</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-2">
                    <a href="{{ route('internship.index') }}" class="flex-1 text-center py-1.5 px-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold shadow-xs transition">
                        Portal Magang
                    </a>
                    <a href="{{ route('internship.logbooks') }}" title="Logbook Magang" class="p-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </a>
                </div>
            </div>

            <!-- 5. Penggajian / Payroll -->
            <div class="group relative flex flex-col justify-between p-4 rounded-2xl border border-slate-200/90 bg-white hover:border-rose-400 hover:shadow-md transition-all duration-200">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center group-hover:bg-rose-600 group-hover:text-white transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-rose-50 text-rose-700">Payroll</span>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 group-hover:text-rose-600 transition">Penggajian / Payroll</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">Periode penggajian, kalkulasi PPh 21, BPJS & slip gaji.</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-2">
                    <a href="{{ route('payroll.periods.index') }}" class="flex-1 text-center py-1.5 px-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-xs transition">
                        Kelola Payroll
                    </a>
                    <a href="{{ route('payroll.payslips.my') }}" title="Slip Gaji Saya" class="p-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </a>
                </div>
            </div>

            <!-- 6. Keuangan & Jurnal -->
            <div class="group relative flex flex-col justify-between p-4 rounded-2xl border border-slate-200/90 bg-white hover:border-violet-400 hover:shadow-md transition-all duration-200">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-9 h-9 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center group-hover:bg-violet-600 group-hover:text-white transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-violet-50 text-violet-700">Finance</span>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 group-hover:text-violet-600 transition">Keuangan & Jurnal</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">Chart of Accounts, entri jurnal umum, buku besar & neraca.</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-2">
                    <a href="{{ route('finance.journals.index') }}" class="flex-1 text-center py-1.5 px-2.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-semibold shadow-xs transition">
                        Buku Jurnal
                    </a>
                    <a href="{{ route('finance.journals.create') }}" title="Buat Jurnal Baru" class="p-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </a>
                </div>
            </div>

            <!-- 7. Pengadaan / PO -->
            <div class="group relative flex flex-col justify-between p-4 rounded-2xl border border-slate-200/90 bg-white hover:border-cyan-400 hover:shadow-md transition-all duration-200">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-9 h-9 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center group-hover:bg-cyan-600 group-hover:text-white transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-cyan-50 text-cyan-700">Procure</span>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 group-hover:text-cyan-600 transition">Pengadaan / PO</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">Purchase Request, Purchase Order, vendor & penerimaan.</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-2">
                    <a href="{{ route('procurement.orders.index') }}" class="flex-1 text-center py-1.5 px-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-semibold shadow-xs transition">
                        Pesanan PO
                    </a>
                    <a href="{{ route('procurement.orders.create') }}" title="Buat PO Baru" class="p-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </a>
                </div>
            </div>

            <!-- 8. Inventaris & Gudang -->
            <div class="group relative flex flex-col justify-between p-4 rounded-2xl border border-slate-200/90 bg-white hover:border-yellow-400 hover:shadow-md transition-all duration-200">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-9 h-9 rounded-xl bg-yellow-50 text-yellow-600 flex items-center justify-center group-hover:bg-yellow-600 group-hover:text-white transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-yellow-50 text-yellow-700">Logistik</span>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 group-hover:text-yellow-600 transition">Inventaris & Gudang</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">Master item barang, stok opname, mutasi & aset kantor.</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-2">
                    <a href="{{ route('inventory.items.index') }}" class="flex-1 text-center py-1.5 px-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold shadow-xs transition">
                        Katalog Stok
                    </a>
                    <a href="{{ route('inventory.assets.index') }}" title="Daftar Aset Fisik" class="p-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                    </a>
                </div>
            </div>

            <!-- 9. Layanan Umum / GA -->
            <div class="group relative flex flex-col justify-between p-4 rounded-2xl border border-slate-200/90 bg-white hover:border-emerald-400 hover:shadow-md transition-all duration-200">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700">GA</span>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 group-hover:text-emerald-600 transition">Layanan Umum / GA</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">Permintaan ATK, perbaikan sarpras & operasional kantor.</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-2">
                    <a href="{{ route('ga.requests.index') }}" class="flex-1 text-center py-1.5 px-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition">
                        Tiket GA
                    </a>
                    <a href="{{ route('departments.index') }}" title="Struktur Unit" class="p-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </a>
                </div>
            </div>

            <!-- 10. Executive Dashboard -->
            <div class="group relative flex flex-col justify-between p-4 rounded-2xl border border-slate-200/90 bg-white hover:border-purple-400 hover:shadow-md transition-all duration-200">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center group-hover:bg-purple-600 group-hover:text-white transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-purple-50 text-purple-700">Executive</span>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 group-hover:text-purple-600 transition">Executive Dashboard</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">BI Analytics, ringkasan pengeluaran & metrik strategis.</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-2">
                    <a href="{{ route('executive.dashboard') }}" class="flex-1 text-center py-1.5 px-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold shadow-xs transition">
                        Buka BI & Metrik
                    </a>
                    <a href="{{ url('/executive/dashboard') }}" title="Pintasan URL Alternatif" class="p-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Secondary Quick Actions Bar -->
        <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-100 text-xs">
            <div class="flex items-center gap-2 text-slate-500 font-medium">
                <span class="font-bold text-slate-700 uppercase tracking-wider">Aksi Cepat Master:</span>
                <a href="{{ route('employees.create') }}" class="hover:text-indigo-600 underline underline-offset-2 transition">+ Karyawan Baru</a>
                <span class="text-slate-300">•</span>
                <a href="{{ route('departments.create') }}" class="hover:text-indigo-600 underline underline-offset-2 transition">+ Departemen</a>
                <span class="text-slate-300">•</span>
                <a href="{{ route('positions.create') }}" class="hover:text-indigo-600 underline underline-offset-2 transition">+ Jabatan</a>
                <span class="text-slate-300">•</span>
                <a href="{{ route('attendance.create') }}" class="hover:text-emerald-600 underline underline-offset-2 transition">Absen Sekarang</a>
            </div>
            <div class="text-slate-400 text-[11px]">
                Sistem Terverifikasi & Sinkronisasi Real-Time
            </div>
        </div>
    </div>

    <!-- 4 Main KPI Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <!-- KPI 1: Total Karyawan -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Karyawan</span>
                <div class="w-10 h-10 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900">{{ $totalEmployees }}</span>
                <span class="text-xs font-semibold text-emerald-600">({{ $activeEmployees }} Aktif)</span>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs text-slate-500 pt-3 border-t border-slate-100">
                <span>{{ $totalDepartments }} Departemen</span>
                <span>{{ $totalPositions }} Jabatan</span>
            </div>
        </div>

        <!-- KPI 2: Kehadiran Hari Ini -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Kehadiran Hari Ini</span>
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900">{{ $totalCheckedIn }}</span>
                <span class="text-xs font-semibold text-emerald-600">{{ $attendanceRate }}% Hadir</span>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs text-slate-500 pt-3 border-t border-slate-100">
                <span class="text-emerald-600 font-medium">{{ $presentTodayCount }} Tepat Waktu</span>
                <span class="text-amber-600 font-medium">{{ $lateTodayCount }} Terlambat</span>
            </div>
        </div>

        <!-- KPI 3: Status Tugas Operasional -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Tugas & Operasional</span>
                <div class="w-10 h-10 rounded-2xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900">{{ $tasksTodoCount + $tasksInProgressCount }}</span>
                <span class="text-xs font-semibold text-amber-600">Berjalan / Pending</span>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs text-slate-500 pt-3 border-t border-slate-100">
                <span>{{ $tasksVerificationCount }} Verifikasi</span>
                <span class="text-emerald-600 font-medium">{{ $tasksCompletedCount }} Selesai</span>
            </div>
        </div>

        <!-- KPI 4: Payroll & Keuangan -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Draft Penggajian</span>
                <div class="w-10 h-10 rounded-2xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900">{{ $draftPayrollRunsCount }}</span>
                <span class="text-xs font-semibold text-blue-600">Batch Menunggu</span>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs text-slate-500 pt-3 border-t border-slate-100">
                <span>Total Net Terakhir:</span>
                <span class="font-bold text-slate-700">Rp {{ number_format($latestPayrollRun?->total_net_pay ?? 0, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Charts Row: Department Distribution & Attendance Trends -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Chart 1: 7-Day Attendance Trend -->
        <div class="lg:col-span-2 bg-white rounded-3xl p-6 border border-slate-200 shadow-xs flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Tren Kehadiran (7 Hari Terakhir)</h3>
                    <p class="text-xs text-slate-500">Perbandingan absensi tepat waktu vs keterlambatan harian</p>
                </div>
                <div class="flex items-center gap-3 text-xs">
                    <span class="inline-flex items-center gap-1.5 text-indigo-600 font-semibold">
                        <span class="w-3 h-3 rounded-full bg-indigo-600"></span> Tepat Waktu
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-amber-500 font-semibold">
                        <span class="w-3 h-3 rounded-full bg-amber-500"></span> Terlambat
                    </span>
                </div>
            </div>
            <div class="h-64 relative flex-1">
                <canvas id="attendanceTrendChart"></canvas>
            </div>
        </div>

        <!-- Chart 2: Department Distribution -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs flex flex-col">
            <div class="mb-4">
                <h3 class="text-sm font-bold text-slate-900">Distribusi Karyawan</h3>
                <p class="text-xs text-slate-500">Jumlah SDM aktif per departemen</p>
            </div>
            <div class="h-64 relative flex-1 flex items-center justify-center">
                <canvas id="departmentChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Personal Context / Employee Self-Service Card (If linked to employee) -->
    @if($currentEmployee)
    <div class="bg-gradient-to-r from-indigo-50 to-blue-50 border border-indigo-100 rounded-3xl p-6 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-indigo-100">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white font-extrabold flex items-center justify-center text-lg shadow-md shadow-indigo-600/20">
                    {{ strtoupper(substr($currentEmployee->full_name, 0, 1)) }}
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">{{ $currentEmployee->full_name }} ({{ $currentEmployee->nik }})</h3>
                    <p class="text-xs text-indigo-700 font-medium">
                        {{ $currentEmployee->department?->name }} &bull; {{ $currentEmployee->position?->name }}
                    </p>
                </div>
            </div>
            <a href="{{ route('employees.show', $currentEmployee->id) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-600 hover:text-indigo-800">
                Lihat Dossier Lengkap
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            <!-- Absensi Hari Ini -->
            <div class="bg-white/80 backdrop-blur-xs p-4 rounded-2xl border border-indigo-100/80">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Status Absensi Anda Hari Ini</div>
                @if($personalAttendanceToday)
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full {{ $personalAttendanceToday->status === 'LATE' ? 'bg-amber-500' : 'bg-emerald-500' }}"></span>
                        <span class="font-bold text-xs text-slate-800">
                            Masuk: {{ $personalAttendanceToday->clock_in ?? '-' }}
                            @if($personalAttendanceToday->clock_out)
                                &bull; Pulang: {{ $personalAttendanceToday->clock_out }}
                            @endif
                        </span>
                    </div>
                    <span class="inline-block mt-1 text-[10px] px-2 py-0.5 rounded font-semibold {{ $personalAttendanceToday->status === 'LATE' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                        {{ $personalAttendanceToday->status }}
                    </span>
                @else
                    <div class="flex items-center gap-2 text-slate-500 text-xs">
                        <span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span>
                        <span>Belum melakukan clock-in hari ini</span>
                    </div>
                @endif
            </div>

            <!-- Tugas Belum Selesai -->
            <div class="bg-white/80 backdrop-blur-xs p-4 rounded-2xl border border-indigo-100/80">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Tugas Anda yang Berjalan</div>
                <div class="text-lg font-bold text-slate-900">{{ $personalPendingTasks->count() }} Tugas</div>
                <div class="text-[11px] text-slate-500 mt-0.5">Segera verifikasi sebelum tenggat waktu</div>
            </div>

            <!-- Kuota Cuti -->
            <div class="bg-white/80 backdrop-blur-xs p-4 rounded-2xl border border-indigo-100/80">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Saldo Kuota Cuti</div>
                @php
                    $totalRemainingQuota = $personalLeaveBalances->sum('remaining_quota');
                @endphp
                <div class="text-lg font-bold text-slate-900">{{ $totalRemainingQuota }} Hari</div>
                <div class="text-[11px] text-slate-500 mt-0.5">Tahun berjalan {{ date('Y') }}</div>
            </div>
        </div>
    </div>
    @endif

    <!-- 2 Column Section: Recent Activities & Recent Employees -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Audit Trail / Aktivitas Terkini -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Jejak Audit & Aktivitas Terkini</h3>
                    <p class="text-xs text-slate-500">Log perubahan master data dan transaksi sistem</p>
                </div>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($recentAuditLogs as $log)
                    <div class="py-3 flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-600 font-bold flex items-center justify-center text-xs shrink-0 mt-0.5">
                                {{ strtoupper(substr($log->event, 0, 2)) }}
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-slate-800">
                                    {{ ucwords(str_replace('_', ' ', $log->event)) }}
                                </div>
                                <div class="text-[11px] text-slate-500">
                                    Oleh: <span class="font-medium text-slate-700">{{ $log->actor?->name ?? 'System' }}</span>
                                </div>
                            </div>
                        </div>
                        <span class="text-[10px] font-mono text-slate-400 shrink-0">
                            {{ $log->created_at?->diffForHumans() }}
                        </span>
                    </div>
                @empty
                    <div class="py-6 text-center text-xs text-slate-400">
                        Belum ada riwayat aktivitas terbaru yang tercatat.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Registered Employees -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Karyawan Baru Terdaftar</h3>
                    <p class="text-xs text-slate-500">Anggota terbaru yang bergabung dalam organisasi</p>
                </div>
                <a href="{{ route('employees.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                    Lihat Semua &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($recentEmployees as $emp)
                    <div class="py-3 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-indigo-50 border border-indigo-200 text-indigo-600 font-bold flex items-center justify-center text-xs shrink-0">
                                {{ strtoupper(substr($emp->full_name, 0, 1)) }}
                            </div>
                            <div>
                                <a href="{{ route('employees.show', $emp->id) }}" class="text-xs font-semibold text-slate-900 hover:text-indigo-600 block">
                                    {{ $emp->full_name }}
                                </a>
                                <span class="text-[11px] text-slate-500">
                                    {{ $emp->department?->name }} &bull; {{ $emp->position?->name }}
                                </span>
                            </div>
                        </div>
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold {{ $emp->employment_status === 'PERMANENT' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                            {{ $emp->employment_status }}
                        </span>
                    </div>
                @empty
                    <div class="py-6 text-center text-xs text-slate-400">
                        Belum ada karyawan baru terdaftar.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Department Distribution Chart (Doughnut)
        const deptCtx = document.getElementById('departmentChart');
        if (deptCtx) {
            new Chart(deptCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($deptChartLabels) !!},
                    datasets: [{
                        data: {!! json_encode($deptChartData) !!},
                        backgroundColor: [
                            '#4f46e5', '#3b82f6', '#06b6d4', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6', '#64748b'
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 10,
                                font: { size: 10, family: 'Plus Jakarta Sans' }
                            }
                        }
                    },
                    cutout: '68%'
                }
            });
        }

        // 2. Attendance Trend Chart (Line)
        const attCtx = document.getElementById('attendanceTrendChart');
        if (attCtx) {
            new Chart(attCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($attendanceTrendLabels) !!},
                    datasets: [
                        {
                            label: 'Tepat Waktu',
                            data: {!! json_encode($attendanceTrendPresent) !!},
                            borderColor: '#4f46e5',
                            backgroundColor: 'rgba(79, 70, 229, 0.1)',
                            fill: true,
                            tension: 0.35,
                            pointRadius: 4,
                            pointBackgroundColor: '#4f46e5'
                        },
                        {
                            label: 'Terlambat',
                            data: {!! json_encode($attendanceTrendLate) !!},
                            borderColor: '#f59e0b',
                            backgroundColor: 'rgba(245, 158, 11, 0.1)',
                            fill: true,
                            tension: 0.35,
                            pointRadius: 4,
                            pointBackgroundColor: '#f59e0b'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, font: { size: 10 } },
                            grid: { color: '#f1f5f9' }
                        },
                        x: {
                            ticks: { font: { size: 10 } },
                            grid: { display: false }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
