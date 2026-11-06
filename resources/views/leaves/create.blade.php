@extends('layouts.app')

@section('title', 'Permohonan Cuti Baru')
@section('page_title', 'Ajukan Cuti')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="leaveApplication()">

    <!-- Breadcrumb & Back -->
    <div>
        <a href="{{ route('leaves.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-indigo-600 transition mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Cuti
        </a>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Formulir Permohonan Cuti & Izin</h1>
        <p class="text-xs text-slate-500 mt-1">Pilih jenis cuti kerja, tentukan rentang hari pelaksanaan, dan lampirkan bukti pendukung.</p>
    </div>

    <!-- Employee Summary Card -->
    <div class="bg-indigo-50/70 border border-indigo-100 rounded-2xl p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white font-extrabold flex items-center justify-center text-sm shadow-xs">
                {{ strtoupper(substr($employee->full_name, 0, 1)) }}
            </div>
            <div>
                <h3 class="text-xs font-bold text-slate-900">{{ $employee->full_name }} ({{ $employee->nik }})</h3>
                <p class="text-[11px] text-slate-500">{{ $employee->department?->name }} &bull; {{ $employee->position?->name }}</p>
            </div>
        </div>
        <div class="text-right">
            <span class="text-[10px] text-slate-400 font-bold uppercase">Tahun Berjalan</span>
            <div class="text-xs font-bold text-indigo-700 font-mono">{{ date('Y') }}</div>
        </div>
    </div>

    <!-- Application Form Card -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs">
        <form action="{{ route('leaves.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <!-- Jenis Cuti -->
            <div>
                <label for="leave_type_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Jenis Cuti / Izin <span class="text-rose-500">*</span>
                </label>
                <select 
                    name="leave_type_id" 
                    id="leave_type_id" 
                    required 
                    x-model="selectedTypeId"
                    @change="updateSelectedQuota()"
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                >
                    <option value="">-- Pilih Jenis Cuti --</option>
                    @foreach($leaveTypes as $type)
                        @php
                            $bal = $balances->firstWhere('leave_type_id', $type->id);
                            $remaining = $bal ? $bal->remaining_quota : $type->default_quota;
                        @endphp
                        <option 
                            value="{{ $type->id }}" 
                            data-remaining="{{ $remaining }}"
                            data-requires-attachment="{{ $type->requires_attachment ? '1' : '0' }}"
                            {{ old('leave_type_id') == $type->id ? 'selected' : '' }}
                        >
                            {{ $type->name }} (Sisa: {{ $remaining }} Hari)
                        </option>
                    @endforeach
                </select>

                <!-- Quota Alert Badge -->
                <div class="mt-2 text-xs flex items-center justify-between p-2.5 bg-slate-50 rounded-xl border border-slate-200" x-show="selectedTypeId">
                    <span class="text-slate-500">Sisa Kuota Tersedia:</span>
                    <span class="font-bold text-indigo-600 font-mono" x-text="selectedQuota + ' Hari'"></span>
                </div>
            </div>

            <!-- Periode Tanggal -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="start_date" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Tanggal Mulai <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        name="start_date" 
                        id="start_date" 
                        value="{{ old('start_date', date('Y-m-d')) }}" 
                        required 
                        x-model="startDate"
                        @change="calculateDays()"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                    >
                </div>

                <div>
                    <label for="end_date" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Tanggal Selesai <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        name="end_date" 
                        id="end_date" 
                        value="{{ old('end_date', date('Y-m-d')) }}" 
                        required 
                        x-model="endDate"
                        @change="calculateDays()"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                    >
                </div>
            </div>

            <!-- Total Days Indicator -->
            <div class="p-3 bg-indigo-50/60 rounded-xl border border-indigo-100 flex items-center justify-between text-xs">
                <span class="text-slate-600 font-medium">Estimasi Durasi Pengajuan:</span>
                <span class="font-extrabold text-indigo-700 font-mono text-sm" x-text="totalDays + ' Hari Kalender'"></span>
            </div>

            <!-- Alasan Cuti -->
            <div>
                <label for="reason" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Alasan Permohonan Cuti <span class="text-rose-500">*</span>
                </label>
                <textarea 
                    name="reason" 
                    id="reason" 
                    rows="3" 
                    required 
                    placeholder="Jelaskan keperluan cuti/izin secara lengkap..."
                    class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                >{{ old('reason') }}</textarea>
            </div>

            <!-- Lampiran Bukti -->
            <div>
                <label for="attachment" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Lampiran Dokumen / Surat Bukti
                    <span x-show="requiresAttachment" class="text-rose-500 text-[10px] lowercase font-normal">(wajib untuk jenis cuti ini)</span>
                </label>
                <input 
                    type="file" 
                    name="attachment" 
                    id="attachment" 
                    :required="requiresAttachment"
                    class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition"
                >
                <p class="text-[11px] text-slate-400 mt-1">Format PDF, JPG, atau PNG maksimal 5MB (misal: surat keterangan dokter untuk cuti sakit).</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('leaves.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition">
                    Batal
                </a>
                <button 
                    type="submit" 
                    :disabled="totalDays > selectedQuota"
                    class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/20 transition disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Kirim Permohonan Cuti
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function leaveApplication() {
        return {
            selectedTypeId: '{{ old('leave_type_id', '') }}',
            selectedQuota: 0,
            requiresAttachment: false,
            startDate: '{{ old('start_date', date('Y-m-d')) }}',
            endDate: '{{ old('end_date', date('Y-m-d')) }}',
            totalDays: 1,

            init() {
                this.updateSelectedQuota();
                this.calculateDays();
            },

            updateSelectedQuota() {
                const selectEl = document.getElementById('leave_type_id');
                if (!selectEl) return;
                const opt = selectEl.options[selectEl.selectedIndex];
                if (opt && opt.value) {
                    this.selectedQuota = parseInt(opt.getAttribute('data-remaining') || '0', 10);
                    this.requiresAttachment = opt.getAttribute('data-requires-attachment') === '1';
                } else {
                    this.selectedQuota = 0;
                    this.requiresAttachment = false;
                }
            },

            calculateDays() {
                if (!this.startDate || !this.endDate) {
                    this.totalDays = 1;
                    return;
                }
                const start = new Date(this.startDate);
                const end = new Date(this.endDate);
                if (end >= start) {
                    const diffTime = Math.abs(end - start);
                    this.totalDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                } else {
                    this.totalDays = 0;
                }
            }
        };
    }
</script>
@endpush
