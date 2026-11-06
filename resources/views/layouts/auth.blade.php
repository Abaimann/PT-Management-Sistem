<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Masuk' }} — {{ config('app.name', 'PT Management Sistem ERP') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-100 bg-slate-950 selection:bg-indigo-500 selection:text-white">
    <div class="relative min-h-screen flex flex-col justify-between overflow-hidden">
        <!-- Background Decorative Orbs -->
        <div class="pointer-events-none absolute inset-0 flex items-center justify-center -z-10">
            <div class="w-[600px] h-[600px] bg-indigo-600/10 rounded-full blur-3xl -top-40 -left-20 absolute"></div>
            <div class="w-[500px] h-[500px] bg-blue-600/10 rounded-full blur-3xl bottom-10 right-10 absolute"></div>
            <div class="w-[300px] h-[300px] bg-emerald-500/5 rounded-full blur-2xl top-1/2 left-1/3 absolute"></div>
        </div>

        <!-- Header Brand Topbar -->
        <header class="w-full max-w-7xl mx-auto px-6 pt-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-blue-500 flex items-center justify-center shadow-lg shadow-indigo-500/20 text-white font-bold text-lg ring-1 ring-white/20">
                    MS
                </div>
                <div>
                    <span class="block text-base font-semibold tracking-tight text-white">PT MANAGEMENT SISTEM</span>
                    <span class="block text-xs font-medium text-slate-400">Integrated Enterprise Resource Planning</span>
                </div>
            </div>
            <div class="hidden sm:flex items-center gap-2 text-xs text-slate-400 bg-slate-900/60 border border-slate-800/80 px-3 py-1.5 rounded-full backdrop-blur">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>WIB (UTC+7) System Active</span>
            </div>
        </header>

        <!-- Main Content Slot -->
        <main class="flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-8 py-10">
            <div class="w-full max-w-md">
                @include('layouts.partials.flash')
                @yield('content')
            </div>
        </main>

        <!-- Footer -->
        <footer class="w-full max-w-7xl mx-auto px-6 py-6 text-center text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-2 border-t border-slate-900">
            <p>&copy; {{ date('Y') }} PT Management Sistem. Seluruh hak cipta dilindungi undang-undang.</p>
            <div class="flex items-center gap-4 text-slate-400">
                <span class="hover:text-slate-200 transition">Sesi Terenkripsi</span>
                <span>&bull;</span>
                <span class="hover:text-slate-200 transition">Keamanan Berbasis Peran (RBAC)</span>
            </div>
        </footer>
    </div>
</body>
</html>
