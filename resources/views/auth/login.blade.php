@extends('layouts.auth')

@section('content')
<div class="bg-slate-900/80 border border-slate-800/80 backdrop-blur-xl rounded-2xl shadow-2xl p-6 sm:p-8">
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold tracking-tight text-white">Selamat Datang</h1>
        <p class="text-xs text-slate-400 mt-1">Masuk ke Portal Operasional PT Management Sistem</p>
    </div>

    @if ($errors->any())
        <div class="mb-5 p-4 rounded-xl border border-rose-500/20 bg-rose-500/10 text-rose-300 text-sm">
            <div class="flex items-center gap-2 font-semibold text-rose-200 mb-1">
                <svg class="w-4 h-4 text-rose-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 7.5h.01" />
                </svg>
                <span>Autentikasi Gagal</span>
            </div>
            <ul class="list-disc list-inside space-y-0.5 text-xs text-rose-300/90">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login.post') }}" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
        @csrf

        <!-- Email Field -->
        <div>
            <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                Alamat Email Perusahaan
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                    </svg>
                </div>
                <input id="email"
                       type="email"
                       name="email"
                       value="{{ old('email', 'admin@pt-management.test') }}"
                       required
                       autofocus
                       autocomplete="email"
                       placeholder="nama@pt-management.test"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl text-xs bg-slate-950/60 border @error('email') border-rose-500 ring-1 ring-rose-500 @else border-slate-700/80 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 @enderror text-white placeholder-slate-500 transition outline-none" />
            </div>
            @error('email')
                <p class="mt-1.5 text-xs text-rose-400 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password Field with Toggle Visibility -->
        <div x-data="{ showPass: false }">
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                    Kata Sandi
                </label>
            </div>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                </div>
                <input id="password"
                       :type="showPass ? 'text' : 'password'"
                       name="password"
                       required
                       autocomplete="current-password"
                       value="password123"
                       placeholder="••••••••"
                       class="w-full pl-10 pr-10 py-2.5 rounded-xl text-xs bg-slate-950/60 border @error('password') border-rose-500 ring-1 ring-rose-500 @else border-slate-700/80 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 @enderror text-white placeholder-slate-500 transition outline-none" />
                <button type="button"
                        @click="showPass = !showPass"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300 transition">
                    <svg x-show="!showPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    <svg x-show="showPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                </button>
            </div>
            @error('password')
                <p class="mt-1.5 text-xs text-rose-400 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remember Me Checkbox -->
        <div class="flex items-center justify-between text-xs pt-1">
            <label class="flex items-center gap-2 cursor-pointer select-none">
                <input type="checkbox"
                       name="remember"
                       id="remember"
                       value="1"
                       {{ old('remember') ? 'checked' : '' }}
                       class="w-4 h-4 rounded bg-slate-950 border-slate-700 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-900" />
                <span class="text-slate-300 font-medium">Ingat sesi saya</span>
            </label>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button type="submit"
                    :disabled="submitting"
                    class="w-full py-2.5 px-4 rounded-xl font-semibold text-xs text-white bg-indigo-600 hover:bg-indigo-500 active:scale-[0.99] focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-900 shadow-lg shadow-indigo-600/30 transition flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                <svg x-show="submitting" class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24" style="display: none;">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span x-text="submitting ? 'Memproses Masuk...' : 'Masuk ke Sistem ERP'">Masuk ke Sistem ERP</span>
            </button>
        </div>
    </form>

    <!-- Quick Demo Credentials Hint -->
    <div class="mt-6 p-3.5 rounded-xl bg-indigo-950/40 border border-indigo-900/50 text-[11px] text-indigo-300">
        <div class="flex items-center gap-2 font-bold text-indigo-200">
            <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
            </svg>
            <span>Kredensial Super Admin Awal:</span>
        </div>
        <div class="font-mono mt-1 text-[10px] text-indigo-300/80 space-y-0.5">
            <div>Email: <span class="text-white">admin@pt-management.test</span></div>
            <div>Password: <span class="text-white">password123</span></div>
        </div>
    </div>
</div>
@endsection
