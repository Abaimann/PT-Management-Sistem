@extends('layouts.app')

@section('title', 'Pendaftaran Peserta Magang Baru')
@section('page_title', 'Daftar Magang')

@section('breadcrumbs')
    <span class="text-slate-300">/</span>
    <a href="{{ route('internship.index') }}" class="hover:text-indigo-600 transition">Program Magang</a>
    <span class="text-slate-300">/</span>
    <span class="text-slate-800 font-semibold">Pendaftaran Baru</span>
@endsection

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="pb-2 border-b border-slate-200">
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Pendaftaran Peserta Magang Baru</h1>
        <p class="text-xs text-slate-500 mt-0.5">Daftarkan identitas mahasiswa/siswa magang, tentukan pembimbing (mentor), dan masa periode magang.</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
        <form action="{{ route('internship.store') }}" method="POST" class="space-y-5" x-data="{ makeAccount: false }">
            @csrf

            <!-- Section 1: Identitas Pribadi & Akademik -->
            <div class="space-y-4">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider pb-1 border-b border-slate-100">
                    Identitas &amp; Asal Institusi
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Full Name -->
                    <div>
                        <label for="full_name" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Nama Lengkap Peserta <span class="text-rose-500">*</span>
                        </label>
                        <input type="text"
                               name="full_name"
                               id="full_name"
                               value="{{ old('full_name') }}"
                               required
                               placeholder="Nama lengkap sesuai KTM/KTP"
                               class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 border @error('full_name') border-rose-500 @else border-slate-200 focus:border-indigo-500 @enderror text-slate-900 outline-none transition" />
                        @error('full_name')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Nomor WhatsApp / HP
                        </label>
                        <input type="text"
                               name="phone"
                               id="phone"
                               value="{{ old('phone') }}"
                               placeholder="0812xxxxxxxx"
                               class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 border @error('phone') border-rose-500 @else border-slate-200 focus:border-indigo-500 @enderror text-slate-900 outline-none transition" />
                        @error('phone')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Institution -->
                    <div>
                        <label for="institution" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Asal Universitas / Sekolah <span class="text-rose-500">*</span>
                        </label>
                        <input type="text"
                               name="institution"
                               id="institution"
                               value="{{ old('institution') }}"
                               required
                               placeholder="Contoh: Universitas Indonesia / SMKN 1"
                               class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 border @error('institution') border-rose-500 @else border-slate-200 focus:border-indigo-500 @enderror text-slate-900 outline-none transition" />
                        @error('institution')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Major -->
                    <div>
                        <label for="major" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Program Studi / Jurusan
                        </label>
                        <input type="text"
                               name="major"
                               id="major"
                               value="{{ old('major') }}"
                               placeholder="Contoh: Teknik Informatika / Manajemen"
                               class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 border @error('major') border-rose-500 @else border-slate-200 focus:border-indigo-500 @enderror text-slate-900 outline-none transition" />
                        @error('major')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Section 2: Penempatan & Mentor -->
            <div class="space-y-4 pt-2">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider pb-1 border-b border-slate-100">
                    Penempatan Divisi &amp; Pembimbing Lapangan
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Department -->
                    <div>
                        <label for="department_id" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Departemen Penempatan <span class="text-rose-500">*</span>
                        </label>
                        <select name="department_id"
                                id="department_id"
                                required
                                class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 border @error('department_id') border-rose-500 @else border-slate-200 focus:border-indigo-500 @enderror text-slate-900 outline-none transition">
                            <option value="">-- Pilih Departemen --</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Mentor -->
                    <div>
                        <label for="mentor_id" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Pembimbing (Mentor Karyawan) <span class="text-rose-500">*</span>
                        </label>
                        <select name="mentor_id"
                                id="mentor_id"
                                required
                                class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 border @error('mentor_id') border-rose-500 @else border-slate-200 focus:border-indigo-500 @enderror text-slate-900 outline-none transition">
                            <option value="">-- Pilih Pembimbing --</option>
                            @foreach ($mentors as $mentor)
                                <option value="{{ $mentor->id }}" {{ old('mentor_id') == $mentor->id ? 'selected' : '' }}>
                                    {{ $mentor->full_name }} ({{ $mentor->department?->name ?? 'Dept' }} - {{ $mentor->position?->name ?? 'Posisi' }})
                                </option>
                            @endforeach
                        </select>
                        @error('mentor_id')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Start Date -->
                    <div>
                        <label for="start_date" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Tanggal Mulai Magang <span class="text-rose-500">*</span>
                        </label>
                        <input type="date"
                               name="start_date"
                               id="start_date"
                               value="{{ old('start_date') }}"
                               required
                               class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 border @error('start_date') border-rose-500 @else border-slate-200 focus:border-indigo-500 @enderror text-slate-900 outline-none transition" />
                        @error('start_date')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- End Date -->
                    <div>
                        <label for="end_date" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Tanggal Selesai Magang <span class="text-rose-500">*</span>
                        </label>
                        <input type="date"
                               name="end_date"
                               id="end_date"
                               value="{{ old('end_date') }}"
                               required
                               class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-slate-50 border @error('end_date') border-rose-500 @else border-slate-200 focus:border-indigo-500 @enderror text-slate-900 outline-none transition" />
                        @error('end_date')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Section 3: Akun Pengguna ERP (Opsional) -->
            <div class="space-y-4 pt-2">
                <div class="flex items-center justify-between pb-1 border-b border-slate-100">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        Akses Login Portal ERP Mandiri
                    </h3>
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-indigo-600">
                        <input type="checkbox"
                               name="create_user_account"
                               value="1"
                               x-model="makeAccount"
                               class="rounded bg-slate-100 border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                        <span>Buatkan Akun Login Magang</span>
                    </label>
                </div>

                <div x-show="makeAccount" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">
                            Email Login Magang <span class="text-rose-500">*</span>
                        </label>
                        <input type="email"
                               name="email"
                               id="email"
                               value="{{ old('email') }}"
                               placeholder="peserta.magang@kampus.ac.id"
                               class="w-full px-3 py-2 text-xs rounded-xl bg-white border border-slate-200 focus:border-indigo-500 outline-none" />
                        @error('email')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-700 mb-1">
                            Kata Sandi Awal <span class="text-rose-500">*</span>
                        </label>
                        <input type="password"
                               name="password"
                               id="password"
                               placeholder="Min. 8 karakter"
                               class="w-full px-3 py-2 text-xs rounded-xl bg-white border border-slate-200 focus:border-indigo-500 outline-none" />
                        @error('password')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Buttons -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('internship.index') }}"
                   class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                    Batal
                </a>
                <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/20 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    <span>Daftarkan Peserta</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
