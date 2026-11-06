@extends('layouts.app')

@section('title', 'Purchase Requests (PR)')
@section('page_title', 'Purchase Requests')

@section('breadcrumbs')
    <span class="text-slate-300">/</span>
    <span class="text-slate-500">Procurement</span>
    <span class="text-slate-300">/</span>
    <span class="text-slate-800 font-semibold">Purchase Requests</span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Daftar Purchase Request (PR)</h1>
            <p class="text-xs text-slate-500 mt-0.5">Pengajuan kebutuhan pengadaan barang &amp; jasa dari tiap departemen untuk diverifikasi dan disetujui.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('procurement.requests.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/20 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Ajukan PR Baru</span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('procurement.requests.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nomor PR atau alasan kebutuhan..."
                       class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-2xs">
            </div>
            <div>
                <select name="department_id" class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-2xs">
                    <option value="">-- Semua Departemen --</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="status" class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-2xs">
                    <option value="">-- Semua Status --</option>
                    <option value="DRAFT" {{ request('status') === 'DRAFT' ? 'selected' : '' }}>DRAFT</option>
                    <option value="SUBMITTED" {{ request('status') === 'SUBMITTED' ? 'selected' : '' }}>SUBMITTED (Menunggu)</option>
                    <option value="APPROVED" {{ request('status') === 'APPROVED' ? 'selected' : '' }}>APPROVED (Disetujui)</option>
                    <option value="CONVERTED_TO_PO" {{ request('status') === 'CONVERTED_TO_PO' ? 'selected' : '' }}>CONVERTED TO PO</option>
                    <option value="REJECTED" {{ request('status') === 'REJECTED' ? 'selected' : '' }}>REJECTED</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition">
                    Filter
                </button>
                @if (request()->hasAny(['search', 'department_id', 'status']))
                    <a href="{{ route('procurement.requests.index') }}" class="px-3 py-2 rounded-xl text-xs text-slate-500 hover:text-slate-800">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Purchase Requests Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">No. PR &amp; Tanggal</th>
                        <th class="px-4 py-3">Departemen &amp; Pemohon</th>
                        <th class="px-4 py-3">Rincian Item</th>
                        <th class="px-4 py-3">Total Estimasi</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi &amp; Otorisasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($purchaseRequests as $pr)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3.5">
                                <div class="font-bold font-mono text-indigo-600">{{ $pr->pr_number }}</div>
                                <div class="text-[11px] text-slate-500">{{ $pr->request_date ? $pr->request_date->format('d M Y') : '-' }}</div>
                                @if ($pr->required_date)
                                    <div class="text-[10px] text-amber-600 font-medium">Batas: {{ $pr->required_date->format('d M Y') }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-slate-900">{{ $pr->department->name ?? '-' }}</div>
                                <div class="text-[11px] text-slate-500">{{ $pr->requester->full_name ?? ($pr->requester->user->name ?? '-') }}</div>
                                <div class="text-[11px] text-slate-400 mt-0.5 line-clamp-1 italic">{{ $pr->reason }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-slate-800">{{ $pr->items->count() }} Item Barang</div>
                                <div class="text-[11px] text-slate-500">
                                    {{ $pr->items->take(2)->pluck('item_name')->join(', ') }}
                                    @if ($pr->items->count() > 2)
                                        <span class="text-slate-400">+{{ $pr->items->count() - 2 }} lainnya</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3.5 font-bold font-mono text-slate-900">
                                Rp {{ number_format($pr->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5">
                                @if ($pr->status === 'APPROVED')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Disetujui (Approved)
                                    </span>
                                @elseif ($pr->status === 'SUBMITTED')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Menunggu Otorisasi
                                    </span>
                                @elseif ($pr->status === 'CONVERTED_TO_PO')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span> Terbit PO
                                    </span>
                                @elseif ($pr->status === 'REJECTED')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Ditolak
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Draft
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right space-x-1">
                                @if (in_array($pr->status, ['DRAFT', 'SUBMITTED']))
                                    <form action="{{ route('procurement.requests.approve', $pr) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" onclick="return confirm('Apakah Anda yakin menyetujui pengajuan PR ini?')"
                                                class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-semibold transition">
                                            Setujui PR
                                        </button>
                                    </form>
                                @endif

                                @if ($pr->status === 'APPROVED')
                                    <a href="{{ route('procurement.orders.create', ['pr_id' => $pr->id]) }}"
                                       class="inline-block px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-semibold transition">
                                        Terbitkan PO
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-slate-400">
                                <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                                <p class="text-xs font-medium text-slate-500">Belum ada pengajuan Purchase Request</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($purchaseRequests->hasPages())
            <div class="p-3 border-t border-slate-200 bg-slate-50">
                {{ $purchaseRequests->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
