@extends('layouts.app')

@section('title', 'Pendaftaran Karyawan Baru')
@section('page_title', 'Tambah Karyawan')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Breadcrumb & Back -->
    <div>
        <a href="{{ route('employees.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-indigo-600 transition mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Direktori Karyawan
        </a>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Pendaftaran Karyawan Baru</h1>
        <p class="text-xs text-slate-500 mt-1">Lengkapi data profil kepegawaian, penempatan divisi kerja, dan akun akses sistem.</p>
    </div>

    <!-- Form Container -->
    <form action="{{ route('employees.store') }}" method="POST" class="space-y-6" x-data="{ createUser: false }">
        @csrf

        <!-- Section 1: Data Identitas Pribadi -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-5">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                    1. Identitas & Data Pribadi
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Informasi kependudukan dan kontak personal pegawai.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- NIK -->
                <div>
                    <label for="nik" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Nomor Induk Karyawan (NIK) <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="nik" 
                        id="nik" 
                        value="{{ old('nik') }}" 
                        required 
                        placeholder="Contoh: EMP-2026-001"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono uppercase text-slate-800 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                    >
                </div>

                <!-- Nama Lengkap -->
                <div>
                    <label for="full_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Nama Lengkap <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="full_name" 
                        id="full_name" 
                        value="{{ old('full_name') }}" 
                        required 
                        placeholder="Nama lengkap sesuai KTP"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                    >
                </div>

                <!-- Gender -->
                <div>
                    <label for="gender" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Jenis Kelamin <span class="text-rose-500">*</span>
                    </label>
                    <select 
                        name="gender" 
                        id="gender" 
                        required 
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                    >
                        <option value="">-- Pilih Jenis Kelamin --</option>
                        <option value="MALE" {{ old('gender') === 'MALE' ? 'selected' : '' }}>Laki-laki (MALE)</option>
                        <option value="FEMALE" {{ old('gender') === 'FEMALE' ? 'selected' : '' }}>Perempuan (FEMALE)</option>
                    </select>
                </div>

                <!-- Telepon -->
                <div>
                    <label for="phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Nomor Telepon / WhatsApp
                    </label>
                    <input 
                        type="text" 
                        name="phone" 
                        id="phone" 
                        value="{{ old('phone') }}" 
                        placeholder="+62 812-xxxx-xxxx"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                    >
                </div>

                <!-- Tempat Lahir -->
                <div>
                    <label for="birth_place" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Tempat Lahir
                    </label>
                    <input 
                        type="text" 
                        name="birth_place" 
                        id="birth_place" 
                        value="{{ old('birth_place') }}" 
                        placeholder="Kota tempat lahir"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                    >
                </div>

                <!-- Tanggal Lahir -->
                <div>
                    <label for="birth_date" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Tanggal Lahir <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        name="birth_date" 
                        id="birth_date" 
                        value="{{ old('birth_date') }}" 
                        required 
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                    >
                </div>
            </div>

            <!-- Alamat -->
            <div>
                <label for="address" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Alamat Domisili Lengkap
                </label>
                <textarea 
                    name="address" 
                    id="address" 
                    rows="2" 
                    placeholder="Alamat jalan, nomor rumah, RT/RW, kelurahan, kecamatan, dan kota..."
                    class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                >{{ old('address') }}</textarea>
            </div>
        </div>

        <!-- Section 2: Struktur Organisasi & Pekerjaan -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-5">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                    2. Struktur Posisi & Kepegawaian
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Penugasan divisi, hirarki jabatan, dan kontrak kerja.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Departemen -->
                <div>
                    <label for="department_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Departemen / Divisi <span class="text-rose-500">*</span>
                    </label>
                    <select 
                        name="department_id" 
                        id="department_id" 
                        required 
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                    >
                        <option value="">-- Pilih Departemen --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }} ({{ $dept->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Jabatan -->
                <div>
                    <label for="position_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Jabatan & Grade <span class="text-rose-500">*</span>
                    </label>
                    <select 
                        name="position_id" 
                        id="position_id" 
                        required 
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                    >
                        <option value="">-- Pilih Jabatan --</option>
                        @foreach($positions as $pos)
                            <option value="{{ $pos->id }}" {{ old('position_id') == $pos->id ? 'selected' : '' }}>
                                {{ $pos->name }} ({{ $pos->department?->code }} - {{ $pos->grade_level }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Atasan Langsung (Supervisor) -->
                <div>
                    <label for="supervisor_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Atasan Langsung (Supervisor / Lead)
                    </label>
                    <select 
                        name="supervisor_id" 
                        id="supervisor_id" 
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                    >
                        <option value="">-- Tanpa Atasan Langsung (Puncak Struktur) --</option>
                        @foreach($supervisors as $spv)
                            <option value="{{ $spv->id }}" {{ old('supervisor_id') == $spv->id ? 'selected' : '' }}>
                                {{ $spv->full_name }} ({{ $spv->position?->name ?? 'Pegawai' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Kepegawaian -->
                <div>
                    <label for="employment_status" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Status Kepegawaian <span class="text-rose-500">*</span>
                    </label>
                    <select 
                        name="employment_status" 
                        id="employment_status" 
                        required 
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                    >
                        <option value="PERMANENT" {{ old('employment_status', 'PERMANENT') === 'PERMANENT' ? 'selected' : '' }}>Tetap (PKWTT / Permanent)</option>
                        <option value="CONTRACT" {{ old('employment_status') === 'CONTRACT' ? 'selected' : '' }}>Kontrak (PKWT / Contract)</option>
                        <option value="PROBATION" {{ old('employment_status') === 'PROBATION' ? 'selected' : '' }}>Masa Percobaan (Probation)</option>
                        <option value="INTERN" {{ old('employment_status') === 'INTERN' ? 'selected' : '' }}>Magang (Internship)</option>
                    </select>
                </div>

                <!-- Tanggal Bergabung -->
                <div>
                    <label for="join_date" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Tanggal Mulai Bergabung <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        name="join_date" 
                        id="join_date" 
                        value="{{ old('join_date', date('Y-m-d')) }}" 
                        required 
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                    >
                </div>

                <!-- Tanggal Berakhir / Resign -->
                <div>
                    <label for="resign_date" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Tanggal Berakhir Kontrak (Opsional)
                    </label>
                    <input 
                        type="date" 
                        name="resign_date" 
                        id="resign_date" 
                        value="{{ old('resign_date') }}" 
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                    >
                </div>
            </div>

            <!-- Status Aktif Pegawai -->
            <div class="pt-2">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input 
                        type="checkbox" 
                        name="is_active" 
                        value="1" 
                        {{ old('is_active', '1') ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                    >
                    <div>
                        <span class="text-xs font-bold text-slate-800">Pegawai Berstatus Aktif</span>
                        <p class="text-[11px] text-slate-500">Karyawan aktif berhak atas absensi harian, penugasan tugas harian, dan kalkulasi gaji.</p>
                    </div>
                </label>
            </div>
        </div>

        <!-- Section 3: Akun Pengguna ERP (Opsional) -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-5">
            <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                        3. Akun Akses Portal ERP
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Pembuatan akun login untuk akses fitur dashboard, absensi selfie, dan tugas.</p>
                </div>

                <label class="flex items-center gap-2 cursor-pointer bg-slate-100 px-3 py-1.5 rounded-xl border border-slate-200">
                    <input 
                        type="checkbox" 
                        name="create_user_account" 
                        value="1" 
                        x-model="createUser"
                        class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                    >
                    <span class="text-xs font-bold text-slate-700">Aktifkan Akun Login</span>
                </label>
            </div>

            <div x-show="createUser" x-transition class="space-y-4 pt-2">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Email Pengguna -->
                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Alamat Email Pengguna
                        </label>
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            value="{{ old('email') }}" 
                            placeholder="nama@perusahaan.co.id"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                        >
                    </div>

                    <!-- Password Pengguna -->
                    <div>
                        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Kata Sandi Default
                        </label>
                        <input 
                            type="password" 
                            name="password" 
                            id="password" 
                            placeholder="Minimal 8 karakter"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                        >
                    </div>

                    <!-- Peran Sistem (Role) -->
                    <div>
                        <label for="role_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Hak Akses Role Sistem
                        </label>
                        <select 
                            name="role_id" 
                            id="role_id" 
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-indigo-500 focus:bg-white transition"
                        >
                            <option value="">-- Pilih Role Sistem --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }} ({{ $role->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Submit Buttons -->
        <div class="flex items-center justify-end gap-3 pt-4">
            <a href="{{ route('employees.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/20 transition">
                Daftarkan Karyawan
            </button>
        </div>
    </form>

</div>
@endsection
