@extends('layouts.app')

@section('title', 'Laporan Buku Besar (General Ledger)')
@section('page_title', 'Buku Besar')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('finance.index') }}" class="hover:text-indigo-600 transition">Finance</a>
                <span>&bull;</span>
                <a href="{{ route('finance.journals.index') }}" class="hover:text-indigo-600 transition">Jurnal</a>
                <span>&bull;</span>
                <span>Buku Besar</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Buku Besar (General Ledger)</h1>
            <p class="text-xs text-slate-500 mt-1">Rincian mutasi debet, kredit, dan kalkulasi saldo berjalan per rekening akun.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('finance.reports.trial_balance') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold border border-slate-200 shadow-xs transition">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Lihat Neraca Saldo &rarr;
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form action="{{ route('finance.reports.general_ledger') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
            <!-- Akun CoA -->
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Rekening Akun</label>
                <select name="account_id" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-hidden bg-white font-mono">
                    @foreach ($accounts as $acc)
                        <option value="{{ $acc->id }}" {{ $selectedAccountId == $acc->id ? 'selected' : '' }}>
                            {{ $acc->account_code }} - {{ $acc->account_name }} ({{ $acc->classification }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Start Date -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ $startDate }}"
                       class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
            </div>

            <!-- End Date & Submit -->
            <div class="flex items-center gap-2">
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Akhir</label>
                    <input type="date" name="end_date" value="{{ $endDate }}"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                </div>
                <button type="submit" class="px-4 py-2 mt-auto rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold transition self-end">
                    Tampilkan
                </button>
            </div>
        </form>
    </div>

    @if ($ledgerData)
    <!-- Account Summary Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <span class="font-mono font-bold text-base text-slate-900">{{ $ledgerData['account']->account_code }}</span>
                    <span class="text-sm font-bold text-slate-800">{{ $ledgerData['account']->account_name }}</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                        {{ $ledgerData['account']->classification }}
                    </span>
                </div>
                <div class="text-xs text-slate-500 mt-1">
                    Saldo Normal: <span class="font-bold text-slate-700 font-mono">{{ $ledgerData['account']->normal_balance }}</span>
                    &bull; Rentang Periode: <span class="font-medium text-slate-700">{{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMM Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMM Y') }}</span>
                </div>
            </div>
            <div class="text-right">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Saldo Akhir Berjalan</div>
                <div class="text-xl font-extrabold text-indigo-700 font-mono mt-0.5">
                    Rp {{ number_format($ledgerData['closing_balance'], 2, ',', '.') }}
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-4 text-xs">
            <div class="p-3 bg-slate-50 rounded-xl">
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Saldo Awal</div>
                <div class="text-sm font-bold font-mono text-slate-800 mt-0.5">
                    Rp {{ number_format($ledgerData['opening_balance'], 2, ',', '.') }}
                </div>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl">
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Mutasi Debet</div>
                <div class="text-sm font-bold font-mono text-emerald-600 mt-0.5">
                    Rp {{ number_format($ledgerData['total_debit'], 2, ',', '.') }}
                </div>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl">
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Mutasi Kredit</div>
                <div class="text-sm font-bold font-mono text-rose-600 mt-0.5">
                    Rp {{ number_format($ledgerData['total_credit'], 2, ',', '.') }}
                </div>
            </div>
            <div class="p-3 bg-indigo-50/60 rounded-xl">
                <div class="text-[10px] font-bold uppercase tracking-wider text-indigo-600">Perubahan Bersih</div>
                <div class="text-sm font-bold font-mono text-indigo-900 mt-0.5">
                    Rp {{ number_format($ledgerData['total_debit'] - $ledgerData['total_credit'], 2, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Ledger Entries Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-200">
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">No. Bukti Jurnal</th>
                        <th class="py-3 px-4">Keterangan Transaksi</th>
                        <th class="py-3 px-4 text-right">Debet (Rp)</th>
                        <th class="py-3 px-4 text-right">Kredit (Rp)</th>
                        <th class="py-3 px-4 text-right">Saldo Berjalan (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    <!-- Opening Balance Row -->
                    <tr class="bg-slate-50/50 font-medium text-slate-500 italic">
                        <td class="py-3 px-4">{{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMM Y') }}</td>
                        <td class="py-3 px-4 font-mono">—</td>
                        <td class="py-3 px-4 font-semibold text-slate-700">Saldo Awal (Opening Balance)</td>
                        <td class="py-3 px-4 text-right font-mono">—</td>
                        <td class="py-3 px-4 text-right font-mono">—</td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-slate-800 not-italic">
                            Rp {{ number_format($ledgerData['opening_balance'], 2, ',', '.') }}
                        </td>
                    </tr>

                    @forelse ($ledgerData['transactions'] as $tx)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3 px-4 whitespace-nowrap text-slate-600">
                            {{ \Carbon\Carbon::parse($tx['date'])->isoFormat('D MMM Y') }}
                        </td>
                        <td class="py-3 px-4 font-mono font-bold text-indigo-600 whitespace-nowrap">
                            {{ $tx['entry_number'] }}
                        </td>
                        <td class="py-3 px-4 text-slate-800">
                            {{ $tx['description'] }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-semibold text-slate-800">
                            @if ($tx['debit'] > 0)
                                Rp {{ number_format($tx['debit'], 2, ',', '.') }}
                            @else
                                <span class="text-slate-300 font-normal">0,00</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-semibold text-slate-800">
                            @if ($tx['credit'] > 0)
                                Rp {{ number_format($tx['credit'], 2, ',', '.') }}
                            @else
                                <span class="text-slate-300 font-normal">0,00</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">
                            Rp {{ number_format($tx['running_balance'], 2, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-xs text-slate-400">
                            Tidak ada transaksi yang tercatat pada rentang tanggal ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 text-xs font-bold border-t-2 border-slate-200">
                        <td colspan="3" class="py-3 px-4 text-right uppercase tracking-wider text-slate-600">
                            Total Mutasi &amp; Saldo Akhir:
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-emerald-700">
                            Rp {{ number_format($ledgerData['total_debit'], 2, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-rose-700">
                            Rp {{ number_format($ledgerData['total_credit'], 2, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-extrabold text-indigo-700">
                            Rp {{ number_format($ledgerData['closing_balance'], 2, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
