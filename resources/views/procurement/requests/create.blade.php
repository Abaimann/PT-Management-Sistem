@extends('layouts.app')

@section('title', 'Buat Purchase Request')
@section('page_title', 'Purchase Request Baru')

@section('breadcrumbs')
    <span class="text-slate-300">/</span>
    <a href="{{ route('procurement.requests.index') }}" class="text-slate-500 hover:text-slate-800">Purchase Requests</a>
    <span class="text-slate-300">/</span>
    <span class="text-slate-800 font-semibold">Buat Baru</span>
@endsection

@section('content')
<div class="max-w-5xl mx-auto space-y-6"
     x-data="{
        items: [
            { item_id: '', item_name: '', quantity: 1, unit: 'PCS', estimated_price: 0 }
        ],
        addItem() {
            this.items.push({ item_id: '', item_name: '', quantity: 1, unit: 'PCS', estimated_price: 0 });
        },
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },
        calculateTotal() {
            return this.items.reduce((sum, item) => sum + (parseFloat(item.quantity || 0) * parseFloat(item.estimated_price || 0)), 0);
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
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Formulir Pengajuan Purchase Request (PR)</h1>
        <p class="text-xs text-slate-500 mt-0.5">Lengkapi informasi kebutuhan barang, estimasi biaya, dan jadwal pemenuhan.</p>
    </div>

    <form action="{{ route('procurement.requests.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- General Info Card -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Informasi Kebutuhan</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Departemen Pemohon <span class="text-rose-500">*</span></label>
                    <select name="department_id" required class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- Pilih Departemen --</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Kebutuhan (Required Date)</label>
                    <input type="date" name="required_date" value="{{ old('required_date', now()->addDays(7)->toDateString()) }}"
                           class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan &amp; Keperluan Pengadaan <span class="text-rose-500">*</span></label>
                <textarea name="reason" rows="2" required placeholder="Contoh: Pengadaan monitor dan perlengkapan workstation untuk tim engineering divisi frontend..."
                          class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('reason') }}</textarea>
            </div>
        </div>

        <!-- Dynamic Items Card -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div>
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Daftar Item Barang</h2>
                    <p class="text-[11px] text-slate-400">Pilih dari katalog gudang atau ketik nama item secara langsung.</p>
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
                        <!-- Catalog Select -->
                        <div class="sm:col-span-3">
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Item Katalog</label>
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

                        <!-- Item Name -->
                        <div class="sm:col-span-3">
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Nama Barang <span class="text-rose-500">*</span></label>
                            <input type="text" :name="`items[${index}][item_name]`" x-model="item.item_name" required placeholder="Nama item"
                                   class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white">
                        </div>

                        <!-- Quantity -->
                        <div class="sm:col-span-2">
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Kuantitas <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" min="0.01" :name="`items[${index}][quantity]`" x-model="item.quantity" required
                                   class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white">
                        </div>

                        <!-- Unit -->
                        <div class="sm:col-span-1">
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Satuan</label>
                            <input type="text" :name="`items[${index}][unit]`" x-model="item.unit" required placeholder="PCS"
                                   class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white uppercase">
                        </div>

                        <!-- Estimated Price -->
                        <div class="sm:col-span-2">
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Estimasi Harga</label>
                            <input type="number" step="100" min="0" :name="`items[${index}][estimated_price]`" x-model="item.estimated_price" required
                                   class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white">
                        </div>

                        <!-- Action Delete -->
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

            <!-- Total Bar -->
            <div class="pt-4 border-t border-slate-200 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Total Estimasi Anggaran:</span>
                <span class="text-base font-extrabold font-mono text-indigo-600">
                    Rp <span x-text="new Intl.NumberFormat('id-ID').format(calculateTotal())"></span>
                </span>
            </div>
        </div>

        <!-- Buttons -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('procurement.requests.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/20 transition">
                Kirim Pengajuan PR
            </button>
        </div>
    </form>

</div>
@endsection
