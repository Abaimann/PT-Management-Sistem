@extends('layouts.app')

@section('title', 'Terbitkan Purchase Order')
@section('page_title', 'Purchase Order Baru')

@section('breadcrumbs')
    <span class="text-slate-300">/</span>
    <a href="{{ route('procurement.orders.index') }}" class="text-slate-500 hover:text-slate-800">Purchase Orders</a>
    <span class="text-slate-300">/</span>
    <span class="text-slate-800 font-semibold">Terbitkan Baru</span>
@endsection

@section('content')
<div class="max-w-5xl mx-auto space-y-6"
     x-data="{
        taxRate: 11,
        items: {{ json_encode($selectedPr ? $selectedPr->items->map(fn($i) => [
            'item_id' => $i->item_id ?? '',
            'item_name' => $i->item_name,
            'quantity' => (float)$i->quantity,
            'unit' => $i->unit,
            'unit_price' => (float)$i->estimated_price,
        ]) : [
            ['item_id' => '', 'item_name' => '', 'quantity' => 1, 'unit' => 'PCS', 'unit_price' => 0]
        ]) }},
        addItem() {
            this.items.push({ item_id: '', item_name: '', quantity: 1, unit: 'PCS', unit_price: 0 });
        },
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },
        calculateSubtotal() {
            return this.items.reduce((sum, item) => sum + (parseFloat(item.quantity || 0) * parseFloat(item.unit_price || 0)), 0);
        },
        calculateTax() {
            return this.calculateSubtotal() * (parseFloat(this.taxRate || 0) / 100);
        },
        calculateGrandTotal() {
            return this.calculateSubtotal() + this.calculateTax();
        },
        onCatalogSelect(index, event) {
            const select = event.target;
            const selectedOption = select.options[select.selectedIndex];
            if (selectedOption && selectedOption.value) {
                this.items[index].item_name = selectedOption.dataset.name || '';
                this.items[index].unit = selectedOption.dataset.unit || 'PCS';
            }
        }
     }">

    <div class="pb-2 border-b border-slate-200">
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Penerbitan Surat Purchase Order (PO)</h1>
        <p class="text-xs text-slate-500 mt-0.5">Terbitkan pesanan pembelian resmi kepada vendor rekanan terpilih dengan rincian harga pasti dan jadwal serah terima.</p>
    </div>

    <form action="{{ route('procurement.orders.store') }}" method="POST" class="space-y-6">
        @csrf

        @if ($selectedPr)
            <input type="hidden" name="purchase_request_id" value="{{ $selectedPr->id }}">
            <div class="p-4 bg-indigo-50 border border-indigo-200 rounded-2xl flex items-center justify-between text-xs text-indigo-900">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Konversi dari Purchase Request: <strong class="font-mono">{{ $selectedPr->pr_number }}</strong> ({{ $selectedPr->department->name ?? 'Departemen' }})</span>
                </div>
                <a href="{{ route('procurement.orders.create') }}" class="font-semibold text-indigo-700 hover:underline">Lepas Referensi PR</a>
            </div>
        @elseif ($approvedPrs->isNotEmpty())
            <div class="bg-amber-50/60 p-4 rounded-2xl border border-amber-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-amber-900">
                <span>Terdapat <strong>{{ $approvedPrs->count() }}</strong> PR berstatus disetujui siap diterbitkan ke PO:</span>
                <div class="flex items-center gap-2">
                    <select onchange="if(this.value) window.location.href='{{ route('procurement.orders.create') }}?pr_id=' + this.value"
                            class="text-xs rounded-xl border-amber-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white">
                        <option value="">-- Pilih PR untuk Dikonversi --</option>
                        @foreach ($approvedPrs as $apr)
                            <option value="{{ $apr->id }}">{{ $apr->pr_number }} ({{ $apr->department->name ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif

        <!-- General PO Info -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Identitas Pesanan &amp; Vendor</h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Rekanan Vendor <span class="text-rose-500">*</span></label>
                    <select name="vendor_id" required class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- Pilih Rekanan Vendor --</option>
                        @foreach ($vendors as $v)
                            <option value="{{ $v->id }}">{{ $v->name }} ({{ $v->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Terbit PO <span class="text-rose-500">*</span></label>
                    <input type="date" name="po_date" value="{{ old('po_date', now()->toDateString()) }}" required
                           class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Estimasi Pengiriman (Delivery Date)</label>
                    <input type="date" name="delivery_date" value="{{ old('delivery_date', now()->addDays(5)->toDateString()) }}"
                           class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan &amp; Syarat Pembayaran (TOP)</label>
                <textarea name="notes" rows="2" placeholder="Contoh: Pembayaran tempo 30 hari kalender setelah faktur pajak dan BAST diterima lengkap..."
                          class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
            </div>
        </div>

        <!-- Dynamic Items Card -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div>
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Rincian Barang yang Dipesan</h2>
                    <p class="text-[11px] text-slate-400">Pastikan kuantitas dan harga satuan sesuai kesepakatan final vendor.</p>
                </div>
                <button type="button" @click="addItem()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Tambah Baris</span>
                </button>
            </div>

            <div class="space-y-3">
                <template x-for="(item, index) in items" :key="index">
                    <div class="p-3.5 bg-slate-50/70 rounded-2xl border border-slate-200/60 grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                        <div class="sm:col-span-3">
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Item Gudang</label>
                            <select :name="`items[${index}][item_id]`" x-model="item.item_id" @change="onCatalogSelect(index, $event)"
                                    class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white">
                                <option value="">-- Item Bebas --</option>
                                @foreach ($items as $catalogItem)
                                    <option value="{{ $catalogItem->id }}" data-name="{{ $catalogItem->name }}" data-unit="{{ $catalogItem->unit }}">
                                        {{ $catalogItem->sku }} - {{ $catalogItem->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="sm:col-span-3">
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Nama Item <span class="text-rose-500">*</span></label>
                            <input type="text" :name="`items[${index}][item_name]`" x-model="item.item_name" required placeholder="Nama item"
                                   class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Kuantitas <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" min="0.01" :name="`items[${index}][quantity]`" x-model="item.quantity" required
                                   class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white">
                        </div>

                        <div class="sm:col-span-1">
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Satuan</label>
                            <input type="text" :name="`items[${index}][unit]`" x-model="item.unit" required placeholder="PCS"
                                   class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white uppercase">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Harga Satuan (Rp)</label>
                            <input type="number" step="100" min="0" :name="`items[${index}][unit_price]`" x-model="item.unit_price" required
                                   class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white">
                        </div>

                        <div class="sm:col-span-1 flex items-center justify-center">
                            <button type="button" @click="removeItem(index)" :disabled="items.length <= 1"
                                    class="p-2 rounded-xl text-rose-500 hover:bg-rose-100 disabled:opacity-30 disabled:pointer-events-none transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Tax and Grand Total Summary -->
            <div class="pt-4 border-t border-slate-200 space-y-2">
                <div class="flex items-center justify-between text-xs text-slate-600">
                    <span>Subtotal Barang:</span>
                    <span class="font-mono font-bold text-slate-800">
                        Rp <span x-text="new Intl.NumberFormat('id-ID').format(calculateSubtotal())"></span>
                    </span>
                </div>

                <div class="flex items-center justify-between text-xs text-slate-600">
                    <div class="flex items-center gap-2">
                        <span>Pajak Pertambahan Nilai (PPN):</span>
                        <input type="number" step="1" min="0" max="100" name="tax_percent" x-model="taxRate"
                               class="w-16 py-0.5 px-2 text-xs rounded-lg border-slate-300 text-center font-mono">
                        <span>%</span>
                    </div>
                    <span class="font-mono text-slate-700">
                        Rp <span x-text="new Intl.NumberFormat('id-ID').format(calculateTax())"></span>
                    </span>
                </div>

                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-base">
                    <span class="font-bold text-slate-900">Total Nilai Purchase Order:</span>
                    <span class="font-extrabold font-mono text-indigo-600">
                        Rp <span x-text="new Intl.NumberFormat('id-ID').format(calculateGrandTotal())"></span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Buttons -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('procurement.orders.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/20 transition">
                Terbitkan Purchase Order
            </button>
        </div>
    </form>

</div>
@endsection
