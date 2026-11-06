@extends('layouts.app')

@section('title', 'Buka Periode Payroll Baru')
@section('page_title', 'Buka Periode Payroll')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Navigation -->
    <div class="flex items-center justify-between">
        <a href="{{ route('payroll.periods.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-900 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Periode
        </a>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
            Status Baru: DRAFT
        </span>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-base font-bold text-slate-900">Formulir Pembukaan Periode Payroll</h2>
            <p class="text-xs text-slate-500 mt-1">Konfigurasikan rentang kerja, tanggal batas cutoff presensi karyawan, dan jadwal pembayaran.</p>
        </div>

        <form method="POST" action="{{ route('payroll.periods.store') }}" class="p-6 space-y-5">
            @csrf

            <!-- Kode & Nama Periode -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Kode Periode <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="code" value="{{ old('code', $suggestedCode) }}" required
                           class="w-full text-xs rounded-xl border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 font-mono uppercase @error('code') border-rose-300 ring-rose-300 @enderror">
                    @error('code')
                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Nama Periode <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $suggestedName) }}" required
                           class="w-full text-xs rounded-xl border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-rose-300 ring-rose-300 @enderror">
                    @error('name')
                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Rentang Tanggal Periode Kerja -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 space-y-3">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Rentang Kalender Kerja</h3>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Mulai Periode <span class="text-rose-500">*</span></label>
                        <input type="date" name="start_date" value="{{ old('start_date', $startDate) }}" required
                               class="w-full text-xs rounded-xl border-slate-200 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Akhir Periode <span class="text-rose-500">*</span></label>
                        <input type="date" name="end_date" value="{{ old('end_date', $endDate) }}" required
                               class="w-full text-xs rounded-xl border-slate-200 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
            </div>

            <!-- Rentang Tanggal Cutoff Kehadiran -->
            <div class="p-4 rounded-xl bg-indigo-50/50 border border-indigo-100 space-y-3">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <h3 class="text-xs font-bold text-indigo-900 uppercase tracking-wider">Rentang Batas Cutoff Absensi</h3>
                </div>
                <p class="text-[11px] text-indigo-700 leading-relaxed">
                    Sistem akan mengambil seluruh rekaman presensi selfie, keterlambatan, lembur, dan absensi mangkir dalam rentang tanggal cutoff ini untuk dikalkulasi secara otomatis ke dalam slip gaji.
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Cutoff Mulai <span class="text-rose-500">*</span></label>
                        <input type="date" name="cutoff_start_date" value="{{ old('cutoff_start_date', $startDate) }}" required
                               class="w-full text-xs rounded-xl border-slate-200 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Cutoff Selesai <span class="text-rose-500">*</span></label>
                        <input type="date" name="cutoff_end_date" value="{{ old('cutoff_end_date', $cutoffDate) }}" required
                               class="w-full text-xs rounded-xl border-slate-200 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
            </div>

            <!-- Jadwal Pembayaran -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Tanggal Jadwal Pembayaran (Pay Date) <span class="text-rose-500">*</span>
                </label>
                <input type="date" name="payment_date" value="{{ old('payment_date', $paymentDate) }}" required
                       class="w-full text-xs rounded-xl border-slate-200 focus:ring-indigo-500 focus:border-indigo-500">
                <p class="text-[10px] text-slate-400 mt-1">Tanggal yang akan tercantum pada slip gaji resmi sebagai tanggal pembayaran gaji.</p>
            </div>

            <!-- Form Actions -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('payroll.periods.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition">
                    Batal
                </a>
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Periode Baru
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
