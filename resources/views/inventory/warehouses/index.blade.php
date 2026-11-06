@extends('layouts.app')

@section('title', 'Master Gudang & Lokasi')
@section('page_title', 'Master Gudang')

@section('breadcrumbs')
    <span class="text-slate-300">/</span>
    <a href="{{ route('inventory.items.index') }}" class="text-slate-500 hover:text-slate-800">Inventory</a>
    <span class="text-slate-300">/</span>
    <span class="text-slate-800 font-semibold">Gudang &amp; Lokasi</span>
@endsection

@section('content')
<div x-data="{ showModal: false }" class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Master Gudang &amp; Lokasi Penyimpanan</h1>
            <p class="text-xs text-slate-500 mt-0.5">Pengelolaan titik penyimpanan fisik barang, penanggung jawab (PIC), dan inventarisasi per gudang.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="showModal = true"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/20 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Tambah Lokasi Gudang</span>
            </button>
        </div>
    </div>

    <!-- Warehouse Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Kode &amp; Nama Gudang</th>
                        <th class="px-4 py-3">Lokasi / Alamat Fisik</th>
                        <th class="px-4 py-3">PIC Penanggung Jawab</th>
                        <th class="px-4 py-3">Total SKU Terdaftar</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($warehouses as $wh)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-slate-900">{{ $wh->name }}</div>
                                <div class="text-[11px] font-mono text-indigo-600">{{ $wh->code }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="text-slate-600">{{ $wh->location ?? 'Head Office' }}</span>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-slate-800">{{ $wh->pic->full_name ?? ($wh->pic->user->name ?? 'Belum Ditunjuk') }}</div>
                                @if ($wh->pic)
                                    <div class="text-[10px] text-slate-400">NIK: {{ $wh->pic->nik }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 font-bold font-mono text-slate-800">
                                {{ $wh->stock_balances_count }} SKU
                            </td>
                            <td class="px-4 py-3.5">
                                @if ($wh->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Nonaktif
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-slate-400">
                                <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                                </svg>
                                <p class="text-xs font-medium text-slate-500">Belum ada gudang penyimpanan terdaftar</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($warehouses->hasPages())
            <div class="p-3 border-t border-slate-200 bg-slate-50">
                {{ $warehouses->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Form Tambah Gudang -->
    <div x-show="showModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         @keydown.escape.window="showModal = false">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4"
             @click.outside="showModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Tambah Lokasi Gudang Baru</h3>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('inventory.warehouses.store') }}" method="POST" class="space-y-3.5 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kode Gudang <span class="text-rose-500">*</span></label>
                    <input type="text" name="code" required placeholder="WH-MAIN"
                           class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 font-mono">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nama Gudang <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="Gudang Utama Logistik"
                           class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Lokasi / Lantai / Ruangan</label>
                    <textarea name="location" rows="2" placeholder="Gedung A Lantai 1 Ruang Logistik"
                              class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">PIC Penanggung Jawab</label>
                    <select name="pic_employee_id" class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- Pilih Karyawan PIC --</option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->nik }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100">
                    <button type="button" @click="showModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-600/20">
                        Simpan Gudang
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
