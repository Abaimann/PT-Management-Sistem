@extends('layouts.app')

@section('title', 'Dossier Peserta Magang: ' . $intern->full_name)
@section('page_title', 'Dossier Magang')

@section('breadcrumbs')
    <span class="text-slate-300">/</span>
    <a href="{{ route('internship.index') }}" class="hover:text-indigo-600 transition">Program Magang</a>
    <span class="text-slate-300">/</span>
    <span class="text-slate-800 font-semibold truncate max-w-[200px]">{{ $intern->full_name }}</span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="font-mono text-xs px-2.5 py-0.5 rounded-lg bg-indigo-50 text-indigo-700 font-bold border border-indigo-200">
                    {{ $intern->intern_code }}
                </span>
                @if ($intern->status === 'ACTIVE')
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Aktif
                    </span>
                @elseif ($intern->status === 'COMPLETED')
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                        Selesai Magang
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600">
                        {{ $intern->status }}
                    </span>
                @endif
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">{{ $intern->full_name }}</h1>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('internship.logbooks', ['intern_id' => $intern->id]) }}"
               class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition shadow-xs flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
                <span>Lihat Semua Logbook</span>
            </a>

            <a href="{{ route('internship.index') }}"
               class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                Kembali
            </a>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Col: Profile & Dossier Info -->
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                <div class="text-center pb-5 border-b border-slate-100">
                    <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-blue-500 text-white font-extrabold text-xl flex items-center justify-center shadow-lg shadow-indigo-600/20 ring-4 ring-indigo-50">
                        {{ strtoupper(substr($intern->full_name, 0, 2)) }}
                    </div>
                    <h2 class="mt-3 text-base font-bold text-slate-900">{{ $intern->full_name }}</h2>
                    <p class="text-xs text-slate-500">{{ $intern->institution }}</p>
                    <span class="inline-block mt-1 text-[11px] font-medium text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">
                        {{ $intern->major ?: 'Umum' }}
                    </span>
                </div>

                <div class="pt-4 space-y-3 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Departemen Penempatan</span>
                        <span class="font-semibold text-slate-800 block mt-0.5">{{ $intern->department?->name ?? '-' }}</span>
                    </div>

                    <div class="pt-2 border-t border-slate-50">
                        <span class="text-slate-400 block text-[11px]">Pembimbing (Mentor)</span>
                        <span class="font-semibold text-slate-800 block mt-0.5">{{ $intern->mentor?->full_name ?? '-' }}</span>
                        <span class="text-[10px] text-slate-400 block">{{ $intern->mentor?->position?->name ?? '' }}</span>
                    </div>

                    <div class="pt-2 border-t border-slate-50">
                        <span class="text-slate-400 block text-[11px]">Periode Magang</span>
                        <span class="font-mono text-slate-700 block mt-0.5">
                            {{ $intern->start_date ? $intern->start_date->format('d M Y') : '-' }} s/d {{ $intern->end_date ? $intern->end_date->format('d M Y') : '-' }}
                        </span>
                    </div>

                    <div class="pt-2 border-t border-slate-50">
                        <span class="text-slate-400 block text-[11px]">Kontak WhatsApp</span>
                        <span class="font-mono text-slate-700 block mt-0.5">{{ $intern->phone ?: '-' }}</span>
                    </div>

                    <div class="pt-2 border-t border-slate-50">
                        <span class="text-slate-400 block text-[11px]">Akun Login Sistem</span>
                        <span class="text-slate-700 block mt-0.5">{{ $intern->user?->email ?: 'Belum memiliki akun login mandiri' }}</span>
                    </div>
                </div>
            </div>

            <!-- Logbook Summary Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-3">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider pb-2 border-b border-slate-100">
                    Rekap Aktivitas Logbook
                </h3>

                <div class="grid grid-cols-2 gap-2 text-center text-xs">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] text-slate-500 block">Total Diisi</span>
                        <span class="text-lg font-bold text-slate-900 mt-0.5 block">{{ $logbooksCount['total'] }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700">
                        <span class="text-[10px] block">Terverifikasi</span>
                        <span class="text-lg font-bold mt-0.5 block">{{ $logbooksCount['verified'] }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-amber-50 border border-amber-100 text-amber-700">
                        <span class="text-[10px] block">Menunggu</span>
                        <span class="text-lg font-bold mt-0.5 block">{{ $logbooksCount['submitted'] }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-rose-50 border border-rose-100 text-rose-700">
                        <span class="text-[10px] block">Ditolak</span>
                        <span class="text-lg font-bold mt-0.5 block">{{ $logbooksCount['rejected'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right 2 Cols: Evaluation Result & Form + Recent Logbooks -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Latest Evaluation Result (If Evaluated) -->
            @if ($latestEvaluation)
                <div class="bg-white rounded-2xl border border-emerald-200 shadow-xs p-6 space-y-4 bg-emerald-50/20">
                    <div class="flex items-center justify-between pb-3 border-b border-emerald-100">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Hasil Penilaian Akhir Magang</span>
                            <h3 class="text-base font-bold text-slate-900 mt-0.5">Nilai Kelulusan &amp; Evaluasi Kompetensi</h3>
                        </div>
                        <div class="text-right">
                            <span class="text-2xl font-black text-emerald-600 block">{{ number_format($latestEvaluation->final_score, 1) }}</span>
                            <span class="text-[10px] text-slate-400 block font-mono">Skor Rata-Rata</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center text-xs">
                        <div class="p-3 rounded-xl bg-white border border-slate-200">
                            <span class="text-[10px] text-slate-500 block">Kedisiplinan</span>
                            <span class="text-base font-bold text-slate-800 mt-0.5 block">{{ number_format($latestEvaluation->discipline_score, 1) }}</span>
                        </div>
                        <div class="p-3 rounded-xl bg-white border border-slate-200">
                            <span class="text-[10px] text-slate-500 block">Kompetensi Teknis</span>
                            <span class="text-base font-bold text-slate-800 mt-0.5 block">{{ number_format($latestEvaluation->technical_score, 1) }}</span>
                        </div>
                        <div class="p-3 rounded-xl bg-white border border-slate-200">
                            <span class="text-[10px] text-slate-500 block">Kerja Sama Tim</span>
                            <span class="text-base font-bold text-slate-800 mt-0.5 block">{{ number_format($latestEvaluation->teamwork_score, 1) }}</span>
                        </div>
                        <div class="p-3 rounded-xl bg-white border border-slate-200">
                            <span class="text-[10px] text-slate-500 block">Komunikasi</span>
                            <span class="text-base font-bold text-slate-800 mt-0.5 block">{{ number_format($latestEvaluation->communication_score, 1) }}</span>
                        </div>
                    </div>

                    @if ($latestEvaluation->recommendation)
                        <div class="p-3.5 rounded-xl bg-white border border-slate-200 text-xs">
                            <span class="font-bold text-slate-700 block mb-1">Rekomendasi Pembimbing / Evaluator:</span>
                            <p class="text-slate-600 leading-relaxed">{{ $latestEvaluation->recommendation }}</p>
                        </div>
                    @endif

                    <div class="flex items-center justify-between text-xs text-slate-500 pt-2 border-t border-emerald-100 font-mono text-[11px]">
                        <span>No. Sertifikat: {{ $latestEvaluation->certificate_number ?: '-' }}</span>
                        <span>Dinilai oleh: {{ $latestEvaluation->evaluator?->full_name ?? 'Mentor' }} &bull; {{ $latestEvaluation->evaluated_at ? $latestEvaluation->evaluated_at->format('d M Y') : '-' }}</span>
                    </div>
                </div>
            @endif

            <!-- Evaluation Form (For Mentor / HR / Super Admin) -->
            @if ($isMentor || $isHRorAdmin)
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                    <div class="pb-3 border-b border-slate-100">
                        <h3 class="text-sm font-bold text-slate-900">
                            {{ $latestEvaluation ? 'Perbarui Form Penilaian Magang' : 'Form Penilaian Akhir Magang' }}
                        </h3>
                        <p class="text-xs text-slate-500">Berikan penilaian komprehensif atas kedisiplinan dan kontribusi peserta magang.</p>
                    </div>

                    <form action="{{ route('internship.evaluate', $intern) }}" method="POST" class="space-y-4">
                        @csrf

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <!-- Disiplin -->
                            <div>
                                <label for="discipline_score" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Disiplin (0-100) <span class="text-rose-500">*</span>
                                </label>
                                <input type="number"
                                       step="0.1"
                                       name="discipline_score"
                                       id="discipline_score"
                                       min="0"
                                       max="100"
                                       value="{{ old('discipline_score', $latestEvaluation?->discipline_score ?? 85) }}"
                                       required
                                       class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-500" />
                            </div>

                            <!-- Teknis -->
                            <div>
                                <label for="technical_score" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Teknis (0-100) <span class="text-rose-500">*</span>
                                </label>
                                <input type="number"
                                       step="0.1"
                                       name="technical_score"
                                       id="technical_score"
                                       min="0"
                                       max="100"
                                       value="{{ old('technical_score', $latestEvaluation?->technical_score ?? 85) }}"
                                       required
                                       class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-500" />
                            </div>

                            <!-- Teamwork -->
                            <div>
                                <label for="teamwork_score" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Teamwork (0-100) <span class="text-rose-500">*</span>
                                </label>
                                <input type="number"
                                       step="0.1"
                                       name="teamwork_score"
                                       id="teamwork_score"
                                       min="0"
                                       max="100"
                                       value="{{ old('teamwork_score', $latestEvaluation?->teamwork_score ?? 85) }}"
                                       required
                                       class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-500" />
                            </div>

                            <!-- Komunikasi -->
                            <div>
                                <label for="communication_score" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Komunikasi (0-100) <span class="text-rose-500">*</span>
                                </label>
                                <input type="number"
                                       step="0.1"
                                       name="communication_score"
                                       id="communication_score"
                                       min="0"
                                       max="100"
                                       value="{{ old('communication_score', $latestEvaluation?->communication_score ?? 85) }}"
                                       required
                                       class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-500" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Evaluated At -->
                            <div>
                                <label for="evaluated_at" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Tanggal Penilaian <span class="text-rose-500">*</span>
                                </label>
                                <input type="date"
                                       name="evaluated_at"
                                       id="evaluated_at"
                                       value="{{ old('evaluated_at', $latestEvaluation?->evaluated_at?->format('Y-m-d') ?? date('Y-m-d')) }}"
                                       required
                                       class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-500" />
                            </div>

                            <!-- Certificate Number -->
                            <div>
                                <label for="certificate_number" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Nomor Sertifikat (Opsional)
                                </label>
                                <input type="text"
                                       name="certificate_number"
                                       id="certificate_number"
                                       value="{{ old('certificate_number', $latestEvaluation?->certificate_number ?? ('CERT-INT-' . $intern->id . '/' . date('Y'))) }}"
                                       placeholder="Misal: CERT-INT-001/2026"
                                       class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-500" />
                            </div>
                        </div>

                        <!-- Recommendation Notes -->
                        <div>
                            <label for="recommendation" class="block text-xs font-semibold text-slate-700 mb-1">
                                Rekomendasi &amp; Catatan Khusus
                            </label>
                            <textarea name="recommendation"
                                      id="recommendation"
                                      rows="3"
                                      placeholder="Tuliskan catatan apresiasi, evaluasi sikap kerja, atau rekomendasi karir lanjutan..."
                                      class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-500">{{ old('recommendation', $latestEvaluation?->recommendation) }}</textarea>
                        </div>

                        <!-- Checkbox Completed -->
                        <div class="flex items-center gap-2">
                            <input type="checkbox"
                                   name="mark_completed"
                                   id="mark_completed"
                                   value="1"
                                   checked
                                   class="rounded bg-slate-100 border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                            <label for="mark_completed" class="text-xs font-semibold text-slate-700 cursor-pointer">
                                Tandai status peserta magang sebagai "Selesai (COMPLETED)"
                            </label>
                        </div>

                        <div class="pt-2 flex justify-end">
                            <button type="submit"
                                    class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md transition flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                                <span>Simpan Hasil Penilaian</span>
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Recent Logbooks Table -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900">Riwayat Catatan Logbook Harian</h3>
                    <a href="{{ route('internship.logbooks', ['intern_id' => $intern->id]) }}"
                       class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition">
                        Semua Catatan &rarr;
                    </a>
                </div>

                @if ($intern->logbooks->isEmpty())
                    <p class="text-xs text-slate-400 italic py-4 text-center">Belum ada catatan logbook yang diisi.</p>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($intern->logbooks->sortByDesc('date')->take(5) as $logbook)
                            <div class="py-3 space-y-1 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="font-mono font-bold text-slate-800">{{ $logbook->date->format('d M Y') }}</span>
                                    @if ($logbook->status === 'VERIFIED')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Terverifikasi
                                        </span>
                                    @elseif ($logbook->status === 'REJECTED')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            Ditolak
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            Menunggu
                                        </span>
                                    @endif
                                </div>
                                <p class="text-slate-600 leading-relaxed">{{ $logbook->activity_description }}</p>
                                @if ($logbook->output_url)
                                    <a href="{{ $logbook->output_url }}" target="_blank" rel="noopener noreferrer" class="text-indigo-600 hover:underline text-[11px] inline-flex items-center gap-1 font-mono">
                                        <span>Link Output Kerja</span>
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 21 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

    </div>

</div>
@endsection
