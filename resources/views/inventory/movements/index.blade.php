@extends('layouts.app')

@section('title', 'Kartu Mutasi Stok')
@section('page_title', 'Mutasi Stok Gudang')

@section('breadcrumbs')
    <span class="text-slate-300">/</span>
    <a href="{{ route('inventory.items.index') }}" class="text-slate-500 hover:text-slate-800">Inventory</a>
    <span class="text-slate-300">/</span>
    <span class="text-slate-800 font-semibold">Mutasi Stok</span>
@endsection

@section('content')
<div x-data="{ showModal: false }" class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Kartu Riwayat Mutasi Stok Barang</h1>
            <p class="text-xs text-slate-500 mt-0.5">Audit trail seluruh pergerakan barang (Masuk, Keluar, dan Penyesuaian Fisik) dengan proteksi saldo minus.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="showModal = true"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/20 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Catat Mutasi Stok</span>
            </button>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('inventory.movements.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <select name="item_id" class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-2xs">
                    <option value="">-- Semua Item Barang --</option>
                    @foreach ($items as $it)
                        <option value="{{ $it->id }}" {{ request('item_id') == $it->id ? 'selected' : '' }}>
                            {{ $it->sku }} - {{ $it->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="warehouse_id" class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-2xs">
                    <option value="">-- Semua Gudang --</option>
                    @foreach ($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>
                            {{ $wh->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="movement_type" class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-2xs">
                    <option value="">-- Semua Tipe Mutasi --</option>
                    <option value="IN" {{ request('movement_type') === 'IN' ? 'selected' : '' }}>IN (Barang Masuk)</option>
                    <option value="OUT" {{ request('movement_type') === 'OUT' ? 'selected' : '' }}>OUT (Barang Keluar)</option>
                    <option value="ADJUSTMENT" {{ request('movement_type') === 'ADJUSTMENT' ? 'selected' : '' }}>ADJUSTMENT (Penyesuaian)</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition">
                    Filter
                </button>
                @if (request()->hasAny(['item_id', 'warehouse_id', 'movement_type', 'search']))
                    <a href="{{ route('inventory.movements.index') }}" class="px-3 py-2 rounded-xl text-xs text-slate-500 hover:text-slate-800">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Movements Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Waktu Mutasi</th>
                        <th class="px-4 py-3">Item Barang</th>
                        <th class="px-4 py-3">Lokasi Gudang</th>
                        <th class="px-4 py-3">Tipe Mutasi</th>
                        <th class="px-4 py-3 text-right">Kuantitas</th>
                        <th class="px-4 py-3 text-right">Saldo Sesudah</th>
                        <th class="px-4 py-3">Petugas &amp; Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($movements as $m)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3.5 font-mono text-[11px] text-slate-500">
                                {{ $m->created_at ? $m->created_at->format('d M Y H:i') : '-' }}
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-slate-900">{{ $m->item->name }}</div>
                                <div class="text-[11px] font-mono text-indigo-600">{{ $m->item->sku }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="font-medium text-slate-800">{{ $m->warehouse->name }}</span>
                            </td>
                            <td class="px-4 py-3.5">
                                @if ($m->movement_type === 'IN')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                                        MASUK (IN)
                                    </span>
                                @elseif ($m->movement_type === 'OUT')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg>
                                        KELUAR (OUT)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        ADJUSTMENT
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono font-bold {{ $m->movement_type === 'IN' ? 'text-emerald-600' : ($m->movement_type === 'OUT' ? 'text-rose-600' : 'text-slate-800') }}">
                                {{ $m->movement_type === 'IN' ? '+' : ($m->movement_type === 'OUT' ? '-' : '') }}{{ number_format($m->quantity, 0, ',', '.') }} {{ $m->item->unit }}
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono font-bold text-slate-900">
                                {{ number_format($m->balance_after, 0, ',', '.') }} {{ $m->item->unit }}
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="text-slate-800 font-medium">{{ $m->createdBy->name ?? 'System' }}</div>
                                <div class="text-[11px] text-slate-400 mt-0.5 line-clamp-1">{{ $m->notes ?? '-' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                                <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                                </svg>
                                <p class="text-xs font-medium text-slate-500">Belum ada catatan mutasi stok barang</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($movements->hasPages())
            <div class="p-3 border-t border-slate-200 bg-slate-50">
                {{ $movements->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Form Catat Mutasi -->
    <div x-show="showModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         @keydown.escape.window="showModal = false">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4"
             @click.outside="showModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Catat Mutasi Stok Gudang</h3>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('inventory.movements.store') }}" method="POST" class="space-y-3.5 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Pilih Item Barang <span class="text-rose-500">*</span></label>
                    <select name="item_id" required class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- Pilih Item --</option>
                        @foreach ($items as $it)
                            <option value="{{ $it->id }}">{{ $it->sku }} - {{ $it->name }} ({{ $it->unit }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Pilih Lokasi Gudang <span class="text-rose-500">*</span></label>
                    <select name="warehouse_id" required class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- Pilih Gudang --</option>
                        @foreach ($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tipe Mutasi <span class="text-rose-500">*</span></label>
                        <select name="movement_type" required class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 font-bold">
                            <option value="IN">IN (Barang Masuk)</option>
                            <option value="OUT">OUT (Barang Keluar)</option>
                            <option value="ADJUSTMENT">ADJUSTMENT (Fisik Baru)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Kuantitas <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="quantity" required
                               class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 font-mono font-bold">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Alasan / Catatan Mutasi</label>
                    <textarea name="notes" rows="2" placeholder="Contoh: Pengambilan untuk operasional divisi atau hasil stock opname fisik..."
                              class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100">
                    <button type="button" @click="showModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-600/20">
                        Catat Mutasi Stok
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
