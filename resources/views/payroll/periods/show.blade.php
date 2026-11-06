@extends('layouts.app')

@section('title', 'Detail Periode Payroll - ' . $period->name)
@section('page_title', 'Detail Periode Payroll')

@section('content')
<div class="space-y-6" x-data="{ approveModal: false, closeModal: false }">

    <!-- Header Navigation & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('payroll.periods.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-900 transition flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Semua Periode
                </a>
                <span class="text-slate-300">/</span>
                <span class="font-mono text-xs text-slate-400">{{ $period->code }}</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight mt-1">{{ $period->name }}</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Cutoff Absensi: <span class="font-semibold text-slate-700">{{ $period->effective_cutoff_start_date->format('d M Y') }} s/d {{ $period->effective_cutoff_end_date->format('d M Y') }}</span>
                &bull; Tanggal Pembayaran: <span class="font-semibold text-slate-700">{{ $period->payment_date->format('d M Y') }}</span>
            </p>
        </div>

        <!-- Status Badges & Quick Action Buttons -->
        <div class="flex flex-wrap items-center gap-2">
            @if($period->status === 'DRAFT')
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span> Status: DRAFT
                </span>
                <form method="POST" action="{{ route('payroll.periods.calculate', $period) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        Jalankan Kalkulasi Payroll
                    </button>
                </form>
            @elseif($period->status === 'CALCULATED')
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span> Status: CALCULATED
                </span>

                <!-- Re-calculate Button -->
                <form method="POST" action="{{ route('payroll.periods.calculate', $period) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Kalkulasi Ulang
                    </button>
                </form>

                <!-- Approve Button -->
                <button type="button" @click="approveModal = true" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Setujui (Approve)
                </button>
            @elseif($period->status === 'APPROVED')
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span> Status: APPROVED
                </span>

                <!-- Close Guard Button -->
                <button type="button" @click="closeModal = true" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Tutup Periode (Closing Guard)
                </button>
            @elseif($period->status === 'CLOSED')
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    CLOSED &amp; TERKUNCI
                </span>
            @endif
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center justify-between">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    </div>
    @endif

    <!-- Workflow Status Banner -->
    @if($period->status === 'CLOSED')
    <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200 text-emerald-900 text-xs flex items-start gap-3">
        <div class="p-2 rounded-xl bg-emerald-100 text-emerald-700 shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        </div>
        <div>
            <div class="font-bold text-sm text-emerald-950">Periode Payroll Resmi Ditutup &amp; Data Presensi Dikunci</div>
            <p class="text-emerald-800 mt-1 leading-relaxed">
                Ditutup oleh: <span class="font-semibold">{{ $period->closedBy?->name ?? 'Administrator' }}</span> pada <span class="font-semibold">{{ $period->closed_at ? $period->closed_at->format('d F Y, H:i T') : '—' }}</span>.
                Semua rekaman presensi pada rentang {{ $period->effective_cutoff_start_date->format('d/m/Y') }} - {{ $period->effective_cutoff_end_date->format('d/m/Y') }} telah dikunci (<code class="font-mono text-emerald-900">is_locked = true</code>) dan slip gaji telah diterbitkan untuk diakses karyawan.
            </p>
        </div>
    </div>
    @elseif($period->status === 'APPROVED')
    <div class="p-4 rounded-2xl bg-indigo-50/70 border border-indigo-200 text-indigo-900 text-xs flex items-start gap-3">
        <div class="p-2 rounded-xl bg-indigo-100 text-indigo-700 shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <div class="font-bold text-sm text-indigo-950">Disetujui Direksi / Manajemen — Siap Ditutup</div>
            <p class="text-indigo-800 mt-1 leading-relaxed">
                Hasil perhitungan batch payroll telah disetujui. Lakukan penutupan periode (Closing Guard) untuk mengunci seluruh catatan presensi secara permanen.
                <span class="font-semibold underline">Catatan:</span> Sistem ERP tidak melakukan transfer dana otomatis keluar. Pembayaran aktual dilakukan secara manual via perbankan/payroll bank transfer.
            </p>
        </div>
    </div>
    @endif

    <!-- Batch Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Karyawan</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900 mt-2">
                {{ $latestRun ? number_format($latestRun->total_employees) : '0' }}
            </div>
            <div class="text-[11px] text-slate-400 mt-1">Karyawan aktif terkalkulasi</div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Gaji Kotor (Gross)</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="text-xl font-bold text-slate-900 mt-2">
                Rp {{ $latestRun ? number_format($latestRun->total_gross_pay, 0, ',', '.') : '0' }}
            </div>
            <div class="text-[11px] text-slate-400 mt-1">Gaji pokok + semua tunjangan</div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Potongan (Deductions)</span>
                <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="text-xl font-bold text-rose-600 mt-2">
                Rp {{ $latestRun ? number_format($latestRun->total_deductions, 0, ',', '.') : '0' }}
            </div>
            <div class="text-[11px] text-slate-400 mt-1">BPJS, kasbon, terlambat, mangkir</div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs ring-1 ring-emerald-500/20">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Total Net Pay (Take Home)</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="text-xl font-black text-emerald-700 mt-2">
                Rp {{ $latestRun ? number_format($latestRun->total_net_pay, 0, ',', '.') : '0' }}
            </div>
            <div class="text-[11px] text-emerald-600/80 mt-1">Total pengeluaran gaji bersih</div>
        </div>
    </div>

    <!-- Filter & Table of Employee Payslips -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Daftar Slip Gaji Pegawai</h3>
                <p class="text-[11px] text-slate-500">Rincian take home pay setiap karyawan aktif pada batch ini.</p>
            </div>
            <form method="GET" action="{{ route('payroll.periods.show', $period) }}" class="flex items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / NIK..." class="text-xs rounded-xl border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 py-1.5 px-3">
                <button type="submit" class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-xl transition">
                    Cari
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/75 border-b border-slate-200 text-slate-700 font-semibold uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="px-4 py-3">No. Slip</th>
                        <th class="px-4 py-3">Karyawan</th>
                        <th class="px-4 py-3">Departemen &amp; Jabatan</th>
                        <th class="px-4 py-3">Gaji Kotor</th>
                        <th class="px-4 py-3">Total Potongan</th>
                        <th class="px-4 py-3">Gaji Bersih (Net)</th>
                        <th class="px-4 py-3">Status Slip</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payslips as $slip)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-4 py-3.5 font-mono text-[11px] text-slate-500">
                            {{ $slip->payslip_number }}
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-bold text-slate-900">{{ $slip->employee->full_name }}</div>
                            <div class="font-mono text-[10px] text-slate-400">NIK: {{ $slip->employee->nik }}</div>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <div class="text-slate-800 font-medium">{{ $slip->employee->department?->name ?? '—' }}</div>
                            <div class="text-[10px] text-slate-400">{{ $slip->employee->position?->name ?? '—' }}</div>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap font-medium text-slate-900">
                            Rp {{ number_format($slip->gross_salary, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap font-medium text-rose-600">
                            - Rp {{ number_format($slip->total_deductions, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap font-bold text-emerald-700">
                            Rp {{ number_format($slip->net_salary, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            @if($slip->status === 'DRAFT')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                DRAFT
                            </span>
                            @elseif($slip->status === 'PUBLISHED')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                PUBLISHED
                            </span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                {{ $slip->status }}
                            </span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-right whitespace-nowrap">
                            <a href="{{ route('payroll.payslips.show', $slip) }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-700 font-semibold text-xs transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Lihat Slip</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-slate-400">
                            @if(!$latestRun)
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-10 h-10 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <span class="font-semibold text-slate-600 text-xs">Belum ada hasil kalkulasi</span>
                                <p class="text-[11px] text-slate-400 mt-1">Klik tombol "Jalankan Kalkulasi Payroll" di atas untuk memproses gaji seluruh karyawan.</p>
                            </div>
                            @else
                            <span>Tidak ditemukan data slip gaji yang cocok dengan filter pencarian.</span>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payslips->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
            {{ $payslips->links() }}
        </div>
        @endif
    </div>

    <!-- Approval Modal (Alpine.js) -->
    <div x-show="approveModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-cloak>
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200" @click.away="approveModal = false">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="text-base font-bold text-slate-900">Konfirmasi Persetujuan Payroll</h3>
            <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                Apakah Anda yakin ingin menyetujui (<span class="font-semibold text-indigo-600">APPROVE</span>) kalkulasi payroll untuk periode <span class="font-semibold text-slate-900">{{ $period->name }}</span>?
            </p>
            <div class="mt-4 p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs space-y-1">
                <div class="flex justify-between text-slate-600">
                    <span>Total Karyawan:</span>
                    <span class="font-bold text-slate-900">{{ $latestRun?->total_employees }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Total Net Pay:</span>
                    <span class="font-bold text-emerald-700">Rp {{ number_format($latestRun?->total_net_pay ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="mt-6 flex items-center justify-end gap-3">
                <button type="button" @click="approveModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold">
                    Batal
                </button>
                <form method="POST" action="{{ route('payroll.periods.approve', $period) }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs">
                        Ya, Setujui Payroll
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Closing Guard Modal (Alpine.js) -->
    <div x-show="closeModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-cloak>
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-200" @click.away="closeModal = false">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <h3 class="text-base font-bold text-slate-900">Penutupan Resmi Periode (Payroll Closing Guard)</h3>
            <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                Anda akan menutup secara permanen periode payroll <span class="font-semibold text-slate-900">{{ $period->name }}</span>.
            </p>
            <div class="mt-4 p-4 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900 space-y-2">
                <div class="font-bold flex items-center gap-1.5 text-amber-800">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Efek Penutupan Payroll:
                </div>
                <ul class="list-disc list-inside space-y-1 text-amber-800 text-[11px]">
                    <li>Seluruh data kehadiran pada rentang cutoff akan <strong>dikunci permanen</strong> (<code class="font-mono">is_locked = true</code>).</li>
                    <li>Slip gaji akan diterbitkan (<code class="font-mono">PUBLISHED</code>) agar dapat diunduh/dilihat pegawai.</li>
                    <li><strong>Tidak ada transfer uang otomatis</strong> yang dipicu oleh sistem ERP ini.</li>
                    <li>Tindakan penutupan akan dicatat secara lengkap pada Audit Log sistem.</li>
                </ul>
            </div>
            <div class="mt-6 flex items-center justify-end gap-3">
                <button type="button" @click="closeModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold">
                    Batal
                </button>
                <form method="POST" action="{{ route('payroll.periods.close', $period) }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs">
                        Konfirmasi Tutup &amp; Kunci Periode
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
