@extends('layouts.app')

@section('title', 'Profil Dossier: ' . $employee->full_name)
@section('page_title', 'Profil Karyawan')

@section('content')
<div class="space-y-6" x-data="{ tab: 'details' }">

    <!-- Back Button -->
    <div>
        <a href="{{ route('employees.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-indigo-600 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Direktori Karyawan
        </a>
    </div>

    <!-- Header Dossier Card -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs relative overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-3xl bg-gradient-to-tr from-indigo-600 to-blue-500 text-white font-extrabold text-2xl sm:text-3xl flex items-center justify-center shadow-lg shadow-indigo-600/30 shrink-0">
                    {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">{{ $employee->full_name }}</h1>
                        @if($employee->is_active)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Nonaktif
                            </span>
                        @endif
                    </div>

                    <p class="text-xs sm:text-sm text-slate-500 mt-1 flex flex-wrap items-center gap-2 font-medium">
                        <span class="text-indigo-600 font-bold">{{ $employee->department?->name }}</span>
                        <span>&bull;</span>
                        <span>{{ $employee->position?->name }}</span>
                        <span>&bull;</span>
                        <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded text-xs font-mono font-semibold">{{ $employee->position?->grade_level }}</span>
                    </p>

                    <div class="flex flex-wrap items-center gap-4 mt-3 text-xs text-slate-500">
                        <span class="font-mono bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-200 text-slate-700">NIK: {{ $employee->nik }}</span>
                        <span class="inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Bergabung: {{ $employee->join_date?->isoFormat('D MMMM Y') ?? '-' }}
                        </span>
                        <span class="inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            {{ $employee->phone ?? '-' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-center">
                <a href="{{ route('employees.edit', $employee->id) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit Profil Karyawan
                </a>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex items-center gap-2 border-t border-slate-100 mt-6 pt-4 overflow-x-auto">
            <button 
                @click="tab = 'details'" 
                :class="tab === 'details' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'"
                class="px-4 py-2 rounded-xl text-xs font-bold transition shrink-0"
            >
                Informasi & Profil
            </button>
            <button 
                @click="tab = 'attendance'" 
                :class="tab === 'attendance' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'"
                class="px-4 py-2 rounded-xl text-xs font-bold transition shrink-0"
            >
                Riwayat Absensi Terkini
            </button>
            <button 
                @click="tab = 'tasks'" 
                :class="tab === 'tasks' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'"
                class="px-4 py-2 rounded-xl text-xs font-bold transition shrink-0"
            >
                Tugas Operasional ({{ $employee->assignedTasks->count() }})
            </button>
            <button 
                @click="tab = 'leaves'" 
                :class="tab === 'leaves' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'"
                class="px-4 py-2 rounded-xl text-xs font-bold transition shrink-0"
            >
                Saldo Cuti
            </button>
            <button 
                @click="tab = 'contracts'" 
                :class="tab === 'contracts' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'"
                class="px-4 py-2 rounded-xl text-xs font-bold transition shrink-0"
            >
                Kontrak Kerja ({{ $employee->contracts->count() }})
            </button>
        </div>
    </div>

    <!-- Tab 1: Informasi & Profil -->
    <div x-show="tab === 'details'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Card: Data Pribadi -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs space-y-4">
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2">
                Data Pribadi & Kependudukan
            </h3>

            <dl class="space-y-3 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-50">
                    <dt class="text-slate-500 font-medium">Jenis Kelamin</dt>
                    <dd class="text-slate-900 font-semibold">{{ $employee->gender === 'MALE' ? 'Laki-laki' : 'Perempuan' }}</dd>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-50">
                    <dt class="text-slate-500 font-medium">Tempat, Tgl Lahir</dt>
                    <dd class="text-slate-900 font-semibold">
                        {{ $employee->birth_place ?? '-' }}, {{ $employee->birth_date?->format('d M Y') ?? '-' }}
                    </dd>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-50">
                    <dt class="text-slate-500 font-medium">Nomor Telepon</dt>
                    <dd class="text-slate-900 font-mono font-semibold">{{ $employee->phone ?? '-' }}</dd>
                </div>
                <div class="py-1">
                    <dt class="text-slate-500 font-medium mb-1">Alamat Lengkap</dt>
                    <dd class="text-slate-800 bg-slate-50 p-2.5 rounded-xl border border-slate-100 leading-relaxed">
                        {{ $employee->address ?? 'Belum ada data alamat tercatat.' }}
                    </dd>
                </div>
            </dl>
        </div>

        <!-- Card: Organisasi & Akun Sistem -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs space-y-4">
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2">
                Penugasan Organisasi & Akun ERP
            </h3>

            <dl class="space-y-3 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-50">
                    <dt class="text-slate-500 font-medium">Departemen</dt>
                    <dd class="text-slate-900 font-semibold">{{ $employee->department?->name }} ({{ $employee->department?->code }})</dd>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-50">
                    <dt class="text-slate-500 font-medium">Jabatan & Grade</dt>
                    <dd class="text-slate-900 font-semibold">{{ $employee->position?->name }} &bull; {{ $employee->position?->grade_level }}</dd>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-50">
                    <dt class="text-slate-500 font-medium">Atasan Langsung</dt>
                    <dd class="text-slate-900 font-semibold">
                        @if($employee->supervisor)
                            <a href="{{ route('employees.show', $employee->supervisor->id) }}" class="text-indigo-600 hover:underline">
                                {{ $employee->supervisor->full_name }}
                            </a>
                        @else
                            <span class="text-slate-400">Tidak ada (Top Management)</span>
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-50">
                    <dt class="text-slate-500 font-medium">Status Kerja</dt>
                    <dd class="text-slate-900 font-semibold">{{ $employee->employment_status }}</dd>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-50">
                    <dt class="text-slate-500 font-medium">Akun Pengguna Sistem</dt>
                    <dd class="text-slate-900 font-semibold">
                        @if($employee->user)
                            <span class="text-indigo-600 font-mono">{{ $employee->user->email }}</span>
                        @else
                            <span class="text-slate-400">Belum terhubung akun ERP</span>
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-50">
                    <dt class="text-slate-500 font-medium">Peran Otorisasi (Roles)</dt>
                    <dd class="text-slate-900 font-semibold">
                        @if($employee->user && $employee->user->roles->isNotEmpty())
                            @foreach($employee->user->roles as $role)
                                <span class="inline-block px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 text-[10px] font-mono uppercase mr-1">
                                    {{ $role->code }}
                                </span>
                            @endforeach
                        @else
                            <span class="text-slate-400">-</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Tab 2: Riwayat Absensi Terkini -->
    <div x-show="tab === 'attendance'" class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Rekam Kehadiran 10 Hari Terakhir</h3>
                <p class="text-xs text-slate-500">Histori clock-in selfie dan GPS kehadiran karyawan</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="py-3.5 px-6">Tanggal</th>
                        <th class="py-3.5 px-6">Clock In</th>
                        <th class="py-3.5 px-6">Clock Out</th>
                        <th class="py-3.5 px-6">Status Kehadiran</th>
                        <th class="py-3.5 px-6">Keterlambatan</th>
                        <th class="py-3.5 px-6">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($employee->attendanceRecords as $record)
                        <tr>
                            <td class="py-3.5 px-6 font-semibold text-slate-900">
                                {{ $record->date?->isoFormat('dddd, D MMM Y') }}
                            </td>
                            <td class="py-3.5 px-6 font-mono text-indigo-600 font-bold">
                                {{ $record->clock_in ?? '-' }}
                            </td>
                            <td class="py-3.5 px-6 font-mono text-slate-700">
                                {{ $record->clock_out ?? '-' }}
                            </td>
                            <td class="py-3.5 px-6">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold {{ $record->status === 'LATE' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                                    {{ $record->status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-6 font-mono text-xs">
                                {{ $record->late_minutes > 0 ? $record->late_minutes . ' Menit' : '-' }}
                            </td>
                            <td class="py-3.5 px-6 text-slate-500">
                                {{ $record->notes ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 text-xs">
                                Belum ada riwayat kehadiran tercatat untuk karyawan ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 3: Tugas Operasional -->
    <div x-show="tab === 'tasks'" class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900">Daftar Penugasan Kerja & Tugas Harian</h3>
            <p class="text-xs text-slate-500">Tugas yang ditugaskan kepada {{ $employee->full_name }}</p>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($employee->assignedTasks as $task)
                <div class="p-5 flex items-center justify-between gap-4 hover:bg-slate-50 transition">
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="text-xs font-bold text-slate-900">{{ $task->title }}</h4>
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded {{ $task->priority === 'URGENT' ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-700' }}">
                                {{ $task->priority }}
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1">{{ Str::limit($task->description, 100) }}</p>
                        <div class="text-[10px] text-slate-400 mt-1 flex items-center gap-2">
                            <span>Tenggat: {{ $task->due_date?->format('d M Y H:i') ?? '-' }}</span>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 text-xs font-bold rounded-full {{ $task->status === 'APPROVED' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                        {{ $task->status }}
                    </span>
                </div>
            @empty
                <div class="p-8 text-center text-slate-400 text-xs">
                    Tidak ada tugas operasional yang sedang aktif ditugaskan.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Tab 4: Kuota Cuti -->
    <div x-show="tab === 'leaves'" class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900">Saldo Kuota Cuti & Izin</h3>
            <p class="text-xs text-slate-500">Alokasi cuti tahunan dan hak izin berbayar</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-6">
            @forelse($employee->leaveBalances as $balance)
                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4">
                    <span class="text-xs font-bold text-slate-800">{{ $balance->leaveType?->name ?? 'Cuti Tahunan' }}</span>
                    <div class="text-2xl font-black text-indigo-600 mt-2">{{ $balance->remaining_quota }} Hari</div>
                    <div class="text-[11px] text-slate-500 mt-1 flex justify-between">
                        <span>Total: {{ $balance->total_quota }}</span>
                        <span>Terpakai: {{ $balance->used_quota }}</span>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-6 text-slate-400 text-xs">
                    Belum ada alokasi kuota cuti untuk karyawan ini.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Tab 5: Kontrak Kerja -->
    <div x-show="tab === 'contracts'" class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900">Riwayat Perjanjian & Kontrak Kerja</h3>
            <p class="text-xs text-slate-500">Histori PKWT, PKWTT, dan perjanjian magang</p>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($employee->contracts as $contract)
                <div class="p-5 flex items-center justify-between gap-4">
                    <div>
                        <div class="text-xs font-bold text-slate-900 font-mono">{{ $contract->contract_number }}</div>
                        <div class="text-[11px] text-slate-500 mt-0.5">
                            Jenis: {{ $contract->contract_type }} &bull; Periode: {{ $contract->start_date?->format('d M Y') }} s/d {{ $contract->end_date?->format('d M Y') ?? 'Tidak Berbatas' }}
                        </div>
                    </div>
                    <span class="px-2.5 py-1 text-xs font-bold rounded-full {{ $contract->status === 'ACTIVE' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                        {{ $contract->status }}
                    </span>
                </div>
            @empty
                <div class="p-8 text-center text-slate-400 text-xs">
                    Belum ada data dokumen kontrak terdaftar untuk karyawan ini.
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
