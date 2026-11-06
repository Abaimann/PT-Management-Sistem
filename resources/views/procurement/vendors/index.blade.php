@extends('layouts.app')

@section('title', 'Direktori Vendor Rekanan')
@section('page_title', 'Vendor Rekanan')

@section('breadcrumbs')
    <span class="text-slate-300">/</span>
    <span class="text-slate-500">Procurement</span>
    <span class="text-slate-300">/</span>
    <span class="text-slate-800 font-semibold">Vendor</span>
@endsection

@section('content')
<div x-data="{ showModal: false }" class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Database Rekanan Vendor</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola data mitra pengadaan, legalitas NPWP, kontak representatif, dan detail rekening bank.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="showModal = true"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/20 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Daftarkan Vendor Baru</span>
            </button>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('procurement.vendors.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari kode, nama vendor, kontak, email..."
                       class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-2xs">
            </div>
            <div>
                <select name="status" class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-2xs">
                    <option value="">-- Semua Status Kemitraan --</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition">
                    Filter
                </button>
                @if (request()->hasAny(['search', 'status']))
                    <a href="{{ route('procurement.vendors.index') }}" class="px-3 py-2 rounded-xl text-xs text-slate-500 hover:text-slate-800">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Vendor Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Kode &amp; Nama Vendor</th>
                        <th class="px-4 py-3">Kontak Person</th>
                        <th class="px-4 py-3">NPWP</th>
                        <th class="px-4 py-3">Informasi Rekening</th>
                        <th class="px-4 py-3">Total PO</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($vendors as $vendor)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-slate-900">{{ $vendor->name }}</div>
                                <div class="text-[11px] font-mono text-indigo-600">{{ $vendor->code }}</div>
                                @if ($vendor->address)
                                    <div class="text-[11px] text-slate-400 mt-0.5 truncate max-w-xs">{{ $vendor->address }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-medium text-slate-800">{{ $vendor->contact_person ?? '-' }}</div>
                                <div class="text-[11px] text-slate-500">{{ $vendor->phone ?? '-' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $vendor->email ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3.5 font-mono text-slate-600">
                                {{ $vendor->npwp ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5">
                                @if ($vendor->bank_name || $vendor->bank_account)
                                    <div class="font-medium text-slate-800">{{ $vendor->bank_name ?? 'Bank' }}</div>
                                    <div class="text-[11px] font-mono text-slate-500">{{ $vendor->bank_account ?? '-' }}</div>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700">
                                    {{ $vendor->purchase_orders_count }} PO
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                @if ($vendor->is_active)
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
                            <td colspan="6" class="px-4 py-12 text-center text-slate-400">
                                <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                                <p class="text-xs font-medium text-slate-500">Belum ada data vendor rekanan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($vendors->hasPages())
            <div class="p-3 border-t border-slate-200 bg-slate-50">
                {{ $vendors->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Form Tambah Vendor -->
    <div x-show="showModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         @keydown.escape.window="showModal = false">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-4"
             @click.outside="showModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Pendaftaran Rekanan Vendor Baru</h3>
                <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('procurement.vendors.store') }}" method="POST" class="space-y-3.5 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Kode Vendor <span class="text-rose-500">*</span></label>
                        <input type="text" name="code" required placeholder="VND-001"
                               class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nama Vendor <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required placeholder="PT Mitra Sejahtera"
                               class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">NPWP</label>
                        <input type="text" name="npwp" placeholder="01.234.567.8-901.000"
                               class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Kontak Person</label>
                        <input type="text" name="contact_person" placeholder="Bpk. Hendra"
                               class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">No. Telepon / WA</label>
                        <input type="text" name="phone" placeholder="081234567890"
                               class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Email Resmi</label>
                        <input type="email" name="email" placeholder="sales@mitrasejahtera.com"
                               class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Alamat Kantor / Workshop</label>
                    <textarea name="address" rows="2" placeholder="Jl. Industri Raya No. 12, Jakarta"
                              class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nama Bank</label>
                        <input type="text" name="bank_name" placeholder="BCA / Mandiri / BNI"
                               class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nomor Rekening</label>
                        <input type="text" name="bank_account" placeholder="1234567890"
                               class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 font-mono">
                    </div>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100">
                    <button type="button" @click="showModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-600/20">
                        Simpan Rekanan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
