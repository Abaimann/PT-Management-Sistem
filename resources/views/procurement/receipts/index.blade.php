@extends('layouts.app')

@section('title', 'Penerimaan Barang (Goods Receipts)')
@section('page_title', 'Goods Receipts')

@section('breadcrumbs')
    <span class="text-slate-300">/</span>
    <span class="text-slate-500">Procurement</span>
    <span class="text-slate-300">/</span>
    <span class="text-slate-800 font-semibold">Penerimaan Barang</span>
@endsection

@section('content')
<div x-data="{
        showModal: false,
        selectedPoId: '',
        openOrders: {{ json_encode($openOrders->map(fn($o) => [
            'id' => $o->id,
            'po_number' => $o->po_number,
            'vendor_name' => $o->vendor->name,
            'items' => $o->items->map(fn($i) => [
                'id' => $i->id,
                'item_name' => $i->item_name,
                'ordered' => (float)$i->quantity,
                'received' => (float)$i->received_quantity,
                'remaining' => max(0, (float)$i->quantity - (float)$i->received_quantity),
                'unit' => $i->unit,
            ])
        ])) }},
        currentPo: null,
        selectPo(id) {
            this.selectedPoId = id;
            this.currentPo = this.openOrders.find(o => o.id == id) || null;
        }
     }" class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Catatan Penerimaan Barang (Goods Receipt)</h1>
            <p class="text-xs text-slate-500 mt-0.5">Penerimaan fisik barang di gudang dengan validasi Three-Way Matching (PO vs Surat Jalan vs Fisik Gudang).</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="showModal = true"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/20 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Catat Penerimaan Barang</span>
            </button>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('procurement.receipts.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="sm:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nomor GR, nomor PO, nomor surat jalan (DO)..."
                       class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-2xs">
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition">
                    Filter
                </button>
                @if (request('search'))
                    <a href="{{ route('procurement.receipts.index') }}" class="px-3 py-2 rounded-xl text-xs text-slate-500 hover:text-slate-800">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Receipts Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">No. GR &amp; Tanggal</th>
                        <th class="px-4 py-3">Ref Purchase Order (PO)</th>
                        <th class="px-4 py-3">No. Surat Jalan (DO)</th>
                        <th class="px-4 py-3">Lokasi Gudang</th>
                        <th class="px-4 py-3">Petugas Gudang (PIC)</th>
                        <th class="px-4 py-3">Status 3-Way Match</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($receipts as $receipt)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3.5">
                                <div class="font-bold font-mono text-indigo-600">{{ $receipt->gr_number }}</div>
                                <div class="text-[11px] text-slate-500">{{ $receipt->receipt_date ? $receipt->receipt_date->format('d M Y') : '-' }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-slate-900">{{ $receipt->purchaseOrder->po_number }}</div>
                                <div class="text-[11px] text-slate-500">{{ $receipt->purchaseOrder->vendor->name }}</div>
                            </td>
                            <td class="px-4 py-3.5 font-mono text-slate-700 font-medium">
                                {{ $receipt->delivery_order_number ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-medium text-slate-800">{{ $receipt->warehouse->name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $receipt->warehouse->code }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-medium text-slate-800">{{ $receipt->receivedBy->full_name ?? ($receipt->receivedBy->user->name ?? '-') }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Verified &amp; Confirmed
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-slate-400">
                                <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.948c0-.621-.504-1.125-1.125-1.125H4.125C3.504 5.5 3 6.004 3 6.625v10.125c0 .621.504 1.125 1.125 1.125h.375m12 0h-9" />
                                </svg>
                                <p class="text-xs font-medium text-slate-500">Belum ada catatan penerimaan barang</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($receipts->hasPages())
            <div class="p-3 border-t border-slate-200 bg-slate-50">
                {{ $receipts->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Catat Penerimaan Barang (Three-Way Matching) -->
    <div x-show="showModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         @keydown.escape.window="showModal = false">
        <div class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-slate-100 space-y-4 max-h-[90vh] overflow-y-auto"
             @click.outside="showModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Verifikasi &amp; Penerimaan Barang Fisik</h3>
                    <p class="text-[11px] text-slate-500">Three-Way Matching: validasi kuantitas fisik tiba terhadap Purchase Order aktif.</p>
                </div>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Form -->
            <form :action="currentPo ? `/procurement/orders/${currentPo.id}/receipts` : '#'" method="POST" class="space-y-4 text-xs">
                @csrf

                <!-- Select PO -->
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Pilih Purchase Order yang Tiba <span class="text-rose-500">*</span></label>
                    <select @change="selectPo($event.target.value)" required
                            class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 font-mono">
                        <option value="">-- Pilih Purchase Order Aktif --</option>
                        <template x-for="po in openOrders" :key="po.id">
                            <option :value="po.id" x-text="`${po.po_number} - ${po.vendor_name}`"></option>
                        </template>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Gudang Penerima <span class="text-rose-500">*</span></label>
                        <select name="warehouse_id" required class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- Pilih Lokasi Gudang --</option>
                            @foreach ($warehouses as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tanggal Penerimaan <span class="text-rose-500">*</span></label>
                        <input type="date" name="receipt_date" value="{{ date('Y-m-d') }}" required
                               class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nomor Surat Jalan Vendor (Delivery Order)</label>
                    <input type="text" name="delivery_order_number" placeholder="DO-12345/SJ/2026"
                           class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 font-mono">
                </div>

                <!-- PO Items Three-Way Match Checklist -->
                <div x-show="currentPo" class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-200/60">
                        <span class="font-bold text-slate-800 uppercase tracking-wider text-[11px]">Validasi Kuantitas Fisik Barang</span>
                        <span class="text-[11px] text-indigo-600 font-semibold" x-text="currentPo ? currentPo.po_number : ''"></span>
                    </div>

                    <div class="space-y-2.5">
                        <template x-for="(item, idx) in (currentPo ? currentPo.items : [])" :key="item.id">
                            <div class="p-3 bg-white rounded-xl border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div class="flex-1">
                                    <div class="font-semibold text-slate-900" x-text="item.item_name"></div>
                                    <div class="text-[10px] text-slate-500">
                                        Total PO: <strong x-text="item.ordered"></strong> <span x-text="item.unit"></span>
                                        | Diterima sebelumnya: <span x-text="item.received"></span>
                                        | <span class="text-amber-600 font-bold">Sisa: <span x-text="item.remaining"></span></span>
                                    </div>
                                    <input type="hidden" :name="`items[${idx}][po_item_id]`" :value="item.id">
                                </div>
                                <div class="flex items-center gap-2">
                                    <label class="text-[11px] text-slate-600 font-medium whitespace-nowrap">Jml Terima:</label>
                                    <input type="number" step="0.01" min="0" :max="item.remaining"
                                           :name="`items[${idx}][received_qty]`"
                                           :value="item.remaining"
                                           class="w-24 text-xs font-mono font-bold rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    <span class="text-[11px] font-semibold text-slate-500" x-text="item.unit"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Catatan Pemeriksaan Fisik (Kondisi Kemasan / Segel)</label>
                    <textarea name="notes" rows="2" placeholder="Barang diterima dalam kondisi utuh dan segel baik..."
                              class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100">
                    <button type="button" @click="showModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit" :disabled="!currentPo"
                            class="px-5 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:pointer-events-none shadow-md shadow-indigo-600/20">
                        Konfirmasi &amp; Update Stok Gudang
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
