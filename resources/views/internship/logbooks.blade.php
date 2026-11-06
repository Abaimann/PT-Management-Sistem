@extends('layouts.app')

@section('title', 'Logbook Aktivitas Harian Magang')
@section('page_title', 'Logbook Magang')

@section('breadcrumbs')
    <span class="text-slate-300">/</span>
    <a href="{{ route('internship.index') }}" class="hover:text-indigo-600 transition">Program Magang</a>
    <span class="text-slate-300">/</span>
    <span class="text-slate-800 font-semibold">Logbook Aktivitas</span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Logbook Aktivitas Harian Magang</h1>
            <p class="text-xs text-slate-500 mt-0.5">Pencatatan rincian pekerjaan harian peserta magang dan verifikasi oleh pembimbing lapangan.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('internship.index') }}"
               class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                Daftar Peserta
            </a>
        </div>
    </div>

    <!-- Top Section: Daily Logbook Input Form (For Interns & Admin) -->
    @if ($currentIntern || $isMentorOrAdmin)
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
            <div class="pb-3 border-b border-slate-100 mb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                        <span>Form Pengisian Logbook Aktivitas Hari Ini</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Catat apa yang Anda kerjakan, teknologi yang dipelajari, serta tautan bukti hasil kerja.</p>
                </div>
                @if ($currentIntern)
                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        {{ $currentIntern->full_name }} ({{ $currentIntern->intern_code }})
                    </span>
                @endif
            </div>

            <form action="{{ route('internship.logbooks.store') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Admin selector if not logged in as intern -->
                @if (! $currentIntern && $isMentorOrAdmin)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="intern_id" class="block text-xs font-semibold text-slate-700 mb-1">
                                Pilih Peserta Magang <span class="text-rose-500">*</span>
                            </label>
                            <select name="intern_id" id="intern_id" required class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-500">
                                <option value="">-- Pilih Peserta --</option>
                                @foreach ($activeInterns as $internOption)
                                    <option value="{{ $internOption->id }}" {{ old('intern_id') == $internOption->id ? 'selected' : '' }}>
                                        {{ $internOption->full_name }} ({{ $internOption->intern_code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="date" class="block text-xs font-semibold text-slate-700 mb-1">
                                Tanggal Pelaksanaan <span class="text-rose-500">*</span>
                            </label>
                            <input type="date"
                                   name="date"
                                   id="date"
                                   value="{{ old('date', date('Y-m-d')) }}"
                                   required
                                   class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-500" />
                        </div>
                    </div>
                @else
                    <div class="w-full sm:w-1/3">
                        <label for="date" class="block text-xs font-semibold text-slate-700 mb-1">
                            Tanggal Pelaksanaan <span class="text-rose-500">*</span>
                        </label>
                        <input type="date"
                               name="date"
                               id="date"
                               value="{{ old('date', date('Y-m-d')) }}"
                               required
                               class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-500" />
                    </div>
                @endif

                <!-- Activity Description -->
                <div>
                    <label for="activity_description" class="block text-xs font-semibold text-slate-700 mb-1">
                        Deskripsi Kegiatan &amp; Hasil Kerja <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="activity_description"
                              id="activity_description"
                              rows="4"
                              required
                              placeholder="Deskripsikan pekerjaan hari ini, fitur/modul yang dikerjakan, kendala yang dihadapi, dan solusi yang diterapkan..."
                              class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-500 outline-none">{{ old('activity_description') }}</textarea>
                    @error('activity_description')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Output URL -->
                <div>
                    <label for="output_url" class="block text-xs font-semibold text-slate-700 mb-1">
                        Link URL Hasil Kerja / Repository / Drive (Opsional)
                    </label>
                    <input type="url"
                           name="output_url"
                           id="output_url"
                           value="{{ old('output_url') }}"
                           placeholder="https://github.com/... atau https://docs.google.com/..."
                           class="w-full px-3.5 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-500 outline-none font-mono text-[11px]" />
                    @error('output_url')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                        </svg>
                        <span>Kirim Catatan Logbook</span>
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Middle Section: Pending Verifications Queue (For Mentors / Supervisors) -->
    @if ($isMentorOrAdmin && $pendingVerifications->isNotEmpty())
        <div class="bg-amber-50/40 rounded-2xl border border-amber-200 p-6 space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-amber-200">
                <div>
                    <h3 class="text-sm font-bold text-amber-900 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                        <span>Antrean Verifikasi Logbook Menunggu Pembimbing</span>
                    </h3>
                    <p class="text-xs text-amber-700 mt-0.5">Terdapat {{ $pendingVerifications->count() }} catatan aktivitas yang perlu diverifikasi.</p>
                </div>
            </div>

            <div class="space-y-3">
                @foreach ($pendingVerifications as $pending)
                    <div class="p-4 rounded-xl bg-white border border-amber-200 shadow-xs space-y-3 text-xs">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900">{{ $pending->intern?->full_name }}</span>
                                <span class="text-slate-400 font-mono text-[11px]">({{ $pending->intern?->intern_code }})</span>
                                <span class="text-slate-300">&bull;</span>
                                <span class="font-mono text-indigo-600 font-semibold">{{ $pending->date->format('d M Y') }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 self-start sm:self-auto">
                                Menunggu Verifikasi
                            </span>
                        </div>

                        <p class="text-slate-700 leading-relaxed bg-slate-50 p-3 rounded-lg border border-slate-100">
                            {{ $pending->activity_description }}
                        </p>

                        @if ($pending->output_url)
                            <div>
                                <a href="{{ $pending->output_url }}" target="_blank" rel="noopener noreferrer"
                                   class="text-indigo-600 hover:underline font-mono text-[11px] inline-flex items-center gap-1">
                                    <span>Bukti Hasil: {{ $pending->output_url }}</span>
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 21 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                    </svg>
                                </a>
                            </div>
                        @endif

                        <!-- Action Form -->
                        <form action="{{ route('internship.logbooks.verify', $pending) }}" method="POST" class="pt-2 border-t border-slate-100 flex flex-col sm:flex-row items-center gap-2">
                            @csrf
                            @method('PATCH')

                            <input type="text"
                                   name="verification_notes"
                                   placeholder="Catatan verifikator (opsional / wajib jika tolak)..."
                                   class="flex-1 px-3 py-1.5 text-xs rounded-lg bg-slate-50 border border-slate-200 focus:outline-none" />

                            <div class="flex items-center gap-2 shrink-0 w-full sm:w-auto">
                                <button type="submit"
                                        name="status"
                                        value="VERIFIED"
                                        class="flex-1 sm:flex-initial px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition flex items-center justify-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                    <span>Setujui</span>
                                </button>
                                <button type="submit"
                                        name="status"
                                        value="REJECTED"
                                        class="flex-1 sm:flex-initial px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs transition flex items-center justify-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                    </svg>
                                    <span>Tolak</span>
                                </button>
                            </div>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Bottom Section: History Logbooks Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h3 class="text-sm font-bold text-slate-900">Riwayat Seluruh Catatan Logbook</h3>

            <!-- Filter Controls -->
            <form action="{{ route('internship.logbooks') }}" method="GET" class="flex flex-wrap items-center gap-2">
                @if ($isMentorOrAdmin && ! $currentIntern)
                    <select name="intern_id" class="px-2.5 py-1.5 text-xs rounded-xl bg-slate-50 border border-slate-200">
                        <option value="">Semua Peserta</option>
                        @foreach ($activeInterns as $internOption)
                            <option value="{{ $internOption->id }}" {{ request('intern_id') == $internOption->id ? 'selected' : '' }}>
                                {{ $internOption->full_name }}
                            </option>
                        @endforeach
                    </select>
                @endif

                <select name="status" class="px-2.5 py-1.5 text-xs rounded-xl bg-slate-50 border border-slate-200">
                    <option value="">Semua Status</option>
                    <option value="SUBMITTED" {{ request('status') === 'SUBMITTED' ? 'selected' : '' }}>Menunggu</option>
                    <option value="VERIFIED" {{ request('status') === 'VERIFIED' ? 'selected' : '' }}>Terverifikasi</option>
                    <option value="REJECTED" {{ request('status') === 'REJECTED' ? 'selected' : '' }}>Ditolak</option>
                </select>

                <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-semibold">
                    Filter
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200/80 text-[11px]">
                    <tr>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Peserta Magang</th>
                        <th class="py-3 px-4">Aktivitas Pekerjaan</th>
                        <th class="py-3 px-4">Output Bukti</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4">Verifikator &amp; Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($logbooks as $logbook)
                        <tr class="hover:bg-slate-50/60 transition">
                            <!-- Date -->
                            <td class="py-3 px-4 font-mono font-bold text-slate-900 whitespace-nowrap">
                                {{ $logbook->date->format('d/m/Y') }}
                            </td>

                            <!-- Intern -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="font-bold text-slate-800 block">{{ $logbook->intern?->full_name }}</span>
                                <span class="text-[10px] text-slate-400 font-mono block">{{ $logbook->intern?->intern_code }}</span>
                            </td>

                            <!-- Activity Description -->
                            <td class="py-3 px-4 max-w-md">
                                <p class="text-slate-700 leading-relaxed">{{ $logbook->activity_description }}</p>
                            </td>

                            <!-- Output URL -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if ($logbook->output_url)
                                    <a href="{{ $logbook->output_url }}" target="_blank" rel="noopener noreferrer"
                                       class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 text-[11px] font-semibold hover:underline inline-flex items-center gap-1">
                                        <span>Buka Link</span>
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 21 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                        </svg>
                                    </a>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                @if ($logbook->status === 'VERIFIED')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Terverifikasi
                                    </span>
                                @elseif ($logbook->status === 'REJECTED')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Ditolak
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Menunggu
                                    </span>
                                @endif
                            </td>

                            <!-- Verifier -->
                            <td class="py-3 px-4 max-w-xs">
                                @if ($logbook->verifiedBy)
                                    <span class="font-semibold text-slate-800 block">{{ $logbook->verifiedBy->full_name }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono block">{{ $logbook->verified_at?->format('d M H:i') }}</span>
                                    @if ($logbook->verification_notes)
                                        <span class="text-[11px] text-slate-600 italic block mt-0.5">"{{ $logbook->verification_notes }}"</span>
                                    @endif
                                @else
                                    <span class="text-slate-400 italic">Belum diverifikasi</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-400 italic">
                                Belum ada riwayat catatan logbook.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logbooks->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $logbooks->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
