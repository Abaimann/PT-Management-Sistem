@extends('layouts.app')

@section('title', 'Rekapitulasi Presensi & Kehadiran')
@section('page_title', 'Presensi Karyawan')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Presensi & Kehadiran Karyawan</h1>
            <p class="text-xs text-slate-500 mt-1">Rekap data jam kerja, validasi geofence GPS, selfie check-in/out, dan status keterlambatan.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('attendance.corrections') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-semibold shadow-xs transition">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Koreksi Absensi
            </a>
            <a href="{{ route('attendance.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Absensi Selfie & GPS
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Hadir Hari Ini</span>
                <div class="text-2xl font-extrabold text-slate-900 mt-0.5">{{ $stats['today_total'] }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Tepat Waktu</span>
                <div class="text-2xl font-extrabold text-emerald-600 mt-0.5">{{ $stats['today_on_time'] }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-600">Terlambat</span>
                <div class="text-2xl font-extrabold text-amber-600 mt-0.5">{{ $stats['today_late'] }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-rose-600">Di Luar Radius</span>
                <div class="text-2xl font-extrabold text-rose-600 mt-0.5">{{ $stats['today_out_of_radius'] }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form action="{{ route('attendance.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-3">
                <input 
                    type="date" 
                    name="date" 
                    value="{{ request('date') }}" 
                    class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                    placeholder="Pilih Tanggal..."
                >
            </div>

            <div class="sm:col-span-4">
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

            <div class="sm:col-span-3">
                <select 
                    name="status" 
                    onchange="this.form.submit()" 
                    class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                >
                    <option value="">Semua Status Kehadiran</option>
                    <option value="ON_TIME" {{ request('status') === 'ON_TIME' ? 'selected' : '' }}>Tepat Waktu (ON_TIME)</option>
                    <option value="LATE" {{ request('status') === 'LATE' ? 'selected' : '' }}>Terlambat (LATE)</option>
                    <option value="OUT_OF_RADIUS" {{ request('status') === 'OUT_OF_RADIUS' ? 'selected' : '' }}>Di Luar Radius (OUT_OF_RADIUS)</option>
                    <option value="LEAVE" {{ request('status') === 'LEAVE' ? 'selected' : '' }}>Cuti (LEAVE)</option>
                    <option value="SICK" {{ request('status') === 'SICK' ? 'selected' : '' }}>Sakit (SICK)</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex gap-1">
                <button type="submit" class="w-full py-2 bg-slate-900 text-white text-xs font-semibold rounded-xl hover:bg-slate-800 transition">
                    Filter
                </button>
                @if(request()->hasAny(['date', 'department_id', 'status']))
                    <a href="{{ route('attendance.index') }}" class="px-3 py-2 bg-slate-100 text-slate-600 text-xs font-semibold rounded-xl hover:bg-slate-200 transition">
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
                        <th class="py-3.5 px-6">Tanggal</th>
                        <th class="py-3.5 px-6">Karyawan</th>
                        <th class="py-3.5 px-6">Departemen & Shift</th>
                        <th class="py-3.5 px-6 text-center">Clock In</th>
                        <th class="py-3.5 px-6 text-center">Clock Out</th>
                        <th class="py-3.5 px-6 text-center">Status</th>
                        <th class="py-3.5 px-6 text-center">Keterlambatan</th>
                        <th class="py-3.5 px-6 text-right">Radius Kantor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($records as $rec)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-6 font-semibold text-slate-900 whitespace-nowrap">
                                {{ $rec->date?->isoFormat('D MMM Y') }}
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-900">{{ $rec->employee?->full_name ?? 'N/A' }}</div>
                                <div class="text-[11px] font-mono text-slate-400">NIK: {{ $rec->employee?->nik ?? '-' }}</div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-medium text-slate-800">{{ $rec->employee?->department?->name ?? '-' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $rec->shift?->name ?? 'Default Shift' }}</div>
                            </td>
                            <td class="py-4 px-6 text-center font-mono font-bold text-indigo-600">
                                {{ $rec->clock_in ?? '-' }}
                            </td>
                            <td class="py-4 px-6 text-center font-mono font-semibold text-slate-600">
                                {{ $rec->clock_out ?? '-' }}
                            </td>
                            <td class="py-4 px-6 text-center">
                                @php
                                    $badgeStyles = [
                                        'ON_TIME' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'LATE' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'OUT_OF_RADIUS' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        'LEAVE' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'SICK' => 'bg-purple-50 text-purple-700 border-purple-200',
                                        'ABSENT' => 'bg-slate-100 text-slate-600 border-slate-200',
                                    ];
                                    $style = $badgeStyles[$rec->status] ?? 'bg-slate-100 text-slate-600 border-slate-200';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $style }}">
                                    {{ $rec->status }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-center font-mono">
                                @if($rec->late_minutes > 0)
                                    <span class="text-amber-600 font-bold">+{{ $rec->late_minutes }} m</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right font-mono text-slate-600">
                                @if($rec->clock_in_distance_meters !== null)
                                    {{ number_format($rec->clock_in_distance_meters, 0) }} m
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400 text-xs">
                                Tidak ada rekam kehadiran yang cocok dengan filter tanggal atau kriteria ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($records->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">
                {{ $records->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
