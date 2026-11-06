@extends('layouts.app')

@section('title', 'Manajemen Tugas & Target Operasional')
@section('page_title', 'Tugas Operasional')

@section('breadcrumbs')
    <span class="text-slate-300">/</span>
    <span class="text-slate-800 font-semibold">Tugas Operasional</span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Manajemen Tugas &amp; Work Order</h1>
            <p class="text-xs text-slate-500 mt-0.5">Penugasan target harian tim, tracking progres pengerjaan, dan verifikasi bukti kerja.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('tasks.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/20 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Buat Tugas Baru</span>
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- Total -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Tugas</span>
            <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ $stats['total'] }}</div>
        </div>

        <!-- Todo -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">To Do</span>
            <div class="text-2xl font-extrabold text-slate-700 mt-1">{{ $stats['todo'] }}</div>
        </div>

        <!-- In Progress -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-blue-500">In Progress</span>
            <div class="text-2xl font-extrabold text-blue-600 mt-1">{{ $stats['in_progress'] }}</div>
        </div>

        <!-- Waiting Verification -->
        <div class="bg-white p-4 rounded-2xl border border-amber-200/80 shadow-xs bg-amber-50/20">
            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600">Verifikasi</span>
            <div class="text-2xl font-extrabold text-amber-600 mt-1">{{ $stats['waiting_verification'] }}</div>
        </div>

        <!-- Revision -->
        <div class="bg-white p-4 rounded-2xl border border-rose-200/80 shadow-xs bg-rose-50/20">
            <span class="text-[10px] font-bold uppercase tracking-wider text-rose-500">Revisi</span>
            <div class="text-2xl font-extrabold text-rose-600 mt-1">{{ $stats['revision'] }}</div>
        </div>

        <!-- Approved -->
        <div class="bg-white p-4 rounded-2xl border border-emerald-200/80 shadow-xs bg-emerald-50/20">
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">Disetujui</span>
            <div class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $stats['approved'] }}</div>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
        <!-- Tabs Scope -->
        <div class="flex items-center gap-2 border-b border-slate-100 pb-3 text-xs font-semibold">
            <a href="{{ request()->fullUrlWithQuery(['scope' => 'all']) }}"
               class="px-3 py-1.5 rounded-lg transition {{ $scope === 'all' ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                Semua Tugas
            </a>
            <a href="{{ request()->fullUrlWithQuery(['scope' => 'assigned_to_me']) }}"
               class="px-3 py-1.5 rounded-lg transition {{ $scope === 'assigned_to_me' ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                Tugas Saya
            </a>
            <a href="{{ request()->fullUrlWithQuery(['scope' => 'assigned_by_me']) }}"
               class="px-3 py-1.5 rounded-lg transition {{ $scope === 'assigned_by_me' ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                Tugas Diberikan
            </a>
        </div>

        <form action="{{ route('tasks.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <input type="hidden" name="scope" value="{{ $scope }}">

            <!-- Search Keyword -->
            <div class="relative lg:col-span-2">
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Cari judul, deskripsi, atau nama staf..."
                       class="w-full pl-9 pr-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" />
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </div>

            <!-- Status Filter -->
            <div>
                <select name="status" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-indigo-500">
                    <option value="">Semua Status</option>
                    <option value="TODO" {{ request('status') === 'TODO' ? 'selected' : '' }}>To Do</option>
                    <option value="IN_PROGRESS" {{ request('status') === 'IN_PROGRESS' ? 'selected' : '' }}>In Progress</option>
                    <option value="WAITING_VERIFICATION" {{ request('status') === 'WAITING_VERIFICATION' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                    <option value="REVISION" {{ request('status') === 'REVISION' ? 'selected' : '' }}>Revisi</option>
                    <option value="APPROVED" {{ request('status') === 'APPROVED' ? 'selected' : '' }}>Disetujui (Approved)</option>
                </select>
            </div>

            <!-- Priority Filter -->
            <div>
                <select name="priority" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-indigo-500">
                    <option value="">Semua Prioritas</option>
                    <option value="LOW" {{ request('priority') === 'LOW' ? 'selected' : '' }}>Low</option>
                    <option value="MEDIUM" {{ request('priority') === 'MEDIUM' ? 'selected' : '' }}>Medium</option>
                    <option value="HIGH" {{ request('priority') === 'HIGH' ? 'selected' : '' }}>High</option>
                    <option value="URGENT" {{ request('priority') === 'URGENT' ? 'selected' : '' }}>Urgent</option>
                </select>
            </div>

            <!-- Submit Button & Reset -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition">
                    Filter
                </button>
                <a href="{{ route('tasks.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Tasks Grid List -->
    @if ($tasks->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200/80 p-12 text-center">
            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                </svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800">Tidak ada tugas ditemukan</h3>
            <p class="text-xs text-slate-500 mt-1">Belum ada tugas yang cocok dengan kriteria filter saat ini.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($tasks as $task)
                @php
                    $isOverdue = $task->due_date && $task->due_date->isPast() && $task->status !== 'APPROVED';
                @endphp
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition flex flex-col justify-between p-5 relative overflow-hidden group">
                    <!-- Top Ribbon Indicators -->
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <!-- Priority Badge -->
                        @if ($task->priority === 'URGENT')
                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200 uppercase">
                                🔥 Urgent
                            </span>
                        @elseif ($task->priority === 'HIGH')
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 uppercase">
                                High
                            </span>
                        @elseif ($task->priority === 'MEDIUM')
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 uppercase">
                                Medium
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600 uppercase">
                                Low
                            </span>
                        @endif

                        <!-- Status Badge -->
                        @if ($task->status === 'APPROVED')
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Disetujui
                            </span>
                        @elseif ($task->status === 'WAITING_VERIFICATION')
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 animate-pulse">
                                Menunggu Verifikasi
                            </span>
                        @elseif ($task->status === 'REVISION')
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                Perlu Revisi
                            </span>
                        @elseif ($task->status === 'IN_PROGRESS')
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                Pengerjaan
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">
                                To Do
                            </span>
                        @endif
                    </div>

                    <!-- Title & Description -->
                    <div class="mb-4">
                        <a href="{{ route('tasks.show', $task) }}" class="text-sm font-bold text-slate-900 group-hover:text-indigo-600 transition block line-clamp-1">
                            {{ $task->title }}
                        </a>
                        <p class="text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed">
                            {{ $task->description ?: 'Tidak ada deskripsi rinci untuk tugas ini.' }}
                        </p>
                    </div>

                    <!-- Assignee & Due Date Footer -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs mt-auto">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-indigo-600 text-white font-bold text-[10px] flex items-center justify-center shrink-0">
                                {{ strtoupper(substr($task->assignedTo?->full_name ?? 'E', 0, 1)) }}
                            </div>
                            <span class="text-slate-700 font-medium truncate max-w-[110px]">
                                {{ $task->assignedTo?->full_name ?? '-' }}
                            </span>
                        </div>

                        <div class="text-right">
                            @if ($task->due_date)
                                <span class="font-mono text-[11px] {{ $isOverdue ? 'text-rose-600 font-bold' : 'text-slate-500' }}">
                                    {{ $task->due_date->format('d M Y') }}
                                </span>
                                @if ($isOverdue)
                                    <span class="block text-[9px] text-rose-500 font-bold uppercase">Terlambat</span>
                                @endif
                            @else
                                <span class="text-slate-400 text-[11px]">-</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $tasks->links() }}
        </div>
    @endif

</div>
@endsection
