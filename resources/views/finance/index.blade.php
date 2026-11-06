@extends('layouts.app')

@section('title', 'Dashboard Keuangan & Akuntansi')
@section('page_title', 'Finance & Accounting')

@section('content')
<div class="space-y-6">

    <!-- Header & Quick Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Dashboard Keuangan &amp; Akuntansi</h1>
            <p class="text-xs text-slate-500 mt-1">Sistem pencatatan double-entry, buku besar, neraca saldo, dan verifikasi klaim reimbursement operasional.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('finance.journals.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Buat Jurnal Baru
            </a>
            <a href="{{ route('finance.reports.trial_balance') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold border border-slate-200 shadow-xs transition">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Neraca Saldo
            </a>
            <a href="{{ route('reimbursements.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold border border-emerald-200 shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Klaim Reimbursement
            </a>
        </div>
    </div>

    <!-- Financial KPI Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Kas & Bank -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Kas &amp; Bank</span>
                <div class="text-xl font-extrabold text-slate-900 mt-1">
                    Rp {{ number_format($metrics['cash_bank_balance'], 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Akun Kas, Mandiri, BCA
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- Beban Operasional Bulan Ini -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Pengeluaran Bulan Ini</span>
                <div class="text-xl font-extrabold text-rose-600 mt-1">
                    Rp {{ number_format($metrics['monthly_expense'], 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-slate-500 mt-1">
                    Periode {{ now()->isoFormat('MMMM Y') }}
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
            </div>
        </div>

        <!-- Pendapatan Bulan Ini -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Pendapatan Bulan Ini</span>
                <div class="text-xl font-extrabold text-emerald-600 mt-1">
                    Rp {{ number_format($metrics['monthly_revenue'], 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-slate-500 mt-1">
                    Akun Klasifikasi Revenue
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
        </div>

        <!-- Pending Reimbursement -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Pending Reimbursement</span>
                <div class="text-xl font-extrabold text-amber-600 mt-1">
                    {{ $metrics['pending_reimbursements_count'] }} <span class="text-xs font-normal text-slate-500">Klaim</span>
                </div>
                <div class="text-[11px] font-medium text-amber-700 mt-1">
                    Total: Rp {{ number_format($metrics['pending_reimbursements_amount'], 0, ',', '.') }}
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>

    <!-- Quick Navigation Sub-Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <a href="{{ route('finance.journals.index') }}" class="p-4 bg-white rounded-2xl border border-slate-200 hover:border-indigo-300 hover:shadow-xs transition group">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-slate-100 group-hover:bg-indigo-50 text-slate-600 group-hover:text-indigo-600 flex items-center justify-center transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-800">Jurnal Umum</h4>
                    <p class="text-[11px] text-slate-400">{{ $metrics['posted_journals_count'] }} Posted &bull; {{ $metrics['draft_journals_count'] }} Draft</p>
                </div>
            </div>
        </a>

        <a href="{{ route('finance.accounts.index') }}" class="p-4 bg-white rounded-2xl border border-slate-200 hover:border-indigo-300 hover:shadow-xs transition group">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-slate-100 group-hover:bg-indigo-50 text-slate-600 group-hover:text-indigo-600 flex items-center justify-center transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-800">Chart of Accounts</h4>
                    <p class="text-[11px] text-slate-400">Master Akun Standar</p>
                </div>
            </div>
        </a>

        <a href="{{ route('finance.reports.general_ledger') }}" class="p-4 bg-white rounded-2xl border border-slate-200 hover:border-indigo-300 hover:shadow-xs transition group">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-slate-100 group-hover:bg-indigo-50 text-slate-600 group-hover:text-indigo-600 flex items-center justify-center transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-800">Buku Besar</h4>
                    <p class="text-[11px] text-slate-400">General Ledger Mutasi</p>
                </div>
            </div>
        </a>

        <a href="{{ route('finance.reports.trial_balance') }}" class="p-4 bg-white rounded-2xl border border-slate-200 hover:border-indigo-300 hover:shadow-xs transition group">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-slate-100 group-hover:bg-indigo-50 text-slate-600 group-hover:text-indigo-600 flex items-center justify-center transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-800">Neraca Saldo</h4>
                    <p class="text-[11px] text-slate-400">Keseimbangan Debit/Kredit</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Content Grid: Jurnal Terbaru & Klaim Reimbursement -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Jurnal Transaksi Terbaru (2 Kolom) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden flex flex-col">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-indigo-600"></div>
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Jurnal Transaksi Terakhir</h3>
                </div>
                <a href="{{ route('finance.journals.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">
                    Lihat Semua &rarr;
                </a>
            </div>

            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                            <th class="py-3 px-4">No. Jurnal &bull; Tanggal</th>
                            <th class="py-3 px-4">Deskripsi</th>
                            <th class="py-3 px-4 text-right">Debit / Kredit</th>
                            <th class="py-3 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse ($metrics['recent_journals'] as $j)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 px-4">
                                <span class="font-bold font-mono text-slate-800">{{ $j->entry_number }}</span>
                                <div class="text-[11px] text-slate-400">{{ $j->date->isoFormat('D MMM Y') }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-medium text-slate-800 truncate max-w-xs">{{ $j->description }}</div>
                                @if ($j->reference_type)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono bg-slate-100 text-slate-600 mt-0.5">
                                        Ref: {{ $j->reference_type }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-medium text-slate-700">
                                Rp {{ number_format($j->lines->sum('debit'), 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if ($j->status === 'POSTED')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        POSTED
                                    </span>
                                @elseif ($j->status === 'DRAFT')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        DRAFT
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        {{ $j->status }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-xs text-slate-400">
                                Belum ada riwayat transaksi jurnal tercatat.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Klaim Reimbursement Terbaru (1 Kolom) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden flex flex-col">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Klaim Reimbursement</h3>
                </div>
                <a href="{{ route('reimbursements.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">
                    Semua &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-100 flex-1">
                @forelse ($metrics['recent_reimbursements'] as $r)
                <div class="p-4 hover:bg-slate-50/60 transition">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-mono font-bold text-xs text-slate-800">{{ $r->claim_number }}</span>
                        @if ($r->status === 'PAID')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">DICAIRKAN</span>
                        @elseif ($r->status === 'PENDING')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">MENUNGGU</span>
                        @elseif ($r->status === 'REJECTED')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">DITOLAK</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">{{ $r->status }}</span>
                        @endif
                    </div>
                    <div class="text-xs font-semibold text-slate-800 mt-1 truncate">{{ $r->title }}</div>
                    <div class="flex items-center justify-between text-[11px] text-slate-400 mt-1">
                        <span>{{ $r->employee?->full_name ?? 'Pegawai' }}</span>
                        <span class="font-mono font-bold text-slate-700">Rp {{ number_format($r->amount, 0, ',', '.') }}</span>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-xs text-slate-400">
                    Tidak ada permohonan reimbursement terbaru.
                </div>
                @endforelse
            </div>
            <div class="p-3 bg-slate-50/70 border-t border-slate-100 text-center">
                <a href="{{ route('reimbursements.create') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Ajukan Klaim Baru
                </a>
            </div>
        </div>

    </div>

</div>
@endsection
