@php
    $user = auth()->user();
    $roles = $user?->roles->pluck('code')->toArray() ?? [];
    $isSuperAdmin = in_array('super_admin', $roles);
@endphp

<!-- Mobile Sidebar Backdrop Overlay -->
<div x-show="$store.sidebar.mobileOpen"
     x-transition:enter="transition-opacity ease-linear duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-slate-950/70 z-40 lg:hidden backdrop-blur-sm"
     @click="$store.sidebar.closeMobile()"
     x-cloak></div>

<!-- Main Collapsible Sidebar -->
<aside :class="{
           'w-64': !$store.sidebar.collapsed,
           'w-20': $store.sidebar.collapsed,
           'translate-x-0': $store.sidebar.mobileOpen,
           '-translate-x-full lg:translate-x-0': !$store.sidebar.mobileOpen
       }"
       class="fixed lg:sticky top-0 inset-y-0 left-0 z-50 flex flex-col bg-slate-950 text-slate-200 transition-all duration-300 shadow-2xl border-r border-slate-800 select-none overflow-y-auto max-h-screen shrink-0">

    <!-- Brand Header -->
    <div class="h-16 flex items-center justify-between px-4 border-b border-slate-800/80 bg-slate-950/90 shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 overflow-hidden group">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-blue-500 flex items-center justify-center text-white font-black text-lg shadow-lg shadow-indigo-600/30 ring-1 ring-white/20 shrink-0 group-hover:scale-105 transition-transform">
                MS
            </div>
            <div class="flex flex-col whitespace-nowrap transition-opacity duration-200" x-show="!$store.sidebar.collapsed">
                <span class="font-bold text-sm tracking-wide text-white uppercase leading-tight">PT MANAGEMENT</span>
                <span class="text-[10px] text-indigo-400 font-semibold tracking-wider">ENTERPRISE SYSTEM</span>
            </div>
        </a>

        <!-- Desktop Collapse Button -->
        <button @click="$store.sidebar.toggle()"
                type="button"
                class="hidden lg:flex p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800/80 transition"
                :title="$store.sidebar.collapsed ? 'Perluas Sidebar' : 'Ciutkan Sidebar'">
            <svg class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': $store.sidebar.collapsed }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m18.75 4.5-7.5 7.5 7.5 7.5m-6-15L5.25 12l7.5 7.5" />
            </svg>
        </button>

        <!-- Mobile Close Button -->
        <button @click="$store.sidebar.closeMobile()"
                type="button"
                class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Active User Mini Profile -->
    <div class="px-3.5 py-3 border-b border-slate-800/80 bg-slate-900/40 shrink-0" x-show="!$store.sidebar.collapsed">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-indigo-600/20 border border-indigo-500/30 text-indigo-300 font-bold flex items-center justify-center text-xs shrink-0 shadow-inner">
                {{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-xs font-semibold text-white truncate">{{ $user->name ?? 'User' }}</div>
                <div class="text-[10px] text-slate-400 flex items-center gap-1.5 mt-0.5">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="uppercase font-mono text-[9px] text-indigo-300 truncate">
                        {{ $user->roles->first()?->name ?? 'Karyawan' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Menu Items -->
    <nav class="flex-1 px-3 py-4 space-y-5 overflow-y-auto custom-scrollbar">

        <!-- 1. DASHBOARD -->
        <div>
            <div class="px-2 mb-1.5 text-[10px] font-bold text-slate-500 uppercase tracking-wider" x-show="!$store.sidebar.collapsed">
                Navigasi
            </div>
            <div class="space-y-1">
                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                   :title="$store.sidebar.collapsed ? 'Dashboard' : ''">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    <span x-show="!$store.sidebar.collapsed" class="truncate">Dashboard</span>
                </a>

                @if ($isSuperAdmin || $user->hasRole('director'))
                @if (Route::has('executive.dashboard'))
                <a href="{{ route('executive.dashboard') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('executive.*') ? 'bg-gradient-to-r from-amber-500 to-amber-600 text-slate-950 font-bold shadow-lg shadow-amber-500/20' : 'text-amber-400 hover:bg-slate-800/80 hover:text-amber-300' }}"
                   :title="$store.sidebar.collapsed ? 'Dashboard Eksekutif' : ''">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6" />
                    </svg>
                    <span x-show="!$store.sidebar.collapsed" class="truncate flex items-center justify-between flex-1">
                        <span>Dashboard Eksekutif</span>
                        <span class="text-[9px] px-1.5 py-0.5 rounded-md bg-amber-400/20 text-amber-300 font-extrabold uppercase">VIP</span>
                    </span>
                </a>
                @endif
                @endif
            </div>
        </div>

        <!-- 2. CORE & MASTER DATA -->
        @if ($isSuperAdmin || $user->hasRole(['hr_admin', 'director']))
        <div>
            <div class="px-2 mb-1.5 text-[10px] font-bold text-slate-500 uppercase tracking-wider" x-show="!$store.sidebar.collapsed">
                Core &amp; Organisasi
            </div>
            <div class="space-y-1">
                <a href="{{ route('departments.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('departments.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                   :title="$store.sidebar.collapsed ? 'Departemen' : ''">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                    </svg>
                    <span x-show="!$store.sidebar.collapsed" class="truncate">Departemen</span>
                </a>

                <a href="{{ route('positions.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('positions.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                   :title="$store.sidebar.collapsed ? 'Jabatan & Grade' : ''">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0M12 12.75h.008v.008H12v-.008Z" />
                    </svg>
                    <span x-show="!$store.sidebar.collapsed" class="truncate">Jabatan &amp; Grade</span>
                </a>
            </div>
        </div>
        @endif

        <!-- 3. HRIS & KEPEGAWAIAN -->
        @if ($isSuperAdmin || $user->hasRole(['hr_admin', 'supervisor', 'director']))
        <div>
            <div class="px-2 mb-1.5 text-[10px] font-bold text-slate-500 uppercase tracking-wider" x-show="!$store.sidebar.collapsed">
                HRIS &amp; Karyawan
            </div>
            <div class="space-y-1">
                @if (Route::has('employees.index'))
                <a href="{{ route('employees.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('employees.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                   :title="$store.sidebar.collapsed ? 'Data Karyawan' : ''">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                    <span x-show="!$store.sidebar.collapsed" class="truncate">Data Karyawan</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        <!-- 4. PRESENSI & ABSENSI SMART -->
        <div>
            <div class="px-2 mb-1.5 text-[10px] font-bold text-slate-500 uppercase tracking-wider" x-show="!$store.sidebar.collapsed">
                Presensi &amp; Cuti
            </div>
            <div class="space-y-1">
                @if (Route::has('attendance.create'))
                <a href="{{ route('attendance.create') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('attendance.create') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                   :title="$store.sidebar.collapsed ? 'Scan Selfie & GPS' : ''">
                    <svg class="w-5 h-5 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                    </svg>
                    <span x-show="!$store.sidebar.collapsed" class="truncate">Scan Presensi Selfie</span>
                </a>
                @endif

                @if (Route::has('attendance.index'))
                <a href="{{ route('attendance.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('attendance.index') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                   :title="$store.sidebar.collapsed ? 'Rekap Kehadiran' : ''">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <span x-show="!$store.sidebar.collapsed" class="truncate">Rekapitulasi Kehadiran</span>
                </a>
                @endif

                @if (Route::has('attendance.corrections'))
                <a href="{{ route('attendance.corrections') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('attendance.corrections*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                   :title="$store.sidebar.collapsed ? 'Koreksi Absensi' : ''">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                    </svg>
                    <span x-show="!$store.sidebar.collapsed" class="truncate">Koreksi Absensi</span>
                </a>
                @endif

                @if (Route::has('leaves.index'))
                <a href="{{ route('leaves.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('leaves.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                   :title="$store.sidebar.collapsed ? 'Pengajuan Cuti & Saldo' : ''">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                    <span x-show="!$store.sidebar.collapsed" class="truncate">Pengajuan &amp; Saldo Cuti</span>
                </a>
                @endif
            </div>
        </div>

        <!-- 5. OPERASIONAL & TUGAS -->
        @if ($isSuperAdmin || $user->hasRole(['supervisor', 'employee', 'intern', 'director']))
        <div>
            <div class="px-2 mb-1.5 text-[10px] font-bold text-slate-500 uppercase tracking-wider" x-show="!$store.sidebar.collapsed">
                Operasional
            </div>
            <div class="space-y-1">
                @if (Route::has('tasks.index'))
                <a href="{{ route('tasks.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('tasks.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                   :title="$store.sidebar.collapsed ? 'Tugas & Target' : ''">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                    </svg>
                    <span x-show="!$store.sidebar.collapsed" class="truncate">Tugas &amp; Target Harian</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        <!-- 6. PROGRAM MAGANG -->
        @if ($isSuperAdmin || $user->hasRole(['intern', 'supervisor', 'hr_admin', 'director']))
        <div>
            <div class="px-2 mb-1.5 text-[10px] font-bold text-slate-500 uppercase tracking-wider" x-show="!$store.sidebar.collapsed">
                Program Magang
            </div>
            <div class="space-y-1">
                @if (Route::has('internship.index'))
                <a href="{{ route('internship.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('internship.index') || request()->routeIs('internship.show') || request()->routeIs('internship.create') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                   :title="$store.sidebar.collapsed ? 'Data Peserta Magang' : ''">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                    </svg>
                    <span x-show="!$store.sidebar.collapsed" class="truncate">Peserta Magang</span>
                </a>
                @endif

                @if (Route::has('internship.logbooks'))
                <a href="{{ route('internship.logbooks') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('internship.logbooks*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                   :title="$store.sidebar.collapsed ? 'Logbook Magang' : ''">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                    </svg>
                    <span x-show="!$store.sidebar.collapsed" class="truncate">Logbook Harian</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        <!-- 7. PENGGAJIAN (PAYROLL) -->
        @if ($isSuperAdmin || $user->hasRole(['payroll_officer', 'finance_officer', 'director']))
        <div>
            <div class="px-2 mb-1.5 text-[10px] font-bold text-slate-500 uppercase tracking-wider" x-show="!$store.sidebar.collapsed">
                Payroll &amp; Penggajian
            </div>
            <div class="space-y-1">
                @if (Route::has('payroll.periods.index'))
                <a href="{{ route('payroll.periods.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('payroll.periods.*') || request()->routeIs('payroll.index') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                   :title="$store.sidebar.collapsed ? 'Periode & Payroll' : ''">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6H2.25m0 0v1.5a.75.75 0 0 1 .75.75H3.75m0-2.25H21m-17.25 0h17.25m-17.25 0V3m17.25 1.5v.75A.75.75 0 0 1 21 6h.75m0 0v1.5a.75.75 0 0 1-.75.75H21m0-2.25H3.75" />
                    </svg>
                    <span x-show="!$store.sidebar.collapsed" class="truncate">Periode &amp; Payroll</span>
                </a>
                @endif

                @if ($user->employee && Route::has('payroll.payslips.my'))
                <a href="{{ route('payroll.payslips.my') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('payroll.payslips.my') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                   :title="$store.sidebar.collapsed ? 'Slip Gaji Saya' : ''">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    <span x-show="!$store.sidebar.collapsed" class="truncate">Slip Gaji Saya</span>
                </a>
                @endif
            </div>
        </div>
        @elseif ($user->employee && Route::has('payroll.payslips.my'))
        <div>
            <div class="px-2 mb-1.5 text-[10px] font-bold text-slate-500 uppercase tracking-wider" x-show="!$store.sidebar.collapsed">
                Gaji &amp; Penghasilan
            </div>
            <div class="space-y-1">
                <a href="{{ route('payroll.payslips.my') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('payroll.payslips.my') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                   :title="$store.sidebar.collapsed ? 'Slip Gaji Saya' : ''">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    <span x-show="!$store.sidebar.collapsed" class="truncate">Slip Gaji Saya</span>
                </a>
            </div>
        </div>
        @endif

        <!-- 8. KEUANGAN & AKUNTANSI -->
        <div>
            <div class="px-2 mb-1.5 text-[10px] font-bold text-slate-500 uppercase tracking-wider" x-show="!$store.sidebar.collapsed">
                Finance &amp; Akuntansi
            </div>
            <div class="space-y-1">
                @if ($isSuperAdmin || $user->hasRole(['finance_officer', 'director']))
                @if (Route::has('finance.index'))
                <a href="{{ route('finance.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('finance.index') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                   :title="$store.sidebar.collapsed ? 'Overview Finansial' : ''">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.143 2.143L15.75 6" />
                    </svg>
                    <span x-show="!$store.sidebar.collapsed" class="truncate">Overview Finansial</span>
                </a>
                @endif

                @if (Route::has('finance.journals.index'))
                <a href="{{ route('finance.journals.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('finance.journals.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                   :title="$store.sidebar.collapsed ? 'Jurnal Umum' : ''">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <span x-show="!$store.sidebar.collapsed" class="truncate">Jurnal Umum</span>
                </a>
                @endif

                @if (Route::has('finance.accounts.index'))
                <a href="{{ route('finance.accounts.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('finance.accounts.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                   :title="$store.sidebar.collapsed ? 'Chart of Accounts' : ''">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" />
                    </svg>
                    <span x-show="!$store.sidebar.collapsed" class="truncate">Chart of Accounts</span>
                </a>
                @endif
                @endif

                @if (Route::has('reimbursements.index'))
                <a href="{{ route('reimbursements.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('reimbursements.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                   :title="$store.sidebar.collapsed ? 'Klaim Reimbursement' : ''">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6H2.25m0 0v1.5a.75.75 0 0 1 .75.75H3.75m0-2.25H21m-17.25 0h17.25m-17.25 0V3m17.25 1.5v.75A.75.75 0 0 1 21 6h.75m0 0v1.5a.75.75 0 0 1-.75.75H21m0-2.25H3.75m0 0v7.5A2.25 2.25 0 0 0 6 15h12a2.25 2.25 0 0 0 2.25-2.25v-7.5" />
                    </svg>
                    <span x-show="!$store.sidebar.collapsed" class="truncate">Klaim Reimbursement</span>
                </a>
                @endif
            </div>
        </div>

        <!-- 9. PENGADAAN (PROCUREMENT) -->
        @if ($isSuperAdmin || $user->hasRole(['procurement_officer', 'finance_officer', 'director']))
        <div>
            <div class="px-2 mb-1.5 text-[10px] font-bold text-slate-500 uppercase tracking-wider" x-show="!$store.sidebar.collapsed">
                Pengadaan (Procurement)
            </div>
            <div class="space-y-1">
                @if (Route::has('procurement.vendors.index'))
                <a href="{{ route('procurement.vendors.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('procurement.vendors.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                   :title="$store.sidebar.collapsed ? 'Vendor Rekanan' : ''">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                    </svg>
                    <span x-show="!$store.sidebar.collapsed" class="truncate">Vendor Rekanan</span>
                </a>
                @endif

                @if (Route::has('procurement.requests.index'))
                <a href="{{ route('procurement.requests.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('procurement.requests.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                   :title="$store.sidebar.collapsed ? 'Purchase Requests' : ''">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    <span x-show="!$store.sidebar.collapsed" class="truncate">Purchase Request (PR)</span>
                </a>
                @endif

                @if (Route::has('procurement.orders.index'))
                <a href="{{ route('procurement.orders.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('procurement.orders.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                   :title="$store.sidebar.collapsed ? 'Purchase Orders' : ''">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                    </svg>
                    <span x-show="!$store.sidebar.collapsed" class="truncate">Purchase Orders (PO)</span>
                </a>
                @endif

                @if (Route::has('procurement.receipts.index'))
                <a href="{{ route('procurement.receipts.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('procurement.receipts.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                   :title="$store.sidebar.collapsed ? 'Penerimaan Barang' : ''">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.948c0-.621-.504-1.125-1.125-1.125H4.125C3.504 5.5 3 6.004 3 6.625v10.125c0 .621.504 1.125 1.125 1.125h.375m12 0h-9" />
                    </svg>
                    <span x-show="!$store.sidebar.collapsed" class="truncate">Penerimaan Barang (GR)</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        <!-- 10. GUDANG & INVENTARIS (INVENTORY) -->
        @if ($isSuperAdmin || $user->hasRole(['inventory_ga', 'director']))
        <div>
            <div class="px-2 mb-1.5 text-[10px] font-bold text-slate-500 uppercase tracking-wider" x-show="!$store.sidebar.collapsed">
                Inventaris &amp; Aset
            </div>
            <div class="space-y-1">
                @if (Route::has('inventory.items.index'))
                <a href="{{ route('inventory.items.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('inventory.items.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                   :title="$store.sidebar.collapsed ? 'Katalog Barang' : ''">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                    </svg>
                    <span x-show="!$store.sidebar.collapsed" class="truncate">Katalog &amp; Stok Barang</span>
                </a>
                @endif

                @if (Route::has('inventory.warehouses.index'))
                <a href="{{ route('inventory.warehouses.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('inventory.warehouses.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                   :title="$store.sidebar.collapsed ? 'Lokasi Gudang' : ''">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                    </svg>
                    <span x-show="!$store.sidebar.collapsed" class="truncate">Gudang &amp; Lokasi</span>
                </a>
                @endif

                @if (Route::has('inventory.movements.index'))
                <a href="{{ route('inventory.movements.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('inventory.movements.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                   :title="$store.sidebar.collapsed ? 'Mutasi Stok' : ''">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                    </svg>
                    <span x-show="!$store.sidebar.collapsed" class="truncate">Mutasi Stok Gudang</span>
                </a>
                @endif

                @if (Route::has('inventory.assets.index'))
                <a href="{{ route('inventory.assets.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('inventory.assets.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                   :title="$store.sidebar.collapsed ? 'Aset Tetap & BAST' : ''">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
                    </svg>
                    <span x-show="!$store.sidebar.collapsed" class="truncate">Aset Tetap &amp; BAST</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        <!-- 11. GENERAL AFFAIRS (GA) -->
        <div>
            <div class="px-2 mb-1.5 text-[10px] font-bold text-slate-500 uppercase tracking-wider" x-show="!$store.sidebar.collapsed">
                General Affairs
            </div>
            <div class="space-y-1">
                @if (Route::has('ga.requests.index'))
                <a href="{{ route('ga.requests.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition {{ request()->routeIs('ga.requests.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                   :title="$store.sidebar.collapsed ? 'Fasilitas & ATK' : ''">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.32l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.32 4.486c.049.58.025 1.193-.14 1.743" />
                    </svg>
                    <span x-show="!$store.sidebar.collapsed" class="truncate">Permintaan Fasilitas &amp; ATK</span>
                </a>
                @endif
            </div>
        </div>

    </nav>

    <!-- Sidebar Bottom: Profile Quick Link & Logout -->
    <div class="p-3 border-t border-slate-800/80 bg-slate-950/80 shrink-0 space-y-1">
        <a href="{{ route('profile.index') }}"
           class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium text-slate-300 hover:bg-slate-800/80 hover:text-white transition"
           :title="$store.sidebar.collapsed ? 'Pengaturan Profil' : ''">
            <svg class="w-5 h-5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
            <span x-show="!$store.sidebar.collapsed" class="truncate">Profil Saya</span>
        </a>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit"
                    class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium text-rose-400 hover:bg-rose-950/30 hover:text-rose-300 transition"
                    :title="$store.sidebar.collapsed ? 'Keluar' : ''">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                </svg>
                <span x-show="!$store.sidebar.collapsed" class="truncate">Keluar Sistem</span>
            </button>
        </form>
    </div>
</aside>
