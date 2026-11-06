@php
    $user = auth()->user();
    $primaryRole = $user?->roles->first();
@endphp

<header class="h-16 bg-white/95 backdrop-blur border-b border-slate-200/80 sticky top-0 z-30 flex items-center justify-between px-4 sm:px-6 shadow-xs">
    <!-- Left: Mobile Menu Trigger & Breadcrumbs -->
    <div class="flex items-center gap-3">
        <!-- Mobile Sidebar Toggle -->
        <button @click="$store.sidebar.toggleMobile()"
                type="button"
                class="lg:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 focus:outline-none transition">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
        </button>

        <!-- Breadcrumbs Navigation -->
        <nav class="flex items-center gap-2 text-xs font-medium text-slate-500">
            <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 transition flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                <span>ERP</span>
            </a>

            @hasSection('breadcrumbs')
                @yield('breadcrumbs')
            @else
                <span class="text-slate-300">/</span>
                <span class="text-slate-800 font-semibold truncate max-w-[200px] sm:max-w-none">
                    @yield('page_title', 'Dashboard')
                </span>
            @endif
        </nav>
    </div>

    <!-- Right: Status, Role Pill, & User Dropdown -->
    <div class="flex items-center gap-3">
        <!-- WIB System Clock -->
        <div class="hidden md:flex items-center gap-2 bg-slate-50 text-slate-600 text-xs px-3 py-1.5 rounded-full border border-slate-200">
            <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="font-medium">{{ \Carbon\Carbon::now()->setTimezone('Asia/Jakarta')->isoFormat('dddd, D MMMM Y') }}</span>
        </div>

        <!-- Role Badge Pill -->
        <div class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200/80 uppercase tracking-wide">
            {{ $primaryRole?->name ?? 'User' }}
        </div>

        <!-- User Dropdown Menu -->
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open"
                    @keydown.escape="open = false"
                    type="button"
                    class="flex items-center gap-2.5 p-1 rounded-full hover:ring-2 hover:ring-indigo-400 focus:outline-none transition group"
                    aria-expanded="false"
                    :aria-expanded="open.toString()">
                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-600 to-blue-500 text-white font-bold flex items-center justify-center text-xs shadow-xs ring-1 ring-white">
                    {{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}
                </div>
                <div class="hidden lg:block text-left pr-1">
                    <div class="text-xs font-semibold text-slate-800 group-hover:text-indigo-600 transition leading-tight">
                        {{ $user->name ?? 'Pengguna' }}
                    </div>
                    <div class="text-[10px] text-slate-400 font-mono">
                        {{ $primaryRole?->code ?? 'karyawan' }}
                    </div>
                </div>
                <svg class="w-4 h-4 text-slate-400 hidden lg:block transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
            </button>

            <!-- Dropdown Panel -->
            <div x-show="open"
                 @click.away="open = false"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-64 rounded-2xl bg-white shadow-2xl border border-slate-200 py-2 z-50 text-xs divide-y divide-slate-100"
                 x-cloak>

                <!-- Profile Info Header -->
                <div class="px-4 py-3">
                    <div class="font-bold text-slate-900 truncate">{{ $user->name }}</div>
                    <div class="text-slate-500 text-[11px] truncate">{{ $user->email }}</div>
                    <div class="mt-2 flex flex-wrap gap-1">
                        @foreach ($user->roles as $role)
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                {{ $role->name }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <!-- Navigation Links inside Dropdown -->
                <div class="py-1">
                    <a href="{{ route('profile.index') }}"
                       class="flex items-center gap-2.5 px-4 py-2.5 text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition">
                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                        <div class="flex-1">
                            <span class="font-medium block">Profil &amp; Keamanan</span>
                            <span class="text-[10px] text-slate-400 block">Ubah kata sandi &amp; data kontak</span>
                        </div>
                    </a>

                    <a href="{{ route('dashboard') }}"
                       class="flex items-center gap-2.5 px-4 py-2 text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition">
                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                        </svg>
                        <span>Dashboard Utama</span>
                    </a>
                </div>

                <!-- Logout Action -->
                <div class="py-1">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="w-full text-left flex items-center gap-2.5 px-4 py-2.5 text-rose-600 hover:bg-rose-50 font-semibold transition">
                            <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                            </svg>
                            <span>Keluar dari Sistem</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
