@extends('layouts.app')

@section('title', 'Tambah Jabatan Baru')
@section('page_title', 'Tambah Jabatan')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Breadcrumb & Back -->
    <div>
        <a href="{{ route('positions.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-indigo-600 transition mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Jabatan
        </a>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Tambah Jabatan Baru</h1>
        <p class="text-xs text-slate-500 mt-1">Daftarkan nama posisi baru, departemen penempatan, dan tingkat grade levelnya.</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs">
        <form action="{{ route('positions.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Departemen -->
            <div>
                <label for="department_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Departemen <span class="text-rose-500">*</span>
                </label>
                <select 
                    name="department_id" 
                    id="department_id" 
                    required 
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                >
                    <option value="">-- Pilih Departemen --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }} ({{ $dept->code }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Posisi jabatan akan berada di bawah naungan struktur divisi ini.</p>
            </div>

            <!-- Nama Jabatan -->
            <div>
                <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Nama Jabatan <span class="text-rose-500">*</span>
                </label>
                <input 
                    type="text" 
                    name="name" 
                    id="name" 
                    value="{{ old('name') }}" 
                    required 
                    placeholder="Contoh: Senior Fullstack Developer, HR Specialist, Finance Officer"
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                >
            </div>

            <!-- Grade Level -->
            <div>
                <label for="grade_level" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Grade Level (Tingkat Jabatan) <span class="text-rose-500">*</span>
                </label>
                <input 
                    type="text" 
                    name="grade_level" 
                    id="grade_level" 
                    value="{{ old('grade_level') }}" 
                    required 
                    list="grade_suggestions"
                    placeholder="Contoh: Staff, Senior, Supervisor, Manager, Director"
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                >
                <datalist id="grade_suggestions">
                    <option value="Intern">
                    <option value="Junior Staff">
                    <option value="Staff">
                    <option value="Senior Staff">
                    <option value="Supervisor">
                    <option value="Assistant Manager">
                    <option value="Manager">
                    <option value="General Manager">
                    <option value="Director">
                    <option value="Grade 1">
                    <option value="Grade 2">
                    <option value="Grade 3">
                    <option value="Grade 4">
                    <option value="Grade 5">
                </datalist>
                <p class="text-[11px] text-slate-400 mt-1">Grade level menentukan tingkatan wewenang dan baseline struktur kompensasi.</p>
            </div>

            <!-- Status Aktif -->
            <div class="pt-2">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input 
                        type="checkbox" 
                        name="is_active" 
                        value="1" 
                        {{ old('is_active', '1') ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                    >
                    <div>
                        <span class="text-xs font-bold text-slate-800">Status Jabatan Aktif</span>
                        <p class="text-[11px] text-slate-500">Jabatan aktif dapat dipilih saat merekrut atau memutasi karyawan.</p>
                    </div>
                </label>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('positions.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/20 transition">
                    Simpan Jabatan
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
