@extends('layouts.app')

@section('title', 'Register Aset Tetap & BAST')
@section('page_title', 'Aset Tetap & Inventaris')

@section('breadcrumbs')
    <span class="text-slate-300">/</span>
    <span class="text-slate-500">Inventory</span>
    <span class="text-slate-300">/</span>
    <span class="text-slate-800 font-semibold">Aset Tetap &amp; BAST</span>
@endsection

@section('content')
<div x-data="{
        showCreateModal: false,
        showAssignModal: false,
        showReturnModal: false,
        selectedAsset: null,
        selectedAssignment: null,
        openAssign(asset) {
            this.selectedAsset = asset;
            this.showAssignModal = true;
        },
        openReturn(assignment) {
            this.selectedAssignment = assignment;
            this.showReturnModal = true;
        }
     }" class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Register Aset Tetap Perusahaan &amp; BAST</h1>
            <p class="text-xs text-slate-500 mt-0.5">Pendataan inventaris modal perusahaan, harga perolehan, nomor barcode, peminjaman BAST karyawan, dan clearance pengembalian.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="showCreateModal = true"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/20 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Registrasi Aset Baru</span>
            </button>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('inventory.assets.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari kode aset, barcode, serial, nama..."
                       class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-2xs">
            </div>
            <div>
                <select name="status" class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-2xs">
                    <option value="">-- Semua Status Ketersediaan --</option>
                    <option value="AVAILABLE" {{ request('status') === 'AVAILABLE' ? 'selected' : '' }}>AVAILABLE (Tersedia)</option>
                    <option value="ASSIGNED" {{ request('status') === 'ASSIGNED' ? 'selected' : '' }}>ASSIGNED (Dipinjam Karyawan)</option>
                    <option value="IN_MAINTENANCE" {{ request('status') === 'IN_MAINTENANCE' ? 'selected' : '' }}>IN MAINTENANCE (Perbaikan)</option>
                    <option value="DISPOSED" {{ request('status') === 'DISPOSED' ? 'selected' : '' }}>DISPOSED (Afkir)</option>
                </select>
            </div>
            <div>
                <select name="condition" class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-2xs">
                    <option value="">-- Semua Kondisi Fisik --</option>
                    <option value="GOOD" {{ request('condition') === 'GOOD' ? 'selected' : '' }}>Kondisi Baik (GOOD)</option>
                    <option value="SLIGHTLY_DAMAGED" {{ request('condition') === 'SLIGHTLY_DAMAGED' ? 'selected' : '' }}>Rusak Ringan</option>
                    <option value="BROKEN" {{ request('condition') === 'BROKEN' ? 'selected' : '' }}>Rusak Berat</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition">
                    Filter
                </button>
                @if (request()->hasAny(['search', 'status', 'condition']))
                    <a href="{{ route('inventory.assets.index') }}" class="px-3 py-2 rounded-xl text-xs text-slate-500 hover:text-slate-800">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Assets Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Kode Aset &amp; Barcode</th>
                        <th class="px-4 py-3">Nama Aset &amp; Serial</th>
                        <th class="px-4 py-3">Tgl &amp; Nilai Perolehan</th>
                        <th class="px-4 py-3">Nilai Buku</th>
                        <th class="px-4 py-3">Kondisi Fisik</th>
                        <th class="px-4 py-3">Status &amp; Pemegang BAST</th>
                        <th class="px-4 py-3 text-right">Aksi BAST</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($assets as $asset)
                        @php
                            $activeAssignment = $asset->assignments->firstWhere('status', 'ACTIVE');
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3.5">
                                <div class="font-bold font-mono text-indigo-600">{{ $asset->asset_code }}</div>
                                @if ($asset->barcode)
                                    <div class="text-[10px] font-mono text-slate-400">Bar: {{ $asset->barcode }}</div>
                                @endif
                                <div class="text-[10px] text-slate-500">{{ $asset->location ?? 'Head Office' }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-slate-900">{{ $asset->name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $asset->category ?? 'Peralatan' }}</div>
                                @if ($asset->serial_number)
                                    <div class="text-[10px] font-mono text-slate-400">SN: {{ $asset->serial_number }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 font-mono">
                                <div class="text-slate-900 font-semibold">Rp {{ number_format($asset->acquisition_cost, 0, ',', '.') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $asset->acquisition_date ? $asset->acquisition_date->format('d M Y') : '-' }}</div>
                            </td>
                            <td class="px-4 py-3.5 font-mono font-bold text-slate-800">
                                Rp {{ number_format($asset->book_value, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5">
                                @if ($asset->condition === 'GOOD')
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Baik (GOOD)
                                    </span>
                                @elseif ($asset->condition === 'SLIGHTLY_DAMAGED')
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-amber-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Rusak Ringan
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-rose-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Rusak Berat
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                @if ($asset->status === 'AVAILABLE')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Tersedia
                                    </span>
                                @elseif ($asset->status === 'ASSIGNED')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Dipinjam
                                    </span>
                                    @if ($activeAssignment)
                                        <div class="text-[11px] font-semibold text-slate-800 mt-1">
                                            {{ $activeAssignment->employee->full_name ?? ($activeAssignment->employee->user->name ?? '-') }}
                                        </div>
                                        <div class="text-[10px] font-mono text-slate-400">BAST: {{ $activeAssignment->handover_document_path }}</div>
                                    @endif
                                @elseif ($asset->status === 'IN_MAINTENANCE')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Maintenance
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                        Afkir (Disposed)
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right space-x-1">
                                @if ($asset->status === 'AVAILABLE')
                                    <button type="button" @click="openAssign({{ json_encode($asset) }})"
                                            class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-semibold transition">
                                        BAST Serah Terima
                                    </button>
                                @elseif ($asset->status === 'ASSIGNED' && $activeAssignment)
                                    <button type="button" @click="openReturn({{ json_encode($activeAssignment) }})"
                                            class="px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-[11px] font-semibold transition">
                                        Pengembalian
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                                <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" />
                                </svg>
                                <p class="text-xs font-medium text-slate-500">Belum ada aset tetap yang didaftarkan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($assets->hasPages())
            <div class="p-3 border-t border-slate-200 bg-slate-50">
                {{ $assets->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Registrasi Aset Baru -->
    <div x-show="showCreateModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         @keydown.escape.window="showCreateModal = false">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-4 max-h-[90vh] overflow-y-auto"
             @click.outside="showCreateModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Registrasi Aset Tetap Baru</h3>
                <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('inventory.assets.store') }}" method="POST" class="space-y-3.5 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Kode Aset <span class="text-rose-500">*</span></label>
                        <input type="text" name="asset_code" required placeholder="AST-IT-001"
                               class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Barcode Label</label>
                        <input type="text" name="barcode" placeholder="BAR-899123"
                               class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nama Aset <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required placeholder="MacBook Pro M3 Max 32GB"
                               class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Kategori Aset</label>
                        <input type="text" name="category" placeholder="Elektronik / Laptop / Furnitur"
                               class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Serial Number (SN)</label>
                        <input type="text" name="serial_number" placeholder="C02G1234MD6R"
                               class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tanggal Perolehan <span class="text-rose-500">*</span></label>
                        <input type="date" name="acquisition_date" value="{{ date('Y-m-d') }}" required
                               class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Harga Perolehan (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" step="1000" min="0" name="acquisition_cost" required placeholder="35000000"
                               class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nilai Buku Saat Ini (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" step="1000" min="0" name="book_value" required placeholder="35000000"
                               class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Kondisi Aset <span class="text-rose-500">*</span></label>
                        <select name="condition" required class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="GOOD">Kondisi Baik (GOOD)</option>
                            <option value="SLIGHTLY_DAMAGED">Rusak Ringan</option>
                            <option value="BROKEN">Rusak Berat</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Lokasi Penyimpanan</label>
                        <input type="text" name="location" placeholder="Ruang Server Lt. 2"
                               class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-600/20">
                        Simpan Aset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal BAST Serah Terima Pinjaman Aset -->
    <div x-show="showAssignModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         @keydown.escape.window="showAssignModal = false">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4"
             @click.outside="showAssignModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Penerbitan BAST Serah Terima</h3>
                    <p class="text-[11px] text-slate-500" x-text="selectedAsset ? `${selectedAsset.asset_code} - ${selectedAsset.name}` : ''"></p>
                </div>
                <button @click="showAssignModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form :action="selectedAsset ? `/inventory/assets/${selectedAsset.id}/assign` : '#'" method="POST" class="space-y-3.5 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Penerima Aset (Karyawan) <span class="text-rose-500">*</span></label>
                    <select name="employee_id" required class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->nik }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Tanggal Serah Terima BAST <span class="text-rose-500">*</span></label>
                    <input type="date" name="assignment_date" value="{{ date('Y-m-d') }}" required
                           class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kondisi Saat Penyerahan</label>
                    <input type="text" name="condition_on_assignment" value="GOOD"
                           class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Catatan Kelengkapan (Charger, Tas, Aksesoris)</label>
                    <textarea name="notes" rows="2" placeholder="Termasuk charger 96W, kabel USB-C magsafe, dan mouse..."
                              class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100">
                    <button type="button" @click="showAssignModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-600/20">
                        Terbitkan BAST Pinjaman
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Pengembalian Aset & Clearance -->
    <div x-show="showReturnModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         @keydown.escape.window="showReturnModal = false">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4"
             @click.outside="showReturnModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Pengembalian Aset &amp; Clearance</h3>
                    <p class="text-[11px] text-slate-500" x-text="selectedAssignment ? `Ref BAST: ${selectedAssignment.handover_document_path}` : ''"></p>
                </div>
                <button @click="showReturnModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form :action="selectedAssignment ? `/inventory/assignments/${selectedAssignment.id}/return` : '#'" method="POST" class="space-y-3.5 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Tanggal Pengembalian Fisik <span class="text-rose-500">*</span></label>
                    <input type="date" name="return_date" value="{{ date('Y-m-d') }}" required
                           class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kondisi Fisik Saat Dikembalikan <span class="text-rose-500">*</span></label>
                    <select name="condition_on_return" required class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="GOOD">Kondisi Baik Lengkap (GOOD)</option>
                        <option value="SLIGHTLY_DAMAGED">Terdapat Kerusakan Ringan / Baret</option>
                        <option value="BROKEN">Rusak Berat (Perlu Servis)</option>
                        <option value="DISPOSED">Hilang / Tidak Dapat Digunakan</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Catatan Pemeriksaan GA</label>
                    <textarea name="notes" rows="2" placeholder="Kondisi fungsi normal, kelengkapan unit lengkap..."
                              class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100">
                    <button type="button" @click="showReturnModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-md shadow-emerald-600/20">
                        Konfirmasi Clearance Aset
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
