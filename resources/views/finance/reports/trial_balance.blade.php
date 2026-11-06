@extends('layouts.app')

@section('title', 'Laporan Neraca Saldo (Trial Balance)')
@section('page_title', 'Neraca Saldo')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('finance.index') }}" class="hover:text-indigo-600 transition">Finance</a>
                <span>&bull;</span>
                <a href="{{ route('finance.journals.index') }}" class="hover:text-indigo-600 transition">Jurnal</a>
                <span>&bull;</span>
                <span>Neraca Saldo</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Neraca Saldo (Trial Balance)</h1>
            <p class="text-xs text-slate-500 mt-1">Uji keseimbangan debit dan kredit seluruh akun buku besar per tanggal cut-off pelaporan.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('finance.reports.general_ledger') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold border border-slate-200 shadow-xs transition">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
                Buku Besar &rarr;
            </a>
        </div>
    </div>

    <!-- Filter As-Of Date -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form action="{{ route('finance.reports.trial_balance') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-3">
            <div class="w-full sm:w-72">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Per Tanggal (Cut-off Date)</label>
                <input type="date" name="as_of_date" value="{{ $asOfDate }}"
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
            </div>
            <button type="submit" class="w-full sm:w-auto px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs transition">
                Hitung Neraca Saldo
            </button>
        </form>
    </div>

    <!-- Balance Verification Banner -->
    <div class="p-5 rounded-2xl border flex flex-col sm:flex-row items-center justify-between gap-4 {{ $trialBalance['is_balanced'] ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-rose-50 border-rose-200 text-rose-900' }}">
        <div class="flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-2xl flex items-center justify-center font-bold text-base {{ $trialBalance['is_balanced'] ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white' }}">
                @if ($trialBalance['is_balanced'])
                    &check;
                @else
                    !
                @endif
            </div>
            <div>
                <h3 class="text-sm font-bold">
                    {{ $trialBalance['is_balanced'] ? 'Neraca Saldo Sempurna & Berimbang (Balanced)' : 'Peringatan: Neraca Saldo Tidak Seimbang (Unbalanced)!' }}
                </h3>
                <p class="text-xs opacity-80 mt-0.5">
                    @if ($trialBalance['is_balanced'])
                        Total saldo debet dan saldo kredit bernilai sama persis Rp {{ number_format($trialBalance['total_debit'], 2, ',', '.') }}.
                    @else
                        Terdapat selisih sebesar Rp {{ number_format($trialBalance['difference'], 2, ',', '.') }} antara total debit dan kredit.
                    @endif
                </p>
            </div>
        </div>

        <div class="flex items-center gap-4 text-xs font-mono font-bold bg-white/70 px-4 py-2.5 rounded-xl border border-black/5">
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-sans">Total Debet</span>
                <span>Rp {{ number_format($trialBalance['total_debit'], 2, ',', '.') }}</span>
            </div>
            <div class="text-slate-300">|</div>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-sans">Total Kredit</span>
                <span>Rp {{ number_format($trialBalance['total_credit'], 2, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Trial Balance Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-200">
                        <th class="py-3.5 px-4 w-28">Kode Akun</th>
                        <th class="py-3.5 px-4">Nama Akun</th>
                        <th class="py-3.5 px-4 w-32">Klasifikasi</th>
                        <th class="py-3.5 px-4 w-44 text-right">Saldo Debet (Rp)</th>
                        <th class="py-3.5 px-4 w-44 text-right">Saldo Kredit (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse ($trialBalance['rows'] as $row)
                    <tr class="hover:bg-slate-50/70 transition {{ ($row['debit'] == 0 && $row['credit'] == 0) ? 'text-slate-400' : 'text-slate-800' }}">
                        <td class="py-2.5 px-4 font-mono font-bold">
                            {{ $row['account_code'] }}
                        </td>
                        <td class="py-2.5 px-4 font-medium">
                            {{ $row['account_name'] }}
                        </td>
                        <td class="py-2.5 px-4">
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">
                                {{ $row['classification'] }}
                            </span>
                        </td>
                        <td class="py-2.5 px-4 text-right font-mono font-semibold">
                            @if ($row['debit'] > 0)
                                Rp {{ number_format($row['debit'], 2, ',', '.') }}
                            @else
                                <span class="text-slate-300 font-normal">0,00</span>
                            @endif
                        </td>
                        <td class="py-2.5 px-4 text-right font-mono font-semibold">
                            @if ($row['credit'] > 0)
                                Rp {{ number_format($row['credit'], 2, ',', '.') }}
                            @else
                                <span class="text-slate-300 font-normal">0,00</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-xs text-slate-400">
                            Belum ada akun atau transaksi yang dibukukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 text-xs font-bold border-t-2 border-slate-200">
                        <td colspan="3" class="py-3.5 px-4 text-right uppercase tracking-wider text-slate-700">
                            Total Neraca Saldo:
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono text-slate-900 text-sm">
                            Rp {{ number_format($trialBalance['total_debit'], 2, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono text-slate-900 text-sm">
                            Rp {{ number_format($trialBalance['total_credit'], 2, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>
@endsection
