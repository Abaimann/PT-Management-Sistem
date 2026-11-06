@extends('layouts.app')

@section('title', 'Slip Gaji - ' . $payslip->payslip_number)
@section('page_title', 'Slip Gaji Pegawai')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Screen Navigation & Action Bar (Hidden in Print) -->
    <div class="flex items-center justify-between print:hidden">
        <a href="{{ url()->previous() ?? route('payroll.periods.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-900 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $payslip->status === 'PUBLISHED' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                Status Slip: {{ $payslip->status }}
            </span>
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Slip Gaji
            </button>
        </div>
    </div>

    <!-- Official Payslip Document Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-10 text-slate-800 print:border-none print:shadow-none print:p-0 print:m-0" id="payslip-content">

        <!-- KOP PERUSAHAAN (COMPANY HEADER) -->
        <div class="border-b-2 border-slate-900 pb-4 mb-6">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-900 text-white flex items-center justify-center font-black text-xl tracking-wider shadow-sm ring-1 ring-black/10 shrink-0">
                        MS
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight leading-none uppercase">
                            {{ $company->legal_name ?? 'PT MANAGEMENT SISTEM INDONESIA' }}
                        </h1>
                        <p class="text-xs font-semibold text-indigo-700 tracking-wider mt-1 uppercase">
                            {{ $company->company_name ?? 'PT Management Sistem ERP' }}
                        </p>
                        <p class="text-[11px] text-slate-500 mt-1 leading-snug">
                            {{ $company->address ?? 'Gedung Cyber 2 Lantai 18, Jl. HR Rasuna Said Blok X-5 No. 13' }}, {{ $company->city ?? 'Jakarta Selatan' }} {{ $company->postal_code ?? '12950' }}
                        </p>
                        <p class="text-[10px] text-slate-400 mt-0.5">
                            NPWP: <span class="font-mono text-slate-600">{{ $company->npwp ?? '01.234.567.8-012.000' }}</span> | Email: {{ $company->email ?? 'hr@managementsistem.co.id' }} | Telp: {{ $company->phone ?? '+62 21 5289 1234' }}
                        </p>
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <div class="inline-block px-3 py-1 bg-slate-900 text-white text-[11px] font-black uppercase tracking-widest rounded-lg">
                        SLIP GAJI RESMI
                    </div>
                    <div class="font-mono text-xs text-slate-600 font-bold mt-2">
                        {{ $payslip->payslip_number }}
                    </div>
                    <div class="text-[10px] text-slate-400 mt-0.5">
                        Tgl Cetak: {{ now()->translatedFormat('d F Y') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- EMPLOYEE & PERIOD METADATA -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200/80 mb-6 text-xs">
            <div>
                <span class="block text-[10px] uppercase font-semibold text-slate-400">Nama Karyawan</span>
                <span class="font-bold text-slate-900 text-sm">{{ $payslip->employee->full_name }}</span>
            </div>
            <div>
                <span class="block text-[10px] uppercase font-semibold text-slate-400">Nomor Induk (NIK)</span>
                <span class="font-mono font-bold text-slate-800">{{ $payslip->employee->nik }}</span>
            </div>
            <div>
                <span class="block text-[10px] uppercase font-semibold text-slate-400">Departemen</span>
                <span class="font-semibold text-slate-800">{{ $payslip->employee->department?->name ?? '—' }}</span>
            </div>
            <div>
                <span class="block text-[10px] uppercase font-semibold text-slate-400">Jabatan</span>
                <span class="font-semibold text-slate-800">{{ $payslip->employee->position?->name ?? '—' }}</span>
            </div>
            <div>
                <span class="block text-[10px] uppercase font-semibold text-slate-400">Periode Payroll</span>
                <span class="font-semibold text-slate-800">{{ $payslip->payrollRun->payrollPeriod->name }}</span>
            </div>
            <div>
                <span class="block text-[10px] uppercase font-semibold text-slate-400">Rentang Cutoff Absensi</span>
                <span class="font-semibold text-slate-800">
                    {{ $payslip->payrollRun->payrollPeriod->effective_cutoff_start_date->format('d/m/Y') }} - {{ $payslip->payrollRun->payrollPeriod->effective_cutoff_end_date->format('d/m/Y') }}
                </span>
            </div>
            <div>
                <span class="block text-[10px] uppercase font-semibold text-slate-400">Tanggal Pembayaran</span>
                <span class="font-semibold text-slate-800">{{ $payslip->payrollRun->payrollPeriod->payment_date->format('d F Y') }}</span>
            </div>
            <div>
                <span class="block text-[10px] uppercase font-semibold text-slate-400">Status Kepegawaian</span>
                <span class="font-semibold text-indigo-700 uppercase">{{ $payslip->employee->employment_status }}</span>
            </div>
        </div>

        <!-- 2-COLUMN SALARY BREAKDOWN (PENDAPATAN & POTONGAN) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

            <!-- KOLOM PENDAPATAN (EARNINGS) -->
            <div class="border border-slate-200 rounded-xl overflow-hidden flex flex-col">
                <div class="px-4 py-2.5 bg-emerald-50/80 border-b border-emerald-100 flex items-center justify-between">
                    <span class="text-xs font-black text-emerald-900 uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        PENDAPATAN (EARNINGS)
                    </span>
                    <span class="text-[10px] text-emerald-700 font-semibold">Jumlah (IDR)</span>
                </div>
                <div class="p-4 space-y-2.5 flex-1 text-xs">
                    @forelse($payslip->earnings() as $item)
                    <div class="flex items-center justify-between py-1 border-b border-dashed border-slate-100 last:border-none">
                        <span class="text-slate-700">{{ $item->name }}</span>
                        <span class="font-semibold text-slate-900 font-mono">Rp {{ number_format($item->amount, 0, ',', '.') }}</span>
                    </div>
                    @empty
                    <div class="text-slate-400 italic text-center py-4">Tidak ada komponen pendapatan tercatat.</div>
                    @endforelse
                </div>
                <div class="px-4 py-3 bg-slate-50 border-t border-slate-200 flex items-center justify-between text-xs font-bold text-slate-900">
                    <span>TOTAL PENDAPATAN KOTOR (A)</span>
                    <span class="font-mono text-emerald-700 text-sm">Rp {{ number_format($payslip->gross_salary, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- KOLOM POTONGAN (DEDUCTIONS) -->
            <div class="border border-slate-200 rounded-xl overflow-hidden flex flex-col">
                <div class="px-4 py-2.5 bg-rose-50/80 border-b border-rose-100 flex items-center justify-between">
                    <span class="text-xs font-black text-rose-900 uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                        POTONGAN (DEDUCTIONS)
                    </span>
                    <span class="text-[10px] text-rose-700 font-semibold">Jumlah (IDR)</span>
                </div>
                <div class="p-4 space-y-2.5 flex-1 text-xs">
                    @forelse($payslip->deductions() as $item)
                    <div class="flex items-center justify-between py-1 border-b border-dashed border-slate-100 last:border-none">
                        <span class="text-slate-700">{{ $item->name }}</span>
                        <span class="font-semibold text-rose-600 font-mono">- Rp {{ number_format($item->amount, 0, ',', '.') }}</span>
                    </div>
                    @empty
                    <div class="text-slate-400 italic text-center py-4">Tidak ada potongan pada periode ini.</div>
                    @endforelse
                </div>
                <div class="px-4 py-3 bg-slate-50 border-t border-slate-200 flex items-center justify-between text-xs font-bold text-slate-900">
                    <span>TOTAL POTONGAN (B)</span>
                    <span class="font-mono text-rose-600 text-sm">Rp {{ number_format($payslip->total_deductions, 0, ',', '.') }}</span>
                </div>
            </div>

        </div>

        <!-- TAKE HOME PAY BANNER -->
        <div class="p-5 rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 shadow-sm">
            <div>
                <div class="text-[11px] font-semibold text-indigo-300 uppercase tracking-wider">GAJI BERSIH DITERIMA (TAKE HOME PAY)</div>
                <div class="text-xs text-slate-300 mt-0.5">Rumus: Total Pendapatan Kotor (A) dikurangi Total Potongan (B)</div>
            </div>
            <div class="text-right">
                <div class="text-2xl sm:text-3xl font-black tracking-tight text-emerald-400 font-mono">
                    Rp {{ number_format($payslip->net_salary, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <!-- SIGNATURES & LEGAL DISCLAIMER -->
        <div class="pt-6 border-t border-slate-200 text-xs text-slate-600 space-y-8">
            <div class="grid grid-cols-2 gap-8 text-center">
                <div>
                    <div class="text-slate-400 text-[11px]">Diterbitkan Oleh:</div>
                    <div class="font-bold text-slate-800 mt-1">Divisi Payroll &amp; HRD</div>
                    <div class="h-20 flex items-center justify-center">
                        <div class="px-4 py-1 rounded-lg border border-dashed border-slate-300 text-[10px] text-slate-400 font-mono">
                            [VALIDATED BY ERP SYSTEM]
                        </div>
                    </div>
                    <div class="font-bold text-slate-800 border-t border-slate-300 pt-1 inline-block min-w-[180px]">
                        {{ $company->legal_name ?? 'PT Management Sistem Indonesia' }}
                    </div>
                </div>

                <div>
                    <div class="text-slate-400 text-[11px]">Diterima Oleh:</div>
                    <div class="font-bold text-slate-800 mt-1">{{ $payslip->employee->full_name }}</div>
                    <div class="h-20"></div>
                    <div class="font-bold text-slate-800 border-t border-slate-300 pt-1 inline-block min-w-[180px]">
                        {{ $payslip->employee->nik }}
                    </div>
                </div>
            </div>

            <!-- Confidentiality Notice -->
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60 text-[10px] text-slate-400 text-center leading-relaxed">
                <strong>PERINGATAN KERAHASIAAN:</strong> Dokumen ini bersifat strictly confidential. Informasi mengenai rincian penghasilan merupakan rahasia antara perusahaan dan karyawan bersangkutan. Dilarang menyebarluaskan dokumen ini kepada pihak ketiga tanpa izin resmi dari manajemen. Dokumen ini sah dan diterbitkan secara digital oleh Sistem Enterprise PT Management Sistem.
            </div>
        </div>

    </div>

</div>

<!-- Inline Print Styles -->
<style>
@media print {
    body * {
        visibility: hidden;
    }
    #payslip-content, #payslip-content * {
        visibility: visible;
    }
    #payslip-content {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    aside, nav, header {
        display: none !important;
    }
}
</style>
@endsection
