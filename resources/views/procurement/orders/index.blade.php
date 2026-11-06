@extends('layouts.app')

@section('title', 'Purchase Orders (PO)')
@section('page_title', 'Purchase Orders')

@section('breadcrumbs')
    <span class="text-slate-300">/</span>
    <span class="text-slate-500">Procurement</span>
    <span class="text-slate-300">/</span>
    <span class="text-slate-800 font-semibold">Purchase Orders</span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Daftar Purchase Order (PO)</h1>
            <p class="text-xs text-slate-500 mt-0.5">Penerbitan surat pesanan resmi kepada rekanan vendor dan pemantauan realisasi pengiriman fisik.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('procurement.orders.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/20 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Terbitkan PO Baru</span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('procurement.orders.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nomor PO atau nama rekanan..."
                       class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-2xs">
            </div>
            <div>
                <select name="vendor_id" class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-2xs">
                    <option value="">-- Semua Rekanan Vendor --</option>
                    @foreach ($vendors as $v)
                        <option value="{{ $v->id }}" {{ request('vendor_id') == $v->id ? 'selected' : '' }}>
                            {{ $v->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="status" class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-2xs">
                    <option value="">-- Semua Status --</option>
                    <option value="DRAFT" {{ request('status') === 'DRAFT' ? 'selected' : '' }}>DRAFT</option>
                    <option value="SENT" {{ request('status') === 'SENT' ? 'selected' : '' }}>SENT (Terkirim ke Rekanan)</option>
                    <option value="PARTIAL_RECEIVED" {{ request('status') === 'PARTIAL_RECEIVED' ? 'selected' : '' }}>PARTIAL (Diterima Sebagian)</option>
                    <option value="RECEIVED" {{ request('status') === 'RECEIVED' ? 'selected' : '' }}>RECEIVED (Lengkap Selesai)</option>
                    <option value="CANCELLED" {{ request('status') === 'CANCELLED' ? 'selected' : '' }}>BATAL</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition">
                    Filter
                </button>
                @if (request()->hasAny(['search', 'vendor_id', 'status']))
                    <a href="{{ route('procurement.orders.index') }}" class="px-3 py-2 rounded-xl text-xs text-slate-500 hover:text-slate-800">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Purchase Orders Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">No. PO &amp; Tanggal</th>
                        <th class="px-4 py-3">Vendor Rekanan</th>
                        <th class="px-4 py-3">Realisasi Penerimaan</th>
                        <th class="px-4 py-3">Subtotal &amp; Pajak</th>
                        <th class="px-4 py-3">Total Pesanan</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($purchaseOrders as $po)
                        @php
                            $totalQty = $po->items->sum('quantity');
                            $receivedQty = $po->items->sum('received_quantity');
                            $percent = $totalQty > 0 ? min(100, round(($receivedQty / $totalQty) * 100)) : 0;
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3.5">
                                <div class="font-bold font-mono text-indigo-600">{{ $po->po_number }}</div>
                                <div class="text-[11px] text-slate-500">{{ $po->po_date ? $po->po_date->format('d M Y') : '-' }}</div>
                                @if ($po->purchaseRequest)
                                    <div class="text-[10px] text-slate-400 font-mono">Ref PR: {{ $po->purchaseRequest->pr_number }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-slate-900">{{ $po->vendor->name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $po->vendor->contact_person ?? $po->vendor->phone }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center justify-between text-[11px] mb-1">
                                    <span class="font-semibold text-slate-700">{{ $receivedQty }} / {{ $totalQty }} Item</span>
                                    <span class="font-bold {{ $percent == 100 ? 'text-emerald-600' : 'text-slate-500' }}">{{ $percent }}%</span>
                                </div>
                                <div class="w-32 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="h-1.5 rounded-full {{ $percent == 100 ? 'bg-emerald-500' : 'bg-indigo-600' }}" style="width: {{ $percent }}%"></div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 font-mono">
                                <div class="text-slate-800">Rp {{ number_format($po->subtotal, 0, ',', '.') }}</div>
                                <div class="text-[10px] text-slate-400">+ PPN Rp {{ number_format($po->tax_amount, 0, ',', '.') }}</div>
                            </td>
                            <td class="px-4 py-3.5 font-bold font-mono text-slate-900">
                                Rp {{ number_format($po->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5">
                                @if ($po->status === 'RECEIVED')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Received (Lengkap)
                                    </span>
                                @elseif ($po->status === 'PARTIAL_RECEIVED')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Sebagian Diterima
                                    </span>
                                @elseif ($po->status === 'SENT')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Sent to Vendor
                                    </span>
                                @elseif ($po->status === 'CANCELLED')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Dibatalkan
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Draft
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
                                <p class="text-xs font-medium text-slate-500">Belum ada Purchase Order yang diterbitkan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($purchaseOrders->hasPages())
            <div class="p-3 border-t border-slate-200 bg-slate-50">
                {{ $purchaseOrders->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
