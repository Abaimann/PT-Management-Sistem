@extends('layouts.app')

@section('title', 'Jurnal Transaksi Umum')
@section('page_title', 'Jurnal Umum')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('finance.index') }}" class="hover:text-indigo-600 transition">Finance</a>
                <span>&bull;</span>
                <span>Jurnal Umum</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Jurnal Transaksi Umum</h1>
            <p class="text-xs text-slate-500 mt-1">Daftar ayat jurnal double-entry dengan status keseimbangan debit dan kredit berimbang.</p>
        </div>
        <a href="{{ route('finance.journals.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs transition self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Buat Jurnal Baru
        </a>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form action="{{ route('finance.journals.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <!-- Search -->
            <div class="relative sm:col-span-2">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nomor jurnal atau keterangan transaksi..."
                       class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
            </div>

            <!-- Status Filter -->
            <div>
                <select name="status" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-hidden bg-white">
                    <option value="">— Semua Status —</option>
                    <option value="POSTED" {{ request('status') === 'POSTED' ? 'selected' : '' }}>POSTED (Buku Besar)</option>
                    <option value="DRAFT" {{ request('status') === 'DRAFT' ? 'selected' : '' }}>DRAFT (Belum Diposting)</option>
                </select>
            </div>

            <!-- Action buttons -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold transition">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'status', 'start_date', 'end_date']))
                    <a href="{{ route('finance.journals.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table of Journals -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                        <th class="py-3.5 px-4">No. Jurnal &bull; Tanggal</th>
                        <th class="py-3.5 px-4">Deskripsi Transaksi</th>
                        <th class="py-3.5 px-4">Rincian Akun (Debet / Kredit)</th>
                        <th class="py-3.5 px-4 text-right">Total Transaksi</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse ($journals as $journal)
                    @php
                        $totalDebit = $journal->lines->sum('debit');
                        $totalCredit = $journal->lines->sum('credit');
                        $isBalanced = bccomp((string) round($totalDebit, 2), (string) round($totalCredit, 2), 2) === 0;
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3.5 px-4 align-top">
                            <span class="font-bold font-mono text-slate-900 text-xs">{{ $journal->entry_number }}</span>
                            <div class="text-[11px] text-slate-500 mt-0.5">
                                {{ $journal->date->isoFormat('dddd, D MMM Y') }}
                            </div>
                            @if ($journal->createdBy)
                                <div class="text-[10px] text-slate-400 mt-1">
                                    Oleh: {{ $journal->createdBy->name }}
                                </div>
                            @endif
                        </td>

                        <td class="py-3.5 px-4 align-top">
                            <div class="font-medium text-slate-800 leading-relaxed">{{ $journal->description }}</div>
                            @if ($journal->reference_type)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono bg-indigo-50 text-indigo-700 mt-1">
                                    Ref: {{ $journal->reference_type }} #{{ $journal->reference_id }}
                                </span>
                            @endif
                        </td>

                        <td class="py-3.5 px-4 align-top">
                            <div class="space-y-1 font-mono text-[11px] max-w-md">
                                @foreach ($journal->lines as $line)
                                <div class="flex items-center justify-between gap-4 py-0.5 border-b border-slate-50">
                                    <div class="truncate {{ $line->credit > 0 ? 'pl-4 text-slate-600' : 'font-semibold text-slate-800' }}">
                                        {{ $line->chartOfAccount?->account_code }} - {{ $line->chartOfAccount?->account_name }}
                                    </div>
                                    <div class="text-right shrink-0">
                                        @if ($line->debit > 0)
                                            <span class="text-slate-800 font-bold">D: Rp {{ number_format($line->debit, 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-slate-500">K: Rp {{ number_format($line->credit, 0, ',', '.') }}</span>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </td>

                        <td class="py-3.5 px-4 align-top text-right font-mono font-bold text-slate-900 text-xs">
                            Rp {{ number_format($totalDebit, 0, ',', '.') }}
                            @if (!$isBalanced)
                                <div class="text-[10px] text-rose-500 font-normal">TIDAK SEIMBANG</div>
                            @endif
                        </td>

                        <td class="py-3.5 px-4 align-top text-center">
                            @if ($journal->status === 'POSTED')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    POSTED
                                </span>
                                @if ($journal->posted_at)
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $journal->posted_at->format('d/m/Y H:i') }}</div>
                                @endif
                            @elseif ($journal->status === 'DRAFT')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    DRAFT
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    {{ $journal->status }}
                                </span>
                            @endif
                        </td>

                        <td class="py-3.5 px-4 align-top text-center">
                            @if ($journal->status === 'DRAFT')
                            <form action="{{ route('finance.journals.post', $journal) }}" method="POST" onsubmit="return confirm('Posting jurnal ini ke Buku Besar?');">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold shadow-xs transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Posting
                                </button>
                            </form>
                            @else
                                <span class="text-slate-400 text-xs font-mono">&check; Posted</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-xs text-slate-400">
                            Tidak ditemukan ayat jurnal yang cocok dengan filter pencarian.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($journals->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $journals->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
