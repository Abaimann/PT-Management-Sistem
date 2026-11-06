@extends('layouts.app')

@section('title', 'Chart of Accounts (CoA)')
@section('page_title', 'Chart of Accounts')

@section('content')
<div class="space-y-6" x-data="{ openCreateModal: false }">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('finance.index') }}" class="hover:text-indigo-600 transition">Finance</a>
                <span>&bull;</span>
                <span>Bagan Akun Standar</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Chart of Accounts (CoA)</h1>
            <p class="text-xs text-slate-500 mt-1">Struktur bagan akun standar Indonesia untuk pembukuan jurnal double-entry, buku besar, dan neraca saldo.</p>
        </div>
        <button type="button"
                @click="openCreateModal = true"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs transition self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Tambah Akun Baru
        </button>
    </div>

    <!-- Filter Tabs per Klasifikasi -->
    <div class="bg-white p-2 rounded-2xl border border-slate-200 shadow-xs flex flex-wrap items-center gap-1.5 text-xs font-medium">
        <a href="{{ route('finance.accounts.index') }}"
           class="px-3 py-1.5 rounded-xl transition {{ empty($classification) ? 'bg-indigo-600 text-white font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
            Semua Klasifikasi ({{ $accounts->count() }})
        </a>
        <a href="{{ route('finance.accounts.index', ['classification' => 'ASSET']) }}"
           class="px-3 py-1.5 rounded-xl transition {{ $classification === 'ASSET' ? 'bg-indigo-600 text-white font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
            Aset (1000)
        </a>
        <a href="{{ route('finance.accounts.index', ['classification' => 'LIABILITY']) }}"
           class="px-3 py-1.5 rounded-xl transition {{ $classification === 'LIABILITY' ? 'bg-indigo-600 text-white font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
            Kewajiban (2000)
        </a>
        <a href="{{ route('finance.accounts.index', ['classification' => 'EQUITY']) }}"
           class="px-3 py-1.5 rounded-xl transition {{ $classification === 'EQUITY' ? 'bg-indigo-600 text-white font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
            Ekuitas (3000)
        </a>
        <a href="{{ route('finance.accounts.index', ['classification' => 'REVENUE']) }}"
           class="px-3 py-1.5 rounded-xl transition {{ $classification === 'REVENUE' ? 'bg-indigo-600 text-white font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
            Pendapatan (4000)
        </a>
        <a href="{{ route('finance.accounts.index', ['classification' => 'EXPENSE']) }}"
           class="px-3 py-1.5 rounded-xl transition {{ $classification === 'EXPENSE' ? 'bg-indigo-600 text-white font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
            Beban Operasional (5000)
        </a>
    </div>

    <!-- Table Master CoA -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                        <th class="py-3.5 px-4">Kode Akun</th>
                        <th class="py-3.5 px-4">Nama Akun</th>
                        <th class="py-3.5 px-4">Klasifikasi</th>
                        <th class="py-3.5 px-4">Saldo Normal</th>
                        <th class="py-3.5 px-4">Induk Akun</th>
                        <th class="py-3.5 px-4 text-right">Saldo Berjalan</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse ($accounts as $acc)
                    @php
                        $isHeader = $acc->children->isNotEmpty();
                        $bal = $balances[$acc->id] ?? 0;
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition {{ $isHeader ? 'bg-slate-50/40 font-semibold' : '' }}">
                        <td class="py-3 px-4 font-mono font-bold text-slate-800">
                            {{ $acc->account_code }}
                        </td>
                        <td class="py-3 px-4">
                            <div class="{{ $isHeader ? 'font-bold text-slate-900' : 'text-slate-700 pl-3' }}">
                                {{ $acc->account_name }}
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            @switch($acc->classification)
                                @case('ASSET')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">ASET</span>
                                    @break
                                @case('LIABILITY')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">KEWAJIBAN</span>
                                    @break
                                @case('EQUITY')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">EKUITAS</span>
                                    @break
                                @case('REVENUE')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">PENDAPATAN</span>
                                    @break
                                @case('EXPENSE')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">BEBAN</span>
                                    @break
                            @endswitch
                        </td>
                        <td class="py-3 px-4">
                            <span class="font-mono text-[11px] font-semibold text-slate-500">
                                {{ $acc->normal_balance }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-slate-500 text-[11px]">
                            {{ $acc->parent ? $acc->parent->account_code . ' - ' . $acc->parent->account_name : '—' }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-slate-800">
                            @if ($bal != 0)
                                Rp {{ number_format($bal, 2, ',', '.') }}
                            @else
                                <span class="text-slate-300 font-normal">Rp 0,00</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if ($acc->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700">Aktif</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-500">Nonaktif</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-10 text-center text-xs text-slate-400">
                            Tidak ada akun ditemukan untuk klasifikasi ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah Akun CoA Baru -->
    <div x-show="openCreateModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full border border-slate-200 shadow-2xl overflow-hidden"
             @click.away="openCreateModal = false">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Tambah Akun Chart of Account</h3>
                        <p class="text-[11px] text-slate-400">Buat rekening pembukuan baru pada sistem akuntansi.</p>
                    </div>
                </div>
                <button type="button" @click="openCreateModal = false" class="text-slate-400 hover:text-slate-600 text-lg leading-none">&times;</button>
            </div>

            <form action="{{ route('finance.accounts.store') }}" method="POST" class="p-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Akun <span class="text-rose-500">*</span></label>
                    <input type="text" name="account_code" required placeholder="Contoh: 1106, 5105"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-hidden font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Akun <span class="text-rose-500">*</span></label>
                    <input type="text" name="account_name" required placeholder="Contoh: Beban Konsumsi Rapat"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Klasifikasi <span class="text-rose-500">*</span></label>
                        <select name="classification" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-hidden bg-white">
                            <option value="ASSET">ASET</option>
                            <option value="LIABILITY">KEWAJIBAN</option>
                            <option value="EQUITY">EKUITAS</option>
                            <option value="REVENUE">PENDAPATAN</option>
                            <option value="EXPENSE" selected>BEBAN</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Saldo Normal <span class="text-rose-500">*</span></label>
                        <select name="normal_balance" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-hidden bg-white">
                            <option value="DEBIT" selected>DEBIT</option>
                            <option value="CREDIT">CREDIT</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Induk Akun (Parent Header)</label>
                    <select name="parent_id" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-hidden bg-white">
                        <option value="">— Tidak Memiliki Induk (Level 1) —</option>
                        @foreach ($parentAccounts as $p)
                            <option value="{{ $p->id }}">{{ $p->account_code }} - {{ $p->account_name }} ({{ $p->classification }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="is_active" id="is_active" value="1" checked class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="is_active" class="text-xs text-slate-700 font-medium">Akun aktif &amp; siap digunakan dalam penjurnalan</label>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="openCreateModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs transition">
                        Simpan Akun
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
