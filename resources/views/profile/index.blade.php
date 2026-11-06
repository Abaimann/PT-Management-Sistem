@extends('layouts.app')

@section('title', 'Profil Pengguna & Keamanan')
@section('page_title', 'Profil Saya')

@section('breadcrumbs')
    <span class="text-slate-300">/</span>
    <span class="text-slate-800 font-semibold">Profil &amp; Keamanan Akun</span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Page Title Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900">Profil &amp; Pengaturan Keamanan</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola data informasi identitas akun dan perbarui kata sandi Anda secara berkala.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Akun Terverifikasi Aktif</span>
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column: Identity Overview Card -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                <div class="text-center pb-6 border-b border-slate-100">
                    <div class="w-20 h-20 mx-auto rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-blue-500 text-white font-extrabold text-2xl flex items-center justify-center shadow-lg shadow-indigo-600/20 ring-4 ring-indigo-50">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    <h2 class="mt-4 text-base font-bold text-slate-900">{{ $user->name }}</h2>
                    <p class="text-xs text-slate-500 truncate">{{ $user->email }}</p>

                    <!-- Assigned Roles -->
                    <div class="mt-3 flex flex-wrap justify-center gap-1.5">
                        @forelse ($user->roles as $role)
                            <span class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                {{ $role->name }}
                            </span>
                        @empty
                            <span class="px-2 py-0.5 rounded text-xs bg-slate-100 text-slate-600">Pengguna Biasa</span>
                        @endforelse
                    </div>
                </div>

                <!-- Detail Metadata -->
                <div class="pt-5 space-y-3 text-xs">
                    <div class="flex items-center justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-400">Status Keanggotaan</span>
                        <span class="font-semibold text-emerald-600 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            Aktif
                        </span>
                    </div>

                    @if ($user->employee)
                        <div class="flex items-center justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-400">Nomor Induk Karyawan</span>
                            <span class="font-mono font-medium text-slate-700">{{ $user->employee->employee_code ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-400">Departemen</span>
                            <span class="font-medium text-slate-800">{{ $user->employee->department?->name ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-400">Jabatan</span>
                            <span class="font-medium text-slate-800">{{ $user->employee->position?->name ?? '-' }}</span>
                        </div>
                    @endif

                    @if ($user->intern)
                        <div class="flex items-center justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-400">Nomor Induk Magang</span>
                            <span class="font-mono font-medium text-slate-700">{{ $user->intern->intern_code ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-400">Institusi / Kampus</span>
                            <span class="font-medium text-slate-800">{{ $user->intern->institution_name ?? '-' }}</span>
                        </div>
                    @endif

                    <div class="flex items-center justify-between py-1">
                        <span class="text-slate-400">Terdaftar Sejak</span>
                        <span class="text-slate-600 font-mono text-[11px]">{{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Security Notice Banner -->
            <div class="bg-indigo-950 text-indigo-200 rounded-2xl p-5 text-xs shadow-md space-y-2 border border-indigo-900">
                <div class="flex items-center gap-2 font-bold text-white">
                    <svg class="w-4 h-4 text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                    </svg>
                    <span>Kebijakan Keamanan Sesi</span>
                </div>
                <p class="text-indigo-300 leading-relaxed text-[11px]">
                    Sesi ERP diamankan dengan enkripsi HTTPS, hashing bcrypt, serta audit jejak aktivitas (actor ID &amp; IP address). Jangan pernah memberikan kredensial Anda kepada siapapun.
                </p>
            </div>
        </div>

        <!-- Right Column: Edit Profile & Password Forms -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Card 1: Update Profile Details -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                <div class="flex items-center gap-3 pb-4 mb-5 border-b border-slate-100">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Informasi Pribadi &amp; Kontak</h2>
                        <p class="text-xs text-slate-500">Perbarui nama tampilan dan alamat email resmi Anda.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Full Name -->
                        <div>
                            <label for="name" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Nama Lengkap <span class="text-rose-500">*</span>
                            </label>
                            <input type="text"
                                   name="name"
                                   id="name"
                                   value="{{ old('name', $user->name) }}"
                                   required
                                   class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 border @error('name') border-rose-500 @else border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 @enderror text-slate-900 placeholder-slate-400 outline-none transition" />
                            @error('name')
                                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email Address -->
                        <div>
                            <label for="profile_email" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Alamat Email <span class="text-rose-500">*</span>
                            </label>
                            <input type="email"
                                   name="email"
                                   id="profile_email"
                                   value="{{ old('email', $user->email) }}"
                                   required
                                   class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 border @error('email') border-rose-500 @else border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 @enderror text-slate-900 placeholder-slate-400 outline-none transition" />
                            @error('email')
                                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    @if ($user->employee)
                        <!-- Phone Number -->
                        <div>
                            <label for="phone" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Nomor Telepon / WhatsApp
                            </label>
                            <input type="text"
                                   name="phone"
                                   id="phone"
                                   value="{{ old('phone', $user->employee->phone) }}"
                                   placeholder="0812xxxxxxxx"
                                   class="w-full sm:w-1/2 px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 border @error('phone') border-rose-500 @else border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 @enderror text-slate-900 placeholder-slate-400 outline-none transition" />
                            @error('phone')
                                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <div class="pt-3 flex justify-end">
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 active:scale-[0.99] shadow-md shadow-indigo-600/20 transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            <span>Simpan Perubahan Profil</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Card 2: Update Password -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                <div class="flex items-center gap-3 pb-4 mb-5 border-b border-slate-100">
                    <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Perbarui Kata Sandi</h2>
                        <p class="text-xs text-slate-500">Pastikan akun Anda menggunakan kata sandi acak dan aman minimal 8 karakter.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <!-- Current Password -->
                    <div>
                        <label for="current_password" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Kata Sandi Saat Ini <span class="text-rose-500">*</span>
                        </label>
                        <input type="password"
                               name="current_password"
                               id="current_password"
                               required
                               autocomplete="current-password"
                               placeholder="••••••••"
                               class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 border @error('current_password') border-rose-500 @else border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 @enderror text-slate-900 placeholder-slate-400 outline-none transition" />
                        @error('current_password')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- New Password -->
                        <div>
                            <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Kata Sandi Baru <span class="text-rose-500">*</span>
                            </label>
                            <input type="password"
                                   name="password"
                                   id="password"
                                   required
                                   autocomplete="new-password"
                                   placeholder="Min. 8 karakter"
                                   class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 border @error('password') border-rose-500 @else border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 @enderror text-slate-900 placeholder-slate-400 outline-none transition" />
                            @error('password')
                                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Confirm New Password -->
                        <div>
                            <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Konfirmasi Kata Sandi Baru <span class="text-rose-500">*</span>
                            </label>
                            <input type="password"
                                   name="password_confirmation"
                                   id="password_confirmation"
                                   required
                                   autocomplete="new-password"
                                   placeholder="Ulangi kata sandi baru"
                                   class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-slate-900 placeholder-slate-400 outline-none transition" />
                        </div>
                    </div>

                    <div class="pt-3 flex justify-end">
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 active:scale-[0.99] shadow-md transition flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>
                            <span>Perbarui Kata Sandi</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection
