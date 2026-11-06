@extends('layouts.app')

@section('title', 'Pengajuan Koreksi Kehadiran')
@section('page_title', 'Koreksi Absensi')

@section('content')
<div class="space-y-6" x-data="{ showModal: false, selectedCorrection: null, reviewAction: 'approve', showForm: false }">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('attendance.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-indigo-600 transition mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Rekap Kehadiran
            </a>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Koreksi Presensi & Waktu Kerja</h1>
            <p class="text-xs text-slate-500 mt-1">Pengajuan perbaikan jam clock-in/out akibat kendala teknis, dinas luar, atau lupa presensi.</p>
        </div>

        <button 
            type="button" 
            @click="showForm = !showForm"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition self-start sm:self-auto"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            <span x-text="showForm ? 'Tutup Formulir' : 'Ajukan Koreksi Baru'"></span>
        </button>
    </div>

    <!-- Collapsible Submission Form Card -->
    <div x-show="showForm" x-transition class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-4" x-cloak>
        <div class="border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Formulir Pengajuan Koreksi Jam Kerja</h3>
            <p class="text-xs text-slate-500 mt-0.5">Pilih tanggal absensi yang ingin dikoreksi dan berikan alasan yang jelas.</p>
        </div>

        <form action="{{ route('attendance.corrections.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Pilih Rekam Absensi -->
                <div>
                    <label for="attendance_record_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Rekam Absensi Terkait <span class="text-rose-500">*</span>
                    </label>
                    <select 
                        name="attendance_record_id" 
                        id="attendance_record_id" 
                        required 
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                    >
                        <option value="">-- Pilih Rekam Absensi --</option>
                        @foreach($employeeRecords as $rec)
                            <option value="{{ $rec->id }}">
                                {{ $rec->date?->format('d M Y') }} (In: {{ $rec->clock_in ?? '-' }}, Out: {{ $rec->clock_out ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Usulan Jam Masuk -->
                <div>
                    <label for="proposed_clock_in" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Usulan Jam Masuk (Clock In)
                    </label>
                    <input 
                        type="time" 
                        name="proposed_clock_in" 
                        id="proposed_clock_in" 
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                    >
                </div>

                <!-- Usulan Jam Pulang -->
                <div>
                    <label for="proposed_clock_out" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Usulan Jam Pulang (Clock Out)
                    </label>
                    <input 
                        type="time" 
                        name="proposed_clock_out" 
                        id="proposed_clock_out" 
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                    >
                </div>
            </div>

            <!-- Alasan Pengajuan -->
            <div>
                <label for="reason" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Alasan Lengkap Koreksi <span class="text-rose-500">*</span>
                </label>
                <textarea 
                    name="reason" 
                    id="reason" 
                    rows="2" 
                    required 
                    placeholder="Contoh: Tugas dinas luar bertemu klien di Sudirman, lupa clock-in karena kendala sinyal GPS..."
                    class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                ></textarea>
            </div>

            <!-- Lampiran Bukti -->
            <div>
                <label for="attachment" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Lampiran Bukti Pendukung (Foto Surat Tugas / Bukti Chat)
                </label>
                <input 
                    type="file" 
                    name="attachment" 
                    id="attachment" 
                    class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                >
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="showForm = false" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-600 text-xs font-semibold">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/20">
                    Kirim Pengajuan Koreksi
                </button>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="py-3.5 px-6">Tanggal Pengajuan</th>
                        <th class="py-3.5 px-6">Karyawan</th>
                        <th class="py-3.5 px-6">Tanggal Absensi</th>
                        <th class="py-3.5 px-6 text-center">Jam Aktual</th>
                        <th class="py-3.5 px-6 text-center">Jam Usulan</th>
                        <th class="py-3.5 px-6">Alasan</th>
                        <th class="py-3.5 px-6 text-center">Status</th>
                        <th class="py-3.5 px-6 text-right">Aksi Review</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($corrections as $corr)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-6 text-slate-500 whitespace-nowrap">
                                {{ $corr->created_at?->format('d M Y H:i') }}
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-900">{{ $corr->employee?->full_name ?? 'N/A' }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $corr->employee?->nik }}</div>
                            </td>
                            <td class="py-4 px-6 font-semibold text-slate-800 whitespace-nowrap">
                                {{ $corr->attendanceRecord?->date?->isoFormat('D MMM Y') ?? '-' }}
                            </td>
                            <td class="py-4 px-6 text-center font-mono text-[11px] text-slate-500">
                                In: {{ $corr->attendanceRecord?->clock_in ?? '-' }}<br>
                                Out: {{ $corr->attendanceRecord?->clock_out ?? '-' }}
                            </td>
                            <td class="py-4 px-6 text-center font-mono text-[11px] font-bold text-indigo-600">
                                In: {{ $corr->proposed_clock_in ?? '-' }}<br>
                                Out: {{ $corr->proposed_clock_out ?? '-' }}
                            </td>
                            <td class="py-4 px-6 max-w-xs text-slate-600">
                                {{ Str::limit($corr->reason, 80) }}
                            </td>
                            <td class="py-4 px-6 text-center">
                                @if($corr->status === 'APPROVED')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Disetujui
                                    </span>
                                @elseif($corr->status === 'REJECTED')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Ditolak
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Menunggu Review
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right whitespace-nowrap">
                                @if($corr->status === 'PENDING' && auth()->user()->hasRole(['super_admin', 'hr_admin', 'supervisor', 'director']))
                                    <button 
                                        type="button" 
                                        @click="selectedCorrection = {{ $corr->id }}; showModal = true"
                                        class="px-3 py-1 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-semibold transition"
                                    >
                                        Review
                                    </button>
                                @else
                                    <span class="text-slate-400 text-[11px]">
                                        {{ $corr->approver?->name ?? '-' }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400 text-xs">
                                Belum ada pengajuan koreksi absensi saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($corrections->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">
                {{ $corrections->links() }}
            </div>
        @endif
    </div>

    <!-- Review Modal for HR/Supervisor -->
    <div 
        x-show="showModal" 
        x-transition:enter="transition ease-out duration-200" 
        x-transition:enter-start="opacity-0" 
        x-transition:enter-end="opacity-100" 
        x-transition:leave="transition ease-in duration-150" 
        x-transition:leave-start="opacity-100" 
        x-transition:leave-end="opacity-0" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" 
        x-cloak
    >
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4" @click.away="showModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">Keputusan Koreksi Kehadiran</h3>
                <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <form :action="'{{ url('attendance/corrections') }}/' + selectedCorrection + '/approve'" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Tindakan</label>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-emerald-700">
                            <input type="radio" name="action" value="approve" x-model="reviewAction" class="text-emerald-600 focus:ring-emerald-500">
                            Setujui Koreksi
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-rose-700">
                            <input type="radio" name="action" value="reject" x-model="reviewAction" class="text-rose-600 focus:ring-rose-500">
                            Tolak Pengajuan
                        </label>
                    </div>
                </div>

                <div x-show="reviewAction === 'reject'">
                    <label for="rejection_reason" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Alasan Penolakan <span class="text-rose-500">*</span>
                    </label>
                    <textarea 
                        name="rejection_reason" 
                        id="rejection_reason" 
                        rows="2" 
                        placeholder="Jelaskan alasan pengajuan ditolak..." 
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-indigo-500"
                    ></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="showModal = false" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-600 text-xs font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/20">
                        Kirim Keputusan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
