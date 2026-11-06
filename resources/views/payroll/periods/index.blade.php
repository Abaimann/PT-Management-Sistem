@extends('layouts.app')

@section('title', 'Daftar Periode Payroll')
@section('page_title', 'Periode Payroll')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Manajemen Penggajian (Payroll)</h1>
            <p class="text-xs text-slate-500 mt-1">Pengelolaan periode cutoff, rekapitulasi kehadiran, kalkulasi slip gaji, dan penutupan batch resmi.</p>
        </div>
        <div class="flex items-center gap-2 self-start sm:self-auto">
            @if(auth()->user()->employee)
            <a href="{{ route('payroll.payslips.my') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold shadow-sm transition">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                Slip Gaji Saya
            </a>
            @endif

            @if($canManagePayroll)
            <a href="{{ route('payroll.periods.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Buka Periode Payroll Baru
            </a>
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

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <div class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Total Periode</div>
            <div class="text-xl font-bold text-slate-900 mt-1">{{ number_format($stats['total_periods']) }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Tercatat di sistem</div>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <div class="text-[11px] font-medium text-amber-600 uppercase tracking-wider">Draft</div>
            <div class="text-xl font-bold text-amber-600 mt-1">{{ number_format($stats['draft_periods']) }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Belum dikalkulasi</div>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <div class="text-[11px] font-medium text-blue-600 uppercase tracking-wider">Terkalkulasi</div>
            <div class="text-xl font-bold text-blue-600 mt-1">{{ number_format($stats['calculated_periods']) }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Menunggu approval</div>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <div class="text-[11px] font-medium text-indigo-600 uppercase tracking-wider">Disetujui</div>
            <div class="text-xl font-bold text-indigo-600 mt-1">{{ number_format($stats['approved_periods']) }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Siap closing</div>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <div class="text-[11px] font-medium text-emerald-600 uppercase tracking-wider">Closed & Locked</div>
            <div class="text-xl font-bold text-emerald-600 mt-1">{{ number_format($stats['closed_periods']) }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Resmi ditutup</div>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <div class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Total Payout Terakhir</div>
            <div class="text-sm font-bold text-slate-900 mt-1 truncate" title="Rp {{ number_format($stats['latest_payout'], 0, ',', '.') }}">
                Rp {{ number_format($stats['latest_payout'] / 1000000, 1) }} Jt
            </div>
            <div class="text-[10px] text-slate-400 mt-0.5">Batch terakhir</div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('payroll.periods.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Status Periode</label>
                <select name="status" class="w-full text-xs rounded-xl border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 py-2">
                    <option value="">Semua Status</option>
                    <option value="DRAFT" {{ request('status') === 'DRAFT' ? 'selected' : '' }}>DRAFT (Belum Kalkulasi)</option>
                    <option value="CALCULATED" {{ request('status') === 'CALCULATED' ? 'selected' : '' }}>CALCULATED (Terkalkulasi)</option>
                    <option value="APPROVED" {{ request('status') === 'APPROVED' ? 'selected' : '' }}>APPROVED (Disetujui Direksi)</option>
                    <option value="CLOSED" {{ request('status') === 'CLOSED' ? 'selected' : '' }}>CLOSED (Selesai &amp; Terkunci)</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Cari Periode</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama periode atau kode PR-..." class="w-full text-xs rounded-xl border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 py-2">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-xl shadow-xs transition">
                    Filter
                </button>
                <a href="{{ route('payroll.periods.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table of Payroll Periods -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/75 border-b border-slate-200 text-slate-700 font-semibold uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Kode &amp; Nama Periode</th>
                        <th class="px-4 py-3">Rentang Cutoff</th>
                        <th class="px-4 py-3">Tgl Pembayaran</th>
                        <th class="px-4 py-3">Karyawan</th>
                        <th class="px-4 py-3">Total Net Payout</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($periods as $period)
                    @php
                        $latestRun = $period->latestPayrollRun;
                    @endphp
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-4 py-3.5">
                            <div class="font-bold text-slate-900">{{ $period->name }}</div>
                            <div class="font-mono text-[10px] text-slate-400 mt-0.5">{{ $period->code }}</div>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <div class="text-slate-800 font-medium">
                                {{ $period->effective_cutoff_start_date->format('d M Y') }} — {{ $period->effective_cutoff_end_date->format('d M Y') }}
                            </div>
                            <div class="text-[10px] text-slate-400">Periode: {{ $period->start_date->format('d/m/Y') }} - {{ $period->end_date->format('d/m/Y') }}</div>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 font-medium text-slate-700 text-[11px]">
                                {{ $period->payment_date->format('d M Y') }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            @if($latestRun)
                            <span class="font-semibold text-slate-900">{{ $latestRun->total_employees }}</span>
                            <span class="text-slate-400 text-[10px]">karyawan</span>
                            @else
                            <span class="text-slate-400 italic text-[11px]">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            @if($latestRun)
                            <span class="font-bold text-slate-900">Rp {{ number_format($latestRun->total_net_pay, 0, ',', '.') }}</span>
                            @else
                            <span class="text-slate-400 italic text-[11px]">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            @if($period->status === 'DRAFT')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200/80">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> DRAFT
                            </span>
                            @elseif($period->status === 'CALCULATED')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/80">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> CALCULATED
                            </span>
                            @elseif($period->status === 'APPROVED')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200/80">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span> APPROVED
                            </span>
                            @elseif($period->status === 'CLOSED')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> CLOSED &amp; LOCKED
                            </span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-right whitespace-nowrap">
                            <a href="{{ route('payroll.periods.show', $period) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-700 font-semibold text-xs transition">
                                <span>Rincian &amp; Slip</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                                <span class="text-xs font-semibold text-slate-600">Belum ada periode payroll</span>
                                <p class="text-[11px] text-slate-400 mt-1">Buat periode payroll baru untuk memulai kalkulasi gaji bulanan.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($periods->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
            {{ $periods->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
