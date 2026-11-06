@extends('layouts.app')

@section('title', 'Slip Gaji Saya')
@section('page_title', 'Slip Gaji Saya')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Riwayat Slip Gaji Saya</h1>
            <p class="text-xs text-slate-500 mt-1">Daftar slip gaji resmi yang telah diterbitkan untuk Anda.</p>
        </div>
        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600">
            <span class="font-semibold text-slate-900">{{ $employee->full_name }}</span> (NIK: <span class="font-mono">{{ $employee->nik }}</span>)
        </div>
    </div>

    <!-- Table of Payslips -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/75 border-b border-slate-200 text-slate-700 font-semibold uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="px-4 py-3">No. Slip</th>
                        <th class="px-4 py-3">Periode Payroll</th>
                        <th class="px-4 py-3">Tanggal Bayar</th>
                        <th class="px-4 py-3">Gaji Kotor</th>
                        <th class="px-4 py-3">Total Potongan</th>
                        <th class="px-4 py-3">Take Home Pay</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payslips as $slip)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-4 py-3.5 font-mono text-[11px] font-semibold text-slate-800">
                            {{ $slip->payslip_number }}
                        </td>
                        <td class="px-4 py-3.5 font-medium text-slate-900">
                            {{ $slip->payrollRun->payrollPeriod->name }}
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            {{ $slip->payrollRun->payrollPeriod->payment_date->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap text-slate-800">
                            Rp {{ number_format($slip->gross_salary, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap text-rose-600 font-medium">
                            - Rp {{ number_format($slip->total_deductions, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap font-bold text-emerald-700">
                            Rp {{ number_format($slip->net_salary, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold {{ $slip->status === 'PUBLISHED' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                                {{ $slip->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-right whitespace-nowrap">
                            <a href="{{ route('payroll.payslips.show', $slip) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold text-xs transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Lihat &amp; Cetak</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-10 h-10 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                                <span class="font-semibold text-slate-600 text-xs">Belum ada slip gaji yang diterbitkan</span>
                                <p class="text-[11px] text-slate-400 mt-1">Slip gaji akan muncul di sini setelah periode payroll disetujui dan ditutup secara resmi oleh bagian payroll/keuangan.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payslips->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
            {{ $payslips->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
