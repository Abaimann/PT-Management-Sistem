@extends('layouts.app')

@section('title', 'Ajukan Klaim Reimbursement')
@section('page_title', 'Ajukan Reimbursement')

@section('content')
<div class="space-y-6 max-w-3xl mx-auto">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('reimbursements.index') }}" class="hover:text-indigo-600 transition">&larr; Kembali ke Daftar Klaim</a>
                <span>&bull;</span>
                <span>Form Pengajuan</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Formulir Klaim Reimbursement</h1>
            <p class="text-xs text-slate-500 mt-1">Ajukan penggantian dana biaya operasional kantor yang telah Anda talangi terlebih dahulu.</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 sm:p-8">
        <form action="{{ route('reimbursements.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <!-- Judul Klaim -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Judul Pengeluaran / Keperluan <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="title" required value="{{ old('title') }}"
                       placeholder="Contoh: Konsumsi Lembur Project Core ERP, Bensin Dinas Lapangan, Pembelian Kabel HDMI"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                @error('title')
                    <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Nominal Pengeluaran -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Nominal Biaya yang Dikeluarkan (Rupiah) <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-xs font-bold text-slate-400">
                        Rp
                    </div>
                    <input type="number" name="amount" required step="1" min="1000" value="{{ old('amount') }}"
                           placeholder="50000"
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-xs font-mono font-bold focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Masukkan nominal angka murni tanpa titik atau koma.</p>
                @error('amount')
                    <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Keterangan / Deskripsi Rinci -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Deskripsi &amp; Rincian Pengeluaran
                </label>
                <textarea name="description" rows="3" placeholder="Jelaskan alasan dan konteks operasional dari pengeluaran ini..."
                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Upload Bukti Nota / Kuitansi -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Unggah Bukti Nota / Kuitansi Pembayaran <span class="text-rose-500">*</span>
                </label>
                <div class="border-2 border-dashed border-slate-200 rounded-2xl p-6 text-center hover:border-indigo-400 transition bg-slate-50/50">
                    <svg class="mx-auto h-10 w-10 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <div class="mt-2 text-xs text-slate-600">
                        <label for="receipt" class="cursor-pointer font-bold text-indigo-600 hover:text-indigo-500 underline">
                            Pilih file kuitansi
                        </label>
                        <span> atau seret ke sini</span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Format: PDF, JPG, JPEG, PNG, WEBP (Maksimal 5 MB)</p>
                    <input id="receipt" name="receipt" type="file" required accept=".pdf,image/*" class="sr-only"
                           onchange="document.getElementById('fileName').textContent = this.files[0] ? this.files[0].name : '';">
                    <div id="fileName" class="text-xs font-bold text-emerald-600 font-mono mt-2"></div>
                </div>
                @error('receipt')
                    <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Enterprise Guideline Note -->
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 flex items-start gap-3">
                <svg class="w-5 h-5 text-indigo-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div class="text-xs text-slate-600 leading-relaxed">
                    <span class="font-bold text-slate-800">Kebijakan Finansial:</span>
                    Pastikan nota mencantumkan tanggal transaksi, stempel toko / tanda tangan basah, dan rincian item barang yang jelas. Nota yang buram atau tidak sah akan ditolak oleh staf Finance.
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('reimbursements.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs transition">
                    Kirim Pengajuan Klaim
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
