@extends('layouts.app')

@section('title', 'Manajemen Program Magang')
@section('page_title', 'Program Magang')

@section('breadcrumbs')
    <span class="text-slate-300">/</span>
    <span class="text-slate-800 font-semibold">Program Magang</span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Manajemen Program Magang</h1>
            <p class="text-xs text-slate-500 mt-0.5">Pengelolaan data peserta magang, bimbingan mentor, logbook aktivitas, dan penilaian akhir.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('internship.logbooks') }}"
               class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
                <span>Logbook Harian</span>
            </a>

            <a href="{{ route('internship.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/20 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Daftarkan Peserta Magang</span>
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <!-- Total -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Peserta</span>
            <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ $stats['total'] }}</div>
        </div>

        <!-- Active -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">Sedang Aktif</span>
            <div class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $stats['active'] }}</div>
        </div>

        <!-- Completed -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600">Selesai Magang</span>
            <div class="text-2xl font-extrabold text-blue-600 mt-1">{{ $stats['completed'] }}</div>
        </div>

        <!-- Verified Logbooks -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600">Logbook Terverifikasi</span>
            <div class="text-2xl font-extrabold text-indigo-600 mt-1">{{ $stats['total_logbooks_verified'] }}</div>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form action="{{ route('internship.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Search Keyword -->
            <div class="relative lg:col-span-2">
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Cari nama, NIM/kode, institusi, jurusan..."
                       class="w-full pl-9 pr-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-indigo-500" />
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </div>

            <!-- Status Filter -->
            <div>
                <select name="status" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-indigo-500">
                    <option value="">Semua Status</option>
                    <option value="ACTIVE" {{ request('status') === 'ACTIVE' ? 'selected' : '' }}>Aktif</option>
                    <option value="COMPLETED" {{ request('status') === 'COMPLETED' ? 'selected' : '' }}>Selesai</option>
                    <option value="TERMINATED" {{ request('status') === 'TERMINATED' ? 'selected' : '' }}>Dihentikan</option>
                </select>
            </div>

            <!-- Department Filter -->
            <div>
                <select name="department_id" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-indigo-500">
                    <option value="">Semua Departemen</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition">
                    Filter
                </button>
                <a href="{{ route('internship.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Interns Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200/80 text-[11px]">
                    <tr>
                        <th class="py-3.5 px-4">Peserta Magang</th>
                        <th class="py-3.5 px-4">Institusi &amp; Jurusan</th>
                        <th class="py-3.5 px-4">Departemen Penempatan</th>
                        <th class="py-3.5 px-4">Pembimbing (Mentor)</th>
                        <th class="py-3.5 px-4">Periode Magang</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($interns as $intern)
                        <tr class="hover:bg-slate-50/60 transition">
                            <!-- Name & Code -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 font-bold flex items-center justify-center shrink-0 border border-indigo-100">
                                        {{ strtoupper(substr($intern->full_name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('internship.show', $intern) }}" class="font-bold text-slate-900 hover:text-indigo-600 transition block">
                                            {{ $intern->full_name }}
                                        </a>
                                        <span class="text-[10px] text-slate-400 font-mono block">{{ $intern->intern_code }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Institution -->
                            <td class="py-3.5 px-4">
                                <span class="font-semibold text-slate-800 block">{{ $intern->institution }}</span>
                                <span class="text-[10px] text-slate-500 block">{{ $intern->major ?: 'Umum' }}</span>
                            </td>

                            <!-- Department -->
                            <td class="py-3.5 px-4">
                                <span class="font-medium text-slate-700">{{ $intern->department?->name ?? '-' }}</span>
                            </td>

                            <!-- Mentor -->
                            <td class="py-3.5 px-4">
                                <span class="font-medium text-slate-800">{{ $intern->mentor?->full_name ?? '-' }}</span>
                            </td>

                            <!-- Period -->
                            <td class="py-3.5 px-4 text-[11px] font-mono text-slate-600">
                                {{ $intern->start_date ? $intern->start_date->format('d/m/Y') : '-' }} s/d {{ $intern->end_date ? $intern->end_date->format('d/m/Y') : '-' }}
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4 text-center">
                                @if ($intern->status === 'ACTIVE')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Aktif
                                    </span>
                                @elseif ($intern->status === 'COMPLETED')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        Selesai
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600">
                                        {{ $intern->status }}
                                    </span>
                                @endif
                            </td>

                            <!-- Action -->
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('internship.show', $intern) }}"
                                   class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-700 text-xs font-semibold transition inline-flex items-center gap-1">
                                    <span>Detail</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400 italic">
                                Belum ada data peserta magang yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($interns->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $interns->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
