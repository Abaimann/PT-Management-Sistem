@extends('layouts.app')

@section('title', 'Direktori Karyawan & SDM')
@section('page_title', 'Data Karyawan')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Direktori Karyawan & SDM</h1>
            <p class="text-xs text-slate-500 mt-1">Daftar seluruh pegawai perusahaan, data posisi jabatan, kontrak kerja, dan penugasan divisi.</p>
        </div>
        <a href="{{ route('employees.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            Tambah Karyawan Baru
        </a>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 sm:gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Karyawan</span>
            <div class="text-2xl font-extrabold text-slate-900 mt-0.5">{{ $stats['total'] }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">{{ $stats['active'] }} Aktif Bekerja</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">Tetap (PKWTT)</span>
            <div class="text-2xl font-extrabold text-emerald-600 mt-0.5">{{ $stats['permanent'] }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Pegawai Tetap</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600">Kontrak (PKWT)</span>
            <div class="text-2xl font-extrabold text-blue-600 mt-0.5">{{ $stats['contract'] }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Pegawai Kontrak</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-purple-600">Magang (Intern)</span>
            <div class="text-2xl font-extrabold text-purple-600 mt-0.5">{{ $stats['intern'] }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Program Magang</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs col-span-2 sm:col-span-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Departemen</span>
            <div class="text-2xl font-extrabold text-slate-700 mt-0.5">{{ $departments->count() }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">Divisi Aktif</div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form action="{{ route('employees.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-4 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Cari NIK, Nama, Telepon, atau Email..." 
                    class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                >
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

            <div class="sm:col-span-2">
                <select 
                    name="employment_status" 
                    onchange="this.form.submit()" 
                    class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                >
                    <option value="">Semua Status Kerja</option>
                    <option value="PERMANENT" {{ request('employment_status') === 'PERMANENT' ? 'selected' : '' }}>Permanent</option>
                    <option value="CONTRACT" {{ request('employment_status') === 'CONTRACT' ? 'selected' : '' }}>Contract</option>
                    <option value="PROBATION" {{ request('employment_status') === 'PROBATION' ? 'selected' : '' }}>Probation</option>
                    <option value="INTERN" {{ request('employment_status') === 'INTERN' ? 'selected' : '' }}>Intern</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <select 
                    name="status" 
                    onchange="this.form.submit()" 
                    class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                >
                    <option value="">Semua Status Aktif</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Hanya Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Hanya Nonaktif</option>
                </select>
            </div>

            <div class="sm:col-span-1 flex gap-1">
                <button type="submit" class="w-full py-2 bg-slate-900 text-white text-xs font-semibold rounded-xl hover:bg-slate-800 transition text-center">
                    Cari
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
                        <th class="py-3.5 px-6">Karyawan</th>
                        <th class="py-3.5 px-6">Departemen & Jabatan</th>
                        <th class="py-3.5 px-6">Status Kerja</th>
                        <th class="py-3.5 px-6">Kontak & Telepon</th>
                        <th class="py-3.5 px-6">Tgl Bergabung</th>
                        <th class="py-3.5 px-6 text-center">Status</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($employees as $emp)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-indigo-50 border border-indigo-200 text-indigo-700 font-bold flex items-center justify-center text-xs shrink-0">
                                        {{ strtoupper(substr($emp->full_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('employees.show', $emp->id) }}" class="font-bold text-slate-900 hover:text-indigo-600 block">
                                            {{ $emp->full_name }}
                                        </a>
                                        <div class="text-[11px] font-mono text-slate-400">
                                            NIK: {{ $emp->nik }}
                                            @if($emp->user)
                                                &bull; <span class="text-indigo-600">{{ $emp->user->email }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-semibold text-slate-800">{{ $emp->department?->name ?? 'N/A' }}</div>
                                <div class="text-[11px] text-slate-500">{{ $emp->position?->name ?? 'N/A' }} ({{ $emp->position?->grade_level ?? '-' }})</div>
                            </td>
                            <td class="py-4 px-6">
                                @php
                                    $statusClasses = [
                                        'PERMANENT' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'CONTRACT' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'PROBATION' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'INTERN' => 'bg-purple-50 text-purple-700 border-purple-200',
                                    ];
                                    $cls = $statusClasses[$emp->employment_status] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $cls }}">
                                    {{ $emp->employment_status }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-slate-600 font-mono text-[11px]">
                                {{ $emp->phone ?? '-' }}
                            </td>
                            <td class="py-4 px-6 text-slate-600">
                                {{ $emp->join_date?->format('d M Y') ?? '-' }}
                            </td>
                            <td class="py-4 px-6 text-center">
                                @if($emp->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('employees.show', $emp->id) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition" title="Lihat Dossier Profil">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>

                                    <a href="{{ route('employees.edit', $emp->id) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition" title="Edit Data Karyawan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>

                                    <form action="{{ route('employees.destroy', $emp->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan/menghapus karyawan \'{{ $emp->full_name }}\'?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Nonaktifkan Karyawan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400 text-xs">
                                Tidak ada data karyawan yang cocok dengan kriteria pencarian saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($employees->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">
                {{ $employees->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
