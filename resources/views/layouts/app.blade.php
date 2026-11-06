<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') — {{ config('app.name', 'PT Management Sistem ERP') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(148, 163, 184, 0.2); border-radius: 9999px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(148, 163, 184, 0.4); }
    </style>
    @stack('styles')
</head>
<body class="h-full text-slate-800 antialiased font-sans bg-slate-50 overflow-x-hidden" x-data>
    <div class="min-h-full flex flex-col lg:flex-row bg-slate-50">

        <!-- Responsive Collapsible Sidebar -->
        @include('layouts.partials.sidebar')

        <!-- Main View Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 min-h-screen">

            <!-- Enterprise Topbar / Navbar -->
            @include('layouts.partials.navbar')

            <!-- Global Toast / Floating Notification Container -->
            <div class="fixed top-20 right-6 z-50 flex flex-col gap-2 pointer-events-none max-w-sm w-full"
                 x-show="$store.notification.items.length > 0" x-cloak>
                <template x-for="item in $store.notification.items" :key="item.id">
                    <div class="pointer-events-auto p-4 rounded-2xl shadow-xl border text-xs font-medium flex items-start gap-3 transition-all duration-300"
                         :class="{
                             'bg-emerald-900/90 text-white border-emerald-700': item.type === 'success',
                             'bg-rose-900/90 text-white border-rose-700': item.type === 'error',
                             'bg-amber-900/90 text-white border-amber-700': item.type === 'warning',
                             'bg-slate-900/90 text-white border-slate-700': item.type === 'info'
                         }">
                        <div class="flex-1 leading-relaxed" x-text="item.message"></div>
                        <button type="button" @click="$store.notification.remove(item.id)" class="text-white/70 hover:text-white">
                            &times;
                        </button>
                    </div>
                </template>
            </div>

            <!-- Page Container with Flash Alerts -->
            <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full">
                <!-- Blade Flash Alerts -->
                @include('layouts.partials.flash')

                <!-- Page Header (Optional Slot) -->
                @hasSection('header')
                    <div class="mb-6">
                        @yield('header')
                    </div>
                @endif

                <!-- Dynamic Content Slot -->
                @yield('content')
            </main>

            <!-- Enterprise Footer -->
            <footer class="bg-white border-t border-slate-200/80 py-4 px-6 text-xs text-slate-500 mt-auto">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-2 max-w-7xl mx-auto">
                    <span>&copy; {{ date('Y') }} PT Management Sistem. Seluruh hak cipta dilindungi undang-undang.</span>
                    <div class="flex items-center gap-3 text-slate-400 font-mono text-[11px]">
                        <span>Timezone: Asia/Jakarta (WIB)</span>
                        <span>&bull;</span>
                        <span>ERP Engine Core 1.0</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
