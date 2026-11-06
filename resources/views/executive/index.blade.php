@extends('layouts.app')

@section('title', 'Executive KPI Dashboard')
@section('page_title', 'Executive Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Executive Greeting & Company Header -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-950 via-indigo-950 to-slate-900 border border-slate-800 p-6 sm:p-8 text-white shadow-2xl">
        <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 border border-amber-400/30 text-amber-300 text-xs font-semibold mb-3">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                    Executive Command Center &bull; Direksi &amp; Manajemen
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                    {{ $company->legal_name ?? 'PT MANAGEMENT SISTEM INDONESIA' }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-2xl">
                    Executive Dashboard mengagregasi performa real-time dari 10 modul bisnis terintegrasi: HRIS, Smart Attendance, Operasional, Magang, Payroll, Finansial, Reimbursement, Pengadaan, Gudang, dan Aset Tetap.
                </p>
            </div>

            <!-- Top Header Metrics -->
            <div class="flex items-center gap-3 shrink-0">
                <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl px-4 py-3 text-center">
                    <div class="text-2xl font-black text-emerald-400 font-mono">{{ $attendanceRatio }}%</div>
                    <div class="text-[11px] text-slate-300">Presensi Hari Ini ({{ $presentToday }}/{{ $totalEmployees }})</div>
                </div>
                <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl px-4 py-3 text-center">
                    <div class="text-2xl font-black text-amber-400 font-mono">{{ $totalPendingApprovals }}</div>
                    <div class="text-[11px] text-slate-300">Pending Approval Lintas Modul</div>
                </div>
            </div>
        </div>
    </div>

    <!-- OPERATIONAL RISK RADAR (PERINGATAN RISIKO OPERASIONAL) -->
    @if($highLateCount > 0 || $overdueTasksCount > 0 || $lowStockCount > 0 || $agingPRCount > 0)
    <div class="p-5 rounded-2xl bg-amber-500/10 border border-amber-500/30">
        <div class="flex items-center gap-2 mb-3">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <h2 class="text-xs font-black text-amber-900 uppercase tracking-wider">Radar Peringatan Risiko Operasional (Real-Time Attention)</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="p-3.5 rounded-xl bg-white border border-amber-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500">Tingkat Keterlambatan Tinggi</div>
                    <div class="text-lg font-black text-amber-600 mt-0.5">{{ $highLateCount }} Kejadian</div>
                    <div class="text-[10px] text-slate-400">Terlambat > 15 menit (30 hari)</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>

            <div class="p-3.5 rounded-xl bg-white border border-rose-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500">Tugas / Target Overdue</div>
                    <div class="text-lg font-black text-rose-600 mt-0.5">{{ $overdueTasksCount }} Tugas</div>
                    <div class="text-[10px] text-slate-400">Melewati batas tenggat waktu</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>

            <div class="p-3.5 rounded-xl bg-white border border-amber-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500">Stok Di Bawah Safety Limit</div>
                    <div class="text-lg font-black text-amber-600 mt-0.5">{{ $lowStockCount }} Item SKU</div>
                    <div class="text-[10px] text-slate-400">Perlu re-order pengadaan</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>

            <div class="p-3.5 rounded-xl bg-white border border-indigo-200/80 shadow-xs flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-500">PR Tertunda (> 2 Hari)</div>
                    <div class="text-lg font-black text-indigo-600 mt-0.5">{{ $agingPRCount }} Permintaan</div>
                    <div class="text-[10px] text-slate-400">Belum disetujui departemen</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- 8 EXECUTIVE CORE KPI METRIC CARDS -->
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <!-- 1. Karyawan & Kehadiran -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">SDM &amp; Kehadiran</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900 mt-2">{{ number_format($totalEmployees) }} <span class="text-xs text-slate-400 font-normal">Karyawan</span></div>
            <div class="text-[11px] text-emerald-600 font-semibold mt-1 flex items-center gap-1">
                <span>{{ $presentToday }} hadir hari ini</span> &bull; <span>{{ $lateToday }} terlambat</span>
            </div>
        </div>

        <!-- 2. Pengeluaran Bulan Berjalan -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Beban Pengeluaran Bulan Ini</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="text-xl font-bold text-slate-900 mt-2 font-mono">
                Rp {{ number_format($totalMonthlyExpense, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-slate-400 mt-1">Beban operasional &amp; penggajian</div>
        </div>

        <!-- 3. Budget vs Realisasi -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Utilisasi Anggaran</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-black text-indigo-700 mt-2 font-mono">{{ $budgetUtilizationPercent }}%</div>
            <div class="text-[11px] text-slate-400 mt-1">
                Sisa: <span class="font-semibold text-slate-700">Rp {{ number_format($budgetRemaining / 1000000, 1) }} Jt</span>
            </div>
        </div>

        <!-- 4. Nilai Aset Tetap -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Nilai Aset Tetap Terdaftar</span>
                <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
            </div>
            <div class="text-xl font-bold text-slate-900 mt-2 font-mono">
                Rp {{ number_format($totalAssetBookValue, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-slate-400 mt-1">{{ $activeAssetCount }} unit aset terdaftar aktif</div>
        </div>

        <!-- 5. Status Pengadaan & PO -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Pengadaan &amp; PO Aktif</span>
                <div class="w-8 h-8 rounded-lg bg-cyan-50 text-cyan-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900 mt-2">{{ $activePOs }} <span class="text-xs text-slate-400 font-normal">Active PO</span></div>
            <div class="text-[11px] text-cyan-700 font-semibold mt-1">
                {{ $pendingReceipts }} menunggu BAST barang
            </div>
        </div>

        <!-- 6. Total Payroll Bulan Berjalan -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Payroll Bulan Berjalan</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="text-xl font-bold text-slate-900 mt-2 font-mono">
                Rp {{ number_format($monthlyPayrollExpense, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-slate-400 mt-1">
                {{ $closedPayrollCount }} periode ditutup / {{ $draftPayrollCount + $calculatedPayrollCount }} aktif
            </div>
        </div>

        <!-- 7. Gudang & SKU Aktif -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Inventaris &amp; SKU</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900 mt-2">{{ $totalItemSKUs }} <span class="text-xs text-slate-400 font-normal">SKU</span></div>
            <div class="text-[11px] text-slate-400 mt-1">Barang inventaris &amp; ATK gudang</div>
        </div>

        <!-- 8. Pending Approvals Total -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs ring-1 ring-amber-500/20">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-amber-700 uppercase tracking-wider">Antrean Approval</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </div>
            </div>
            <div class="text-2xl font-black text-amber-700 mt-2 font-mono">{{ $totalPendingApprovals }} <span class="text-xs text-amber-600 font-normal">Pengajuan</span></div>
            <div class="text-[10px] text-slate-400 mt-1 truncate">
                Cuti: {{ $pendingLeaves }} &bull; Koreksi: {{ $pendingCorrections }} &bull; PO: {{ $pendingPO }} &bull; Reimb: {{ $pendingReimbursements }}
            </div>
        </div>

    </div>

    <!-- CHARTS SECTION (CHART.JS) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Chart 1: Tren Pengeluaran vs Budget 6 Bulan Terakhir -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Tren Beban Pengeluaran vs Anggaran Bulanan</h3>
                    <p class="text-[11px] text-slate-500">Perbandingan realisasi beban operasional dan alokasi budget 6 bulan terakhir.</p>
                </div>
                <div class="flex items-center gap-4 text-[11px]">
                    <span class="flex items-center gap-1.5 font-medium text-slate-600">
                        <span class="w-3 h-3 rounded bg-indigo-600"></span> Pengeluaran Riil
                    </span>
                    <span class="flex items-center gap-1.5 font-medium text-slate-600">
                        <span class="w-3 h-3 rounded bg-slate-300"></span> Budget Plafon
                    </span>
                </div>
            </div>
            <div class="h-64">
                <canvas id="expenseBudgetChart"></canvas>
            </div>
        </div>

        <!-- Chart 2: Distribusi Status Kehadiran Bulan Ini -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 flex flex-col justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Distribusi Presensi Karyawan</h3>
                <p class="text-[11px] text-slate-500">Kepatuhan kehadiran &amp; jam kerja bulan berjalan.</p>
            </div>
            <div class="h-56 my-2 relative">
                <canvas id="attendanceDistChart"></canvas>
            </div>
            <div class="grid grid-cols-3 gap-2 text-center text-[10px] border-t border-slate-100 pt-3 text-slate-500">
                <div>
                    <span class="block font-bold text-slate-800 text-xs">{{ $attendanceDistribution['on_time'] }}</span>
                    <span>Tepat Waktu</span>
                </div>
                <div>
                    <span class="block font-bold text-amber-600 text-xs">{{ $attendanceDistribution['late'] }}</span>
                    <span>Terlambat</span>
                </div>
                <div>
                    <span class="block font-bold text-rose-600 text-xs">{{ $attendanceDistribution['absent'] }}</span>
                    <span>Mangkir</span>
                </div>
            </div>
        </div>

    </div>

    <!-- HIGH-VALUE / PRIORITY APPROVAL QUEUE (ANTEAN APPROVAL TINGKAT TINGGI) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
            <div>
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    Antrean Approval Prioritas Tinggi (High-Value Approvals)
                </h3>
                <p class="text-[11px] text-slate-500">Daftar permohonan bernilai tinggi yang membutuhkan persetujuan Direksi &amp; Manajemen.</p>
            </div>
            <span class="text-xs font-semibold text-indigo-600 bg-indigo-50 px-3 py-1 rounded-xl">
                {{ $highValueApprovals->count() }} Permohonan Teratas
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/75 border-b border-slate-200 text-slate-700 font-semibold uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Modul &amp; Dokumen</th>
                        <th class="px-4 py-3">Departemen</th>
                        <th class="px-4 py-3">Pemohon / Vendor</th>
                        <th class="px-4 py-3">Nominal (IDR)</th>
                        <th class="px-4 py-3">Tgl Pengajuan</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($highValueApprovals as $item)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-4 py-3.5">
                            <div class="font-bold text-slate-900">{{ $item['title'] }}</div>
                            <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600">
                                {{ $item['module'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap text-slate-800">
                            {{ $item['department'] }}
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap font-medium text-slate-700">
                            {{ $item['requester'] }}
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap font-bold text-slate-900 font-mono">
                            @if($item['amount'] > 0)
                            Rp {{ number_format($item['amount'], 0, ',', '.') }}
                            @else
                            <span class="text-slate-400 font-normal italic">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap text-slate-500 text-[11px]">
                            {{ $item['date'] ? \Carbon\Carbon::parse($item['date'])->format('d M Y H:i') : '—' }}
                        </td>
                        <td class="px-4 py-3.5 text-right whitespace-nowrap">
                            <a href="{{ $item['link'] }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-xs transition">
                                <span>Review &bull; Otorisasi</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-10 h-10 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="font-semibold text-slate-600 text-xs">Semua approval telah dituntaskan</span>
                                <p class="text-[11px] text-slate-400 mt-0.5">Tidak ada antrean persetujuan prioritas tinggi yang tertahan saat ini.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- CROSS-SYSTEM 10-MODULE HEALTH RADAR -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
        <div class="mb-4">
            <h3 class="text-sm font-bold text-slate-900">Kesehatan Operasional 10 Modul Bisnis ERP</h3>
            <p class="text-[11px] text-slate-500">Status sinkronisasi dan kelancaran alur data lintas departemen.</p>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-xs">
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-800">1. HRIS &amp; Profil</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                </div>
                <div class="text-[11px] text-slate-500 mt-1">{{ $totalEmployees }} Karyawan Aktif</div>
            </div>

            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-800">2. Smart Attendance</span>
                    <span class="w-2 h-2 rounded-full {{ $highLateCount > 5 ? 'bg-amber-500' : 'bg-emerald-500' }}"></span>
                </div>
                <div class="text-[11px] text-slate-500 mt-1">Geofence GPS Aktif</div>
            </div>

            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-800">3. Work Management</span>
                    <span class="w-2 h-2 rounded-full {{ $overdueTasksCount > 0 ? 'bg-rose-500' : 'bg-emerald-500' }}"></span>
                </div>
                <div class="text-[11px] text-slate-500 mt-1">{{ $pendingTasks }} Review Tasks</div>
            </div>

            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-800">4. Program Magang</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                </div>
                <div class="text-[11px] text-slate-500 mt-1">Logbook &amp; Evaluasi OK</div>
            </div>

            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-800">5. Payroll Engine</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                </div>
                <div class="text-[11px] text-slate-500 mt-1">Closing Guard Siap</div>
            </div>

            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-800">6. Double-Entry Finance</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                </div>
                <div class="text-[11px] text-slate-500 mt-1">General Ledger Balance</div>
            </div>

            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-800">7. Reimbursement</span>
                    <span class="w-2 h-2 rounded-full {{ $pendingReimbursements > 0 ? 'bg-amber-500' : 'bg-emerald-500' }}"></span>
                </div>
                <div class="text-[11px] text-slate-500 mt-1">{{ $pendingReimbursements }} Klaim Tertunda</div>
            </div>

            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-800">8. Pengadaan / PO</span>
                    <span class="w-2 h-2 rounded-full {{ $pendingPO > 0 ? 'bg-amber-500' : 'bg-emerald-500' }}"></span>
                </div>
                <div class="text-[11px] text-slate-500 mt-1">{{ $activePOs }} Active POs</div>
            </div>

            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-800">9. Gudang &amp; Stok</span>
                    <span class="w-2 h-2 rounded-full {{ $lowStockCount > 0 ? 'bg-amber-500' : 'bg-emerald-500' }}"></span>
                </div>
                <div class="text-[11px] text-slate-500 mt-1">{{ $totalItemSKUs }} SKU Barang</div>
            </div>

            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-800">10. Register Aset</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                </div>
                <div class="text-[11px] text-slate-500 mt-1">{{ $activeAssetCount }} Unit Terpelihara</div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Expense vs Budget Trend Chart
        const expenseCtx = document.getElementById('expenseBudgetChart');
        if (expenseCtx) {
            new Chart(expenseCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($trendMonths) !!},
                    datasets: [
                        {
                            type: 'bar',
                            label: 'Realisasi Beban (IDR)',
                            data: {!! json_encode($trendExpenses) !!},
                            backgroundColor: '#4f46e5',
                            borderRadius: 8,
                            barPercentage: 0.5,
                        },
                        {
                            type: 'line',
                            label: 'Plafon Anggaran (IDR)',
                            data: {!! json_encode($trendBudgets) !!},
                            borderColor: '#cbd5e1',
                            borderWidth: 2,
                            borderDash: [5, 5],
                            pointRadius: 3,
                            fill: false,
                            tension: 0.1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return context.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function (value) {
                                    return 'Rp ' + (value / 1000000).toFixed(0) + ' Jt';
                                },
                                font: { size: 10 }
                            },
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

        // 2. Attendance Status Distribution Doughnut Chart
        const distCtx = document.getElementById('attendanceDistChart');
        if (distCtx) {
            new Chart(distCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Tepat Waktu', 'Terlambat', 'Luar Radius', 'Izin / Cuti', 'Sakit', 'Mangkir'],
                    datasets: [{
                        data: [
                            {{ $attendanceDistribution['on_time'] }},
                            {{ $attendanceDistribution['late'] }},
                            {{ $attendanceDistribution['out_of_radius'] }},
                            {{ $attendanceDistribution['leave'] }},
                            {{ $attendanceDistribution['sick'] }},
                            {{ $attendanceDistribution['absent'] }}
                        ],
                        backgroundColor: [
                            '#10b981', // emerald (on time)
                            '#f59e0b', // amber (late)
                            '#6366f1', // indigo (out of radius)
                            '#3b82f6', // blue (leave)
                            '#8b5cf6', // purple (sick)
                            '#ef4444'  // rose (absent)
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
                                boxWidth: 10,
                                padding: 8,
                                font: { size: 10 }
                            }
                        }
                    },
                    cutout: '65%'
                }
            });
        }
    });
</script>
@endpush
@endsection
