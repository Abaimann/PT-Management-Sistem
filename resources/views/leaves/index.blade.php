@extends('layouts.app')

@section('title', 'Manajemen Pengajuan Cuti & Saldo')
@section('page_title', 'Pengajuan Cuti')

@section('content')
<div class="space-y-6" x-data="{ rejectModal: false, selectedLeaveId: null }">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Manajemen Cuti & Izin Karyawan</h1>
            <p class="text-xs text-slate-500 mt-1">Pengajuan cuti tahunan, izin sakit berbayar, dan persetujuan bertingkat.</p>
        </div>
        <a href="{{ route('leaves.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Ajukan Permohonan Cuti
        </a>
    </div>

    <!-- Saldo Cuti Cards for Logged in Employee -->
    @if($employeeBalances->isNotEmpty())
    <div>
        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Saldo Kuota Cuti Anda (Tahun {{ date('Y') }})</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
            @foreach($employeeBalances as $bal)
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                    <span class="text-[11px] font-bold text-slate-500">{{ $bal->leaveType?->name ?? 'Cuti' }}</span>
                    <div class="text-2xl font-black text-indigo-600 mt-1">{{ $bal->remaining_quota }} Hari</div>
                    <div class="text-[10px] text-slate-400 mt-0.5 flex justify-between">
                        <span>Terpakai: {{ $bal->used_quota }}</span>
                        <span>Total: {{ $bal->total_quota }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Menunggu Approval</span>
                <div class="text-2xl font-extrabold text-amber-600 mt-0.5">{{ $stats['pending_count'] }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Disetujui Bulan Ini</span>
                <div class="text-2xl font-extrabold text-emerald-600 mt-0.5">{{ $stats['approved_month'] }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Karyawan Cuti Hari Ini</span>
                <div class="text-2xl font-extrabold text-indigo-600 mt-0.5">{{ $stats['total_active_leaves_today'] }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form action="{{ route('leaves.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-3">
                <select 
                    name="status" 
                    onchange="this.form.submit()" 
                    class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                >
                    <option value="">Semua Status Pengajuan</option>
                    <option value="PENDING" {{ request('status') === 'PENDING' ? 'selected' : '' }}>Menunggu Approval (PENDING)</option>
                    <option value="APPROVED" {{ request('status') === 'APPROVED' ? 'selected' : '' }}>Disetujui (APPROVED)</option>
                    <option value="REJECTED" {{ request('status') === 'REJECTED' ? 'selected' : '' }}>Ditolak (REJECTED)</option>
                </select>
            </div>

            <div class="sm:col-span-4">
                <select 
                    name="leave_type_id" 
                    onchange="this.form.submit()" 
                    class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                >
                    <option value="">Semua Jenis Cuti</option>
                    @foreach($leaveTypes as $type)
                        <option value="{{ $type->id }}" {{ request('leave_type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-3">
                <select 
                    name="department_id" 
                    onchange="this.form.submit()" 
                    class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                >
                    <option value="">Semua Departemen</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 flex gap-1">
                <button type="submit" class="w-full py-2 bg-slate-900 text-white text-xs font-semibold rounded-xl hover:bg-slate-800 transition">
                    Filter
                </button>
                @if(request()->hasAny(['status', 'leave_type_id', 'department_id']))
                    <a href="{{ route('leaves.index') }}" class="px-3 py-2 bg-slate-100 text-slate-600 text-xs font-semibold rounded-xl hover:bg-slate-200 transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="py-3.5 px-6">Karyawan</th>
                        <th class="py-3.5 px-6">Jenis Cuti</th>
                        <th class="py-3.5 px-6">Periode Cuti</th>
                        <th class="py-3.5 px-6 text-center">Durasi</th>
                        <th class="py-3.5 px-6">Alasan</th>
                        <th class="py-3.5 px-6 text-center">Status</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($leaveRequests as $leave)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-900">{{ $leave->employee?->full_name ?? 'N/A' }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $leave->employee?->nik }} &bull; {{ $leave->employee?->department?->code }}</div>
                            </td>
                            <td class="py-4 px-6 font-semibold text-slate-800">
                                {{ $leave->leaveType?->name ?? 'N/A' }}
                            </td>
                            <td class="py-4 px-6 whitespace-nowrap">
                                <span class="font-semibold text-slate-900">{{ $leave->start_date?->format('d M Y') }}</span>
                                <span class="text-slate-400">s/d</span>
                                <span class="font-semibold text-slate-900">{{ $leave->end_date?->format('d M Y') }}</span>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-indigo-50 text-indigo-700">
                                    {{ $leave->total_days }} Hari
                                </span>
                            </td>
                            <td class="py-4 px-6 max-w-xs text-slate-600">
                                {{ Str::limit($leave->reason, 75) }}
                                @if($leave->attachment_path)
                                    <span class="block mt-0.5 text-[10px] text-indigo-600 font-medium">Ada Dokumen Lampiran</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-center">
                                @if($leave->status === 'APPROVED')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Disetujui
                                    </span>
                                @elseif($leave->status === 'REJECTED')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Ditolak
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Menunggu Approval
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right whitespace-nowrap">
                                @if($leave->status === 'PENDING' && $isApprover)
                                    <div class="inline-flex items-center gap-1.5">
                                        <!-- Approve Form -->
                                        <form action="{{ route('leaves.approve', $leave->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui permohonan cuti {{ $leave->employee?->full_name }}?');" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold transition shadow-xs">
                                                Setujui
                                            </button>
                                        </form>

                                        <!-- Reject Trigger -->
                                        <button 
                                            type="button" 
                                            @click="selectedLeaveId = {{ $leave->id }}; rejectModal = true"
                                            class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 text-[11px] font-bold transition"
                                        >
                                            Tolak
                                        </button>
                                    </div>
                                @else
                                    <span class="text-slate-400 text-[11px]">
                                        {{ $leave->approver?->name ?? '-' }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400 text-xs">
                                Belum ada daftar pengajuan cuti yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leaveRequests->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">
                {{ $leaveRequests->links() }}
            </div>
        @endif
    </div>

    <!-- Reject Modal -->
    <div 
        x-show="rejectModal" 
        x-transition:enter="transition ease-out duration-200" 
        x-transition:enter-start="opacity-0" 
        x-transition:enter-end="opacity-100" 
        x-transition:leave="transition ease-in duration-150" 
        x-transition:leave-start="opacity-100" 
        x-transition:leave-end="opacity-0" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" 
        x-cloak
    >
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4" @click.away="rejectModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">Alasan Penolakan Permohonan Cuti</h3>
                <button type="button" @click="rejectModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <form :action="'{{ url('leaves') }}/' + selectedLeaveId + '/reject'" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="modal_rejection_reason" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Keterangan Alasan Penolakan <span class="text-rose-500">*</span>
                    </label>
                    <textarea 
                        name="rejection_reason" 
                        id="modal_rejection_reason" 
                        rows="3" 
                        required 
                        placeholder="Contoh: Beban kerja tim sedang tinggi, sisa kuota tidak mencukupi, harap koordinasikan kembali..."
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-rose-500"
                    ></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="rejectModal = false" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-600 text-xs font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-md shadow-rose-600/20">
                        Konfirmasi Penolakan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
