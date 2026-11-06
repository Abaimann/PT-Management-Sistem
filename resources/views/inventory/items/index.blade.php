@extends('layouts.app')

@section('title', 'Katalog Barang & Safety Stock')
@section('page_title', 'Master Data Barang')

@section('breadcrumbs')
    <span class="text-slate-300">/</span>
    <span class="text-slate-500">Inventory</span>
    <span class="text-slate-300">/</span>
    <span class="text-slate-800 font-semibold">Master Barang</span>
@endsection

@section('content')
<div x-data="{ showModal: false }" class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Katalog Master Barang &amp; Safety Stock</h1>
            <p class="text-xs text-slate-500 mt-0.5">Pemantauan nomor SKU, barcode, unit satuan, ambang batas stok minimum, dan saldo real-time gudang.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('inventory.warehouses.index') }}"
               class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 text-xs font-semibold shadow-2xs transition">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                </svg>
                <span>Kelola Gudang</span>
            </a>
            <button @click="showModal = true"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/20 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Tambah Item Baru</span>
            </button>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('inventory.items.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari SKU, barcode, nama barang..."
                       class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-2xs">
            </div>
            <div>
                <select name="category" class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-2xs">
                    <option value="">-- Semua Kategori --</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center">
                <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer">
                    <input type="checkbox" name="low_stock" value="1" {{ request('low_stock') ? 'checked' : '' }}
                           class="rounded-md border-slate-300 text-rose-600 focus:ring-rose-500">
                    <span class="text-rose-600">Hanya Stok Kritis (&le; Min)</span>
                </label>
            </div>
            <div class="flex items-center gap-2 justify-end">
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition">
                    Filter
                </button>
                @if (request()->hasAny(['search', 'category', 'low_stock']))
                    <a href="{{ route('inventory.items.index') }}" class="px-3 py-2 rounded-xl text-xs text-slate-500 hover:text-slate-800">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Items Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">SKU &amp; Barcode</th>
                        <th class="px-4 py-3">Nama Barang &amp; Kategori</th>
                        <th class="px-4 py-3">Satuan</th>
                        <th class="px-4 py-3">Stok Minimum</th>
                        <th class="px-4 py-3">Stok Saat Ini</th>
                        <th class="px-4 py-3">Status Persediaan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($items as $item)
                        <tr class="hover:bg-slate-50/70 transition {{ $item->is_low_stock ? 'bg-rose-50/20' : '' }}">
                            <td class="px-4 py-3.5">
                                <div class="font-bold font-mono text-indigo-600">{{ $item->sku }}</div>
                                @if ($item->barcode)
                                    <div class="text-[10px] font-mono text-slate-400 mt-0.5">Bar: {{ $item->barcode }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-slate-900">{{ $item->name }}</div>
                                <div class="text-[11px] text-slate-400">{{ $item->category ?? 'Umum' }}</div>
                            </td>
                            <td class="px-4 py-3.5 font-bold uppercase text-slate-700">
                                {{ $item->unit }}
                            </td>
                            <td class="px-4 py-3.5 font-mono text-slate-600">
                                {{ number_format($item->minimum_stock, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 font-bold font-mono text-base {{ $item->is_low_stock ? 'text-rose-600' : 'text-slate-900' }}">
                                {{ number_format($item->current_stock, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5">
                                @if ($item->current_stock <= 0)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-600 animate-pulse"></span> Habis (Out of Stock)
                                    </span>
                                @elseif ($item->is_low_stock)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Stok Kritis (&le; Min)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aman (Optimal)
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-slate-400">
                                <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                                </svg>
                                <p class="text-xs font-medium text-slate-500">Belum ada item barang di katalog gudang</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($items->hasPages())
            <div class="p-3 border-t border-slate-200 bg-slate-50">
                {{ $items->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Form Tambah Item -->
    <div x-show="showModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         @keydown.escape.window="showModal = false">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-4"
             @click.outside="showModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Registrasi Item Barang Baru</h3>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('inventory.items.store') }}" method="POST" class="space-y-3.5 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Kode SKU <span class="text-rose-500">*</span></label>
                        <input type="text" name="sku" required placeholder="SKU-ATK-001"
                               class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Barcode Scanner</label>
                        <input type="text" name="barcode" placeholder="899123456789"
                               class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 font-mono">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nama Barang <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="Kertas HVS A4 80gr Sinar Dunia"
                           class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Kategori</label>
                        <input type="text" name="category" placeholder="ATK / IT / Elektronik"
                               class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Satuan <span class="text-rose-500">*</span></label>
                        <input type="text" name="unit" required placeholder="RIM / PCS / BOX"
                               class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 uppercase">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Stok Min <span class="text-rose-500">*</span></label>
                        <input type="number" step="1" min="0" name="minimum_stock" value="5" required
                               class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 font-mono">
                    </div>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100">
                    <button type="button" @click="showModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-600/20">
                        Simpan Item
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
