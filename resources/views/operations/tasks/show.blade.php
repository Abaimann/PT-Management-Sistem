@extends('layouts.app')

@section('title', 'Detail Tugas: ' . $task->title)
@section('page_title', 'Detail Tugas')

@section('breadcrumbs')
    <span class="text-slate-300">/</span>
    <a href="{{ route('tasks.index') }}" class="hover:text-indigo-600 transition">Tugas Operasional</a>
    <span class="text-slate-300">/</span>
    <span class="text-slate-800 font-semibold truncate max-w-[200px]">{{ $task->title }}</span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <!-- Priority Badge -->
                @if ($task->priority === 'URGENT')
                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200 uppercase">
                        🔥 Urgent
                    </span>
                @elseif ($task->priority === 'HIGH')
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 uppercase">
                        High
                    </span>
                @elseif ($task->priority === 'MEDIUM')
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 uppercase">
                        Medium
                    </span>
                @else
                    <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600 uppercase">
                        Low
                    </span>
                @endif

                <!-- Status Badge -->
                @if ($task->status === 'APPROVED')
                    <span class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Disetujui (Approved)
                    </span>
                @elseif ($task->status === 'WAITING_VERIFICATION')
                    <span class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200 animate-pulse">
                        Menunggu Verifikasi
                    </span>
                @elseif ($task->status === 'REVISION')
                    <span class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                        Perlu Revisi
                    </span>
                @elseif ($task->status === 'IN_PROGRESS')
                    <span class="px-2.5 py-0.5 rounded text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                        Sedang Dikerjakan
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-700">
                        To Do (Belum Dimulai)
                    </span>
                @endif
            </div>

            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">{{ $task->title }}</h1>
        </div>

        <!-- Quick Status Transitions for Assignee -->
        <div class="flex items-center gap-2">
            @if ($task->status === 'TODO' && ($isAssignee || $isSupervisor))
                <form action="{{ route('tasks.status', $task) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="IN_PROGRESS">
                    <button type="submit"
                            class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" />
                        </svg>
                        <span>Mulai Kerjakan (In Progress)</span>
                    </button>
                </form>
            @endif

            <a href="{{ route('tasks.index') }}"
               class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                Kembali ke Daftar
            </a>
        </div>
    </div>

    <!-- Main Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Details & Evidence Submission -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Detail Description Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Instruksi &amp; Rincian Pengerjaan</h3>
                <div class="text-xs text-slate-800 leading-relaxed whitespace-pre-line bg-slate-50/70 p-4 rounded-xl border border-slate-100">
                    {{ $task->description ?: 'Tidak ada instruksi khusus yang disertakan.' }}
                </div>

                @if ($task->workTarget)
                    <div class="p-3.5 rounded-xl bg-indigo-50/60 border border-indigo-100 flex items-start gap-3 text-xs">
                        <div class="w-7 h-7 rounded-lg bg-indigo-600 text-white flex items-center justify-center shrink-0 font-bold">
                            KPI
                        </div>
                        <div class="flex-1">
                            <span class="font-bold text-indigo-900 block">Terkait Target Kerja: {{ $task->workTarget->title }}</span>
                            <span class="text-slate-600 block text-[11px] mt-0.5">
                                Periode: Bulan {{ $task->workTarget->month }}/{{ $task->workTarget->year }} &bull; Target: {{ number_format($task->workTarget->target_value, 0) }} {{ $task->workTarget->target_metric }}
                            </span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Upload Evidence Box -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Bukti Pengerjaan Tugas</h3>
                        <p class="text-xs text-slate-500">Unggah file dokumen, laporan, screenshot, atau arsip zip pengerjaan Anda.</p>
                    </div>
                    <span class="text-[10px] text-slate-400 font-mono">Private Storage Secure</span>
                </div>

                <!-- Existing Attachments List -->
                @if ($task->attachments->isEmpty())
                    <p class="text-xs text-slate-400 italic py-2">Belum ada file bukti kerja yang diunggah.</p>
                @else
                    <div class="space-y-2">
                        @foreach ($task->attachments as $attachment)
                            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-semibold text-slate-800 block truncate">{{ $attachment->file_name }}</span>
                                        <span class="text-[10px] text-slate-500 block">
                                            {{ round($attachment->file_size / 1024, 1) }} KB &bull; Diunggah oleh {{ $attachment->uploadedBy?->name ?? 'User' }} &bull; {{ $attachment->created_at->format('d M Y H:i') }}
                                        </span>
                                    </div>
                                </div>

                                <a href="{{ route('tasks.attachments.download', [$task, $attachment]) }}"
                                   class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 font-semibold transition shrink-0 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>
                                    <span>Unduh</span>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Upload Form for Assignee / Author -->
                @if ($task->status !== 'APPROVED')
                    <form action="{{ route('tasks.evidence', $task) }}" method="POST" enctype="multipart/form-data" class="pt-4 border-t border-slate-100 space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Unggah File Bukti Baru (PDF, DOCX, XLSX, ZIP, JPG, PNG maks. 10MB)
                            </label>
                            <input type="file"
                                   name="file"
                                   required
                                   class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer" />
                            @error('file')
                                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <input type="text"
                                   name="notes"
                                   placeholder="Catatan pengerjaan singkat (opsional)..."
                                   class="w-full px-3.5 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:border-indigo-500" />
                        </div>

                        <div class="flex justify-end">
                            <button type="submit"
                                    class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                                </svg>
                                <span>Submit Bukti Kerja</span>
                            </button>
                        </div>
                    </form>
                @endif
            </div>

            <!-- Review & Revision History Timeline -->
            @if ($task->reviews->isNotEmpty())
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                    <h3 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100">
                        Riwayat Evaluasi &amp; Catatan Supervisor
                    </h3>

                    <div class="space-y-4">
                        @foreach ($task->reviews->sortByDesc('reviewed_at') as $review)
                            <div class="p-4 rounded-xl border {{ $review->decision === 'APPROVED' ? 'bg-emerald-50/40 border-emerald-200' : 'bg-rose-50/40 border-rose-200' }} space-y-2 text-xs">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-slate-900">{{ $review->reviewer?->full_name ?? 'Supervisor' }}</span>
                                        <span class="text-slate-400">&bull;</span>
                                        <span class="text-slate-500 font-mono text-[11px]">{{ $review->reviewed_at?->format('d M Y H:i') }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if ($review->score !== null)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-white border border-slate-200 text-slate-800">
                                                Skor: {{ number_format($review->score, 1) }}
                                            </span>
                                        @endif
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $review->decision === 'APPROVED' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                            {{ $review->decision }}
                                        </span>
                                    </div>
                                </div>

                                <div class="text-slate-700 leading-relaxed">
                                    {{ $review->feedback }}
                                </div>

                                @if ($review->revision_notes)
                                    <div class="p-2.5 rounded-lg bg-rose-100/60 border border-rose-200 text-rose-900 text-[11px] leading-relaxed">
                                        <span class="font-bold block text-rose-800 mb-0.5">Poin Revisi yang Harus Diperbaiki:</span>
                                        {{ $review->revision_notes }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        <!-- Right Col: Metadata & Supervisor Review Panel -->
        <div class="space-y-6">

            <!-- Metadata Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider pb-2 border-b border-slate-100">
                    Informasi Tugas
                </h3>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Penerima Tugas</span>
                        <div class="flex items-center gap-2 mt-1">
                            <div class="w-7 h-7 rounded-lg bg-indigo-600 text-white font-bold flex items-center justify-center text-xs">
                                {{ strtoupper(substr($task->assignedTo?->full_name ?? 'E', 0, 1)) }}
                            </div>
                            <div>
                                <span class="font-semibold text-slate-900 block">{{ $task->assignedTo?->full_name ?? '-' }}</span>
                                <span class="text-[10px] text-slate-500 block">{{ $task->assignedTo?->department?->name ?? 'Dept' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-50">
                        <span class="text-slate-400 block text-[11px]">Pemberi Tugas</span>
                        <span class="font-medium text-slate-800 mt-0.5 block">{{ $task->assignedBy?->full_name ?? '-' }}</span>
                    </div>

                    <div class="pt-2 border-t border-slate-50">
                        <span class="text-slate-400 block text-[11px]">Batas Waktu (Deadline)</span>
                        <span class="font-mono font-semibold text-slate-800 mt-0.5 block">
                            {{ $task->due_date ? $task->due_date->format('d M Y H:i') : 'Tidak ada batas waktu' }}
                        </span>
                    </div>

                    @if ($task->completed_at)
                        <div class="pt-2 border-t border-slate-50">
                            <span class="text-slate-400 block text-[11px]">Selesai Pada</span>
                            <span class="font-mono font-semibold text-emerald-600 mt-0.5 block">
                                {{ $task->completed_at->format('d M Y H:i') }}
                            </span>
                        </div>
                    @endif

                    <div class="pt-2 border-t border-slate-50">
                        <span class="text-slate-400 block text-[11px]">Dibuat Pada</span>
                        <span class="font-mono text-slate-500 text-[11px] mt-0.5 block">
                            {{ $task->created_at->format('d M Y H:i') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Supervisor Evaluation Form (Panel Review) -->
            @if ($isSupervisor && $task->status !== 'APPROVED')
                <div class="bg-slate-900 text-white rounded-2xl p-6 shadow-xl space-y-4 border border-slate-800">
                    <div class="pb-3 border-b border-slate-800">
                        <h3 class="text-sm font-bold text-white flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                            </svg>
                            <span>Panel Verifikasi Supervisor</span>
                        </h3>
                        <p class="text-slate-400 text-[11px] mt-0.5">Tinjau kesesuaian bukti pengerjaan tugas ini.</p>
                    </div>

                    <form action="{{ route('tasks.review', $task) }}" method="POST" class="space-y-4" x-data="{ decision: 'APPROVED' }">
                        @csrf

                        <!-- Decision Choice -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                Keputusan Review <span class="text-rose-400">*</span>
                            </label>
                            <select name="decision"
                                    x-model="decision"
                                    class="w-full px-3 py-2 text-xs rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-indigo-500">
                                <option value="APPROVED">SETUJUI (APPROVED)</option>
                                <option value="REVISION">PERLU REVISI (REVISION)</option>
                                <option value="REJECTED">TOLAK (REJECTED)</option>
                            </select>
                        </div>

                        <!-- Score -->
                        <div>
                            <label for="score" class="block text-xs font-semibold text-slate-300 mb-1.5">
                                Skor Penilaian (0 - 100)
                            </label>
                            <input type="number"
                                   step="0.1"
                                   name="score"
                                   id="score"
                                   min="0"
                                   max="100"
                                   placeholder="Contoh: 90.5"
                                   class="w-full px-3 py-2 text-xs rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-indigo-500" />
                        </div>

                        <!-- Feedback -->
                        <div>
                            <label for="feedback" class="block text-xs font-semibold text-slate-300 mb-1.5">
                                Umpan Balik / Catatan Evaluasi <span class="text-rose-400">*</span>
                            </label>
                            <textarea name="feedback"
                                      id="feedback"
                                      rows="3"
                                      required
                                      placeholder="Berikan apresiasi atau komentar mengenai hasil kerja..."
                                      class="w-full px-3 py-2 text-xs rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-indigo-500"></textarea>
                        </div>

                        <!-- Mandatory Revision Notes if REVISION -->
                        <div x-show="decision === 'REVISION'" x-cloak>
                            <label for="revision_notes" class="block text-xs font-semibold text-rose-400 mb-1.5">
                                Poin Wajib Revisi <span class="text-rose-400">*</span>
                            </label>
                            <textarea name="revision_notes"
                                      id="revision_notes"
                                      rows="3"
                                      placeholder="Rincikan bagian mana yang harus diperbaiki kembali oleh staf..."
                                      class="w-full px-3 py-2 text-xs rounded-xl bg-slate-950 border border-rose-600 text-white focus:outline-none focus:border-rose-400"></textarea>
                        </div>

                        <button type="submit"
                                class="w-full py-2.5 px-4 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-md transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            <span>Simpan Keputusan Review</span>
                        </button>
                    </form>
                </div>
            @endif

        </div>

    </div>

</div>
@endsection
