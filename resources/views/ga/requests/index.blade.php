@extends('layouts.app')

@section('title', 'Permintaan Fasilitas & GA')
@section('page_title', 'General Affairs')

@section('breadcrumbs')
    <span class="text-slate-300">/</span>
    <span class="text-slate-500">General Affairs</span>
    <span class="text-slate-300">/</span>
    <span class="text-slate-800 font-semibold">Permintaan Fasilitas</span>
@endsection

@section('content')
<div x-data="{
        showCreateModal: false,
        showStatusModal: false,
        selectedRequest: null,
        openStatus(req) {
            this.selectedRequest = req;
            this.showStatusModal = true;
        }
     }" class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Permintaan Sarana, ATK &amp; Perbaikan Fasilitas</h1>
            <p class="text-xs text-slate-500 mt-0.5">Layanan terpadu General Affairs (GA) untuk kebutuhan logistik kantor, perbaikan AC/listrik, sarana kerja, dan operasional.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="showCreateModal = true"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/20 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Ajukan Kebutuhan Fasilitas</span>
            </button>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('ga.requests.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari judul permintaan sarana..."
                       class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-2xs">
            </div>
            <div>
                <select name="request_type" class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-2xs">
                    <option value="">-- Semua Jenis Kebutuhan --</option>
                    <option value="STATIONERY" {{ request('request_type') === 'STATIONERY' ? 'selected' : '' }}>Alat Tulis Kantor (ATK)</option>
                    <option value="EQUIPMENT" {{ request('request_type') === 'EQUIPMENT' ? 'selected' : '' }}>Peralatan &amp; Sarana Kerja</option>
                    <option value="FACILITY_REPAIR" {{ request('request_type') === 'FACILITY_REPAIR' ? 'selected' : '' }}>Perbaikan Fasilitas / AC / Listrik</option>
                    <option value="VEHICLE" {{ request('request_type') === 'VEHICLE' ? 'selected' : '' }}>Operasional Kendaraan</option>
                </select>
            </div>
            <div>
                <select name="status" class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-2xs">
                    <option value="">-- Semua Status --</option>
                    <option value="PENDING" {{ request('status') === 'PENDING' ? 'selected' : '' }}>PENDING (Menunggu)</option>
                    <option value="APPROVED" {{ request('status') === 'APPROVED' ? 'selected' : '' }}>APPROVED (Disetujui)</option>
                    <option value="IN_PROGRESS" {{ request('status') === 'IN_PROGRESS' ? 'selected' : '' }}>IN PROGRESS (Dikerjakan)</option>
                    <option value="RESOLVED" {{ request('status') === 'RESOLVED' ? 'selected' : '' }}>RESOLVED (Selesai)</option>
                    <option value="REJECTED" {{ request('status') === 'REJECTED' ? 'selected' : '' }}>REJECTED (Ditolak)</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition">
                    Filter
                </button>
                @if (request()->hasAny(['search', 'request_type', 'status']))
                    <a href="{{ route('ga.requests.index') }}" class="px-3 py-2 rounded-xl text-xs text-slate-500 hover:text-slate-800">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Requests Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Tgl &amp; Jenis Kebutuhan</th>
                        <th class="px-4 py-3">Judul &amp; Keterangan</th>
                        <th class="px-4 py-3">Pemohon &amp; Divisi</th>
                        <th class="px-4 py-3">Kuantitas &amp; Prioritas</th>
                        <th class="px-4 py-3">Status Permohonan</th>
                        @if ($isPrivileged)
                            <th class="px-4 py-3 text-right">Tindak Lanjut GA</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($facilityRequests as $req)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3.5">
                                <div class="text-[11px] text-slate-500">{{ $req->created_at ? $req->created_at->format('d M Y') : '-' }}</div>
                                @if ($req->request_type === 'STATIONERY')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700">
                                        ATK Kantor
                                    </span>
                                @elseif ($req->request_type === 'FACILITY_REPAIR')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-50 text-rose-700">
                                        Perbaikan Fasilitas
                                    </span>
                                @elseif ($req->request_type === 'EQUIPMENT')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-700">
                                        Peralatan Kerja
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">
                                        Kendaraan / Transport
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-slate-900">{{ $req->title }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5 whitespace-pre-line line-clamp-2">{{ $req->description }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-slate-800">{{ $req->employee->full_name ?? ($req->employee->user->name ?? '-') }}</div>
                                <div class="text-[11px] text-slate-400">{{ $req->department->name ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-mono font-bold text-slate-800">{{ $req->quantity }} Unit</div>
                                @if ($req->priority === 'URGENT')
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-rose-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-600 animate-pulse"></span> URGENT
                                    </span>
                                @elseif ($req->priority === 'HIGH')
                                    <span class="text-[10px] font-semibold text-amber-600">Tinggi (High)</span>
                                @else
                                    <span class="text-[10px] text-slate-400">Normal / Medium</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                @if ($req->status === 'RESOLVED')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Selesai (Resolved)
                                    </span>
                                    @if ($req->resolved_at)
                                        <div class="text-[10px] text-slate-400 mt-0.5">{{ $req->resolved_at->format('d M H:i') }}</div>
                                    @endif
                                @elseif ($req->status === 'IN_PROGRESS')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Sedang Dikerjakan
                                    </span>
                                @elseif ($req->status === 'APPROVED')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span> Disetujui GA
                                    </span>
                                @elseif ($req->status === 'REJECTED')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Ditolak
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Menunggu (Pending)
                                    </span>
                                @endif
                            </td>
                            @if ($isPrivileged)
                                <td class="px-4 py-3.5 text-right">
                                    <button type="button" @click="openStatus({{ json_encode($req) }})"
                                            class="px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-[11px] font-semibold transition">
                                        Tindak Lanjut
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isPrivileged ? 6 : 5 }}" class="px-4 py-12 text-center text-slate-400">
                                <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.32l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.32 4.486c.049.58.025 1.193-.14 1.743" />
                                </svg>
                                <p class="text-xs font-medium text-slate-500">Belum ada permohonan fasilitas yang diajukan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($facilityRequests->hasPages())
            <div class="p-3 border-t border-slate-200 bg-slate-50">
                {{ $facilityRequests->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Form Buat Permohonan Baru -->
    <div x-show="showCreateModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         @keydown.escape.window="showCreateModal = false">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-4"
             @click.outside="showCreateModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Formulir Permohonan Fasilitas / ATK</h3>
                <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('ga.requests.store') }}" method="POST" class="space-y-3.5 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Jenis Kebutuhan <span class="text-rose-500">*</span></label>
                        <select name="request_type" required class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="STATIONERY">Alat Tulis Kantor (ATK)</option>
                            <option value="EQUIPMENT">Peralatan / Sarana Kerja</option>
                            <option value="FACILITY_REPAIR">Perbaikan Fasilitas / AC / Listrik</option>
                            <option value="VEHICLE">Operasional Kendaraan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tingkat Urgensi <span class="text-rose-500">*</span></label>
                        <select name="priority" required class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="MEDIUM">Normal / Sedang</option>
                            <option value="LOW">Rendah</option>
                            <option value="HIGH">Tinggi (High)</option>
                            <option value="URGENT">Mendesak (URGENT)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Judul Permohonan <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" required placeholder="Contoh: Penggantian freon & pembersihan AC Ruang Meeting Lt. 2"
                           class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Jumlah / Kuantitas <span class="text-rose-500">*</span></label>
                        <input type="number" min="1" name="quantity" value="1" required
                               class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Departemen Pemohon</label>
                        <select name="department_id" class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- Gunakan Divisi Saya --</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Deskripsi Detail Masalah / Kebutuhan <span class="text-rose-500">*</span></label>
                    <textarea name="description" rows="3" required placeholder="Jelaskan kendala, lokasi spesifik, atau spesifikasi barang yang diperlukan..."
                              class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-600/20">
                        Kirim ke Tim GA
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Update Status GA (Privileged only) -->
    @if ($isPrivileged)
        <div x-show="showStatusModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
             @keydown.escape.window="showStatusModal = false">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4"
                 @click.outside="showStatusModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Tindak Lanjut &amp; Otorisasi GA</h3>
                        <p class="text-[11px] text-slate-500" x-text="selectedRequest ? selectedRequest.title : ''"></p>
                    </div>
                    <button @click="showStatusModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form :action="selectedRequest ? `/ga/requests/${selectedRequest.id}/status` : '#'" method="POST" class="space-y-3.5 text-xs">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Pembaruan Status <span class="text-rose-500">*</span></label>
                        <select name="status" required class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 font-bold">
                            <option value="APPROVED">APPROVED (Disetujui untuk Diproses)</option>
                            <option value="IN_PROGRESS">IN PROGRESS (Sedang Dalam Pengerjaan / Pembelian)</option>
                            <option value="RESOLVED">RESOLVED (Pekerjaan / Pengadaan Selesai)</option>
                            <option value="REJECTED">REJECTED (Permohonan Ditolak)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Catatan Tindak Lanjut GA</label>
                        <textarea name="resolution_notes" rows="3" placeholder="Contoh: Teknisi eksternal telah dipanggil, perbaikan selesai pada pukul 14:00..."
                                  class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100">
                        <button type="button" @click="showStatusModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 shadow-md">
                            Perbarui Status Permohonan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
@endsection
