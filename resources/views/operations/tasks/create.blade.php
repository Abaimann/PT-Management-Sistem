@extends('layouts.app')

@section('title', 'Buat Tugas Baru')
@section('page_title', 'Buat Tugas')

@section('breadcrumbs')
    <span class="text-slate-300">/</span>
    <a href="{{ route('tasks.index') }}" class="hover:text-indigo-600 transition">Tugas Operasional</a>
    <span class="text-slate-300">/</span>
    <span class="text-slate-800 font-semibold">Buat Baru</span>
@endsection

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header -->
    <div class="pb-2 border-b border-slate-200">
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Buat Penugasan Tugas Baru</h1>
        <p class="text-xs text-slate-500 mt-0.5">Tentukan pelaksana tugas, batas waktu, target operasional, dan tingkat prioritas.</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
        <form action="{{ route('tasks.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Task Title -->
            <div>
                <label for="title" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Judul Tugas <span class="text-rose-500">*</span>
                </label>
                <input type="text"
                       name="title"
                       id="title"
                       value="{{ old('title') }}"
                       required
                       placeholder="Misal: Penyusunan Laporan Audit Internal Q1"
                       class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 border @error('title') border-rose-500 @else border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 @enderror text-slate-900 outline-none transition" />
                @error('title')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Assignee -->
                <div>
                    <label for="assigned_to" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Penerima Tugas (Staf / Pelaksana) <span class="text-rose-500">*</span>
                    </label>
                    <select name="assigned_to"
                            id="assigned_to"
                            required
                            class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 border @error('assigned_to') border-rose-500 @else border-slate-200 focus:border-indigo-500 @enderror text-slate-900 outline-none transition">
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}" {{ old('assigned_to') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->full_name }} ({{ $emp->department?->name ?? 'Dept' }} - {{ $emp->position?->name ?? 'Pos' }})
                            </option>
                        @endforeach
                    </select>
                    @error('assigned_to')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Work Target KPI (Optional) -->
                <div>
                    <label for="work_target_id" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Terkait Target Kerja / KPI (Opsional)
                    </label>
                    <select name="work_target_id"
                            id="work_target_id"
                            class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 border @error('work_target_id') border-rose-500 @else border-slate-200 focus:border-indigo-500 @enderror text-slate-900 outline-none transition">
                        <option value="">-- Tanpa Kaitan Target Kerja --</option>
                        @foreach ($workTargets as $target)
                            <option value="{{ $target->id }}" {{ old('work_target_id') == $target->id ? 'selected' : '' }}>
                                {{ $target->title }} (Bulan {{ $target->month }}/{{ $target->year }})
                            </option>
                        @endforeach
                    </select>
                    @error('work_target_id')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Priority -->
                <div>
                    <label for="priority" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Tingkat Prioritas <span class="text-rose-500">*</span>
                    </label>
                    <select name="priority"
                            id="priority"
                            required
                            class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 border @error('priority') border-rose-500 @else border-slate-200 focus:border-indigo-500 @enderror text-slate-900 outline-none transition">
                        <option value="LOW" {{ old('priority') === 'LOW' ? 'selected' : '' }}>Low (Rendah)</option>
                        <option value="MEDIUM" {{ old('priority', 'MEDIUM') === 'MEDIUM' ? 'selected' : '' }}>Medium (Standar)</option>
                        <option value="HIGH" {{ old('priority') === 'HIGH' ? 'selected' : '' }}>High (Tinggi)</option>
                        <option value="URGENT" {{ old('priority') === 'URGENT' ? 'selected' : '' }}>Urgent (Mendesak)</option>
                    </select>
                    @error('priority')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Due Date -->
                <div>
                    <label for="due_date" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Batas Waktu Pengerjaan (Deadline)
                    </label>
                    <input type="datetime-local"
                           name="due_date"
                           id="due_date"
                           value="{{ old('due_date') }}"
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 border @error('due_date') border-rose-500 @else border-slate-200 focus:border-indigo-500 @enderror text-slate-900 outline-none transition" />
                    @error('due_date')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Rincian Instruksi &amp; Kriteria Keberhasilan
                </label>
                <textarea name="description"
                          id="description"
                          rows="5"
                          placeholder="Jelaskan ruang lingkup tugas, output yang diharapkan, serta format bukti kerja yang harus diunggah..."
                          class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 border @error('description') border-rose-500 @else border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 @enderror text-slate-900 outline-none transition">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('tasks.index') }}"
                   class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                    Batal
                </a>
                <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/20 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    <span>Simpan &amp; Terbitkan Tugas</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
