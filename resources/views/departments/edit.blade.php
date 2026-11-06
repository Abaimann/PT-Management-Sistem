@extends('layouts.app')

@section('title', 'Edit Departemen ' . $department->name)
@section('page_title', 'Edit Departemen')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Breadcrumb & Back -->
    <div>
        <a href="{{ route('departments.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-indigo-600 transition mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Departemen
        </a>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Edit Departemen: {{ $department->name }}</h1>
        <p class="text-xs text-slate-500 mt-1">Perbarui kode divisi, nama resmi departemen, atau pusat biaya.</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs">
        <form action="{{ route('departments.update', $department->id) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Kode Departemen -->
            <div>
                <label for="code" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Kode Departemen <span class="text-rose-500">*</span>
                </label>
                <input 
                    type="text" 
                    name="code" 
                    id="code" 
                    value="{{ old('code', $department->code) }}" 
                    required 
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono uppercase text-slate-800 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                >
                <p class="text-[11px] text-slate-400 mt-1">Maksimal 20 karakter, harus unik (contoh: HRD, FIN, ENG).</p>
            </div>

            <!-- Nama Departemen -->
            <div>
                <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Nama Departemen <span class="text-rose-500">*</span>
                </label>
                <input 
                    type="text" 
                    name="name" 
                    id="name" 
                    value="{{ old('name', $department->name) }}" 
                    required 
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                >
            </div>

            <!-- Cost Center -->
            <div>
                <label for="cost_center" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Cost Center (Pusat Biaya)
                </label>
                <input 
                    type="text" 
                    name="cost_center" 
                    id="cost_center" 
                    value="{{ old('cost_center', $department->cost_center) }}" 
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono uppercase text-slate-800 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                >
            </div>

            <!-- Status Aktif -->
            <div class="pt-2">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input 
                        type="checkbox" 
                        name="is_active" 
                        value="1" 
                        {{ old('is_active', $department->is_active) ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                    >
                    <div>
                        <span class="text-xs font-bold text-slate-800">Status Departemen Aktif</span>
                        <p class="text-[11px] text-slate-500">Nonaktifkan jika departemen ini dilebur atau sudah tidak beroperasi.</p>
                    </div>
                </label>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('departments.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/20 transition">
                    Perbarui Departemen
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
