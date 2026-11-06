@extends('layouts.app')

@section('title', 'Klaim Reimbursement Karyawan')
@section('page_title', 'Reimbursement')

@section('content')
<div class="space-y-6" x-data="{
    approveModalOpen: false,
    rejectModalOpen: false,
    selectedReimbursement: null,
    approvalActionUrl: '',
    rejectActionUrl: '',

    openApprove(r) {
        this.selectedReimbursement = r;
        this.approvalActionUrl = '{{ url('reimbursements') }}/' + r.id + '/approve';
        this.approveModalOpen = true;
    },

    openReject(r) {
        this.selectedReimbursement = r;
        this.rejectActionUrl = '{{ url('reimbursements') }}/' + r.id + '/reject';
        this.rejectModalOpen = true;
    }
}">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('finance.index') }}" class="hover:text-indigo-600 transition">Finance</a>
                <span>&bull;</span>
                <span>Klaim Biaya Operasional</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Klaim Reimbursement Karyawan</h1>
            <p class="text-xs text-slate-500 mt-1">Pengajuan klaim penggantian biaya operasional, verifikasi bukti nota, dan pencairan kas otomatis.</p>
        </div>
        <a href="{{ route('reimbursements.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs transition self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Ajukan Klaim Baru
        </a>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form action="{{ route('reimbursements.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="relative sm:col-span-2">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nomor klaim, judul pengeluaran, atau nama pegawai..."
                       class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
            </div>

            <div>
                <select name="status" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-hidden bg-white">
                    <option value="">— Semua Status —</option>
                    <option value="PENDING" {{ request('status') === 'PENDING' ? 'selected' : '' }}>PENDING (Menunggu)</option>
                    <option value="PAID" {{ request('status') === 'PAID' ? 'selected' : '' }}>PAID (Dicairkan)</option>
                    <option value="REJECTED" {{ request('status') === 'REJECTED' ? 'selected' : '' }}>REJECTED (Ditolak)</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold transition">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('reimbursements.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table of Reimbursements -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                        <th class="py-3.5 px-4">No. Klaim &bull; Tanggal</th>
                        <th class="py-3.5 px-4">Karyawan &bull; Departemen</th>
                        <th class="py-3.5 px-4">Keperluan / Keterangan</th>
                        <th class="py-3.5 px-4 text-right">Nominal (Rp)</th>
                        <th class="py-3.5 px-4 text-center">Bukti Nota</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center">Aksi / Verifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse ($reimbursements as $r)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3 px-4 whitespace-nowrap">
                            <span class="font-mono font-bold text-slate-800">{{ $r->claim_number }}</span>
                            <div class="text-[11px] text-slate-400 mt-0.5">
                                {{ $r->created_at->isoFormat('D MMM Y') }}
                            </div>
                        </td>

                        <td class="py-3 px-4 whitespace-nowrap">
                            <div class="font-semibold text-slate-900">{{ $r->employee?->full_name ?? '—' }}</div>
                            <div class="text-[11px] text-slate-500">
                                {{ $r->employee?->department?->name ?? '—' }}
                            </div>
                        </td>

                        <td class="py-3 px-4">
                            <div class="font-semibold text-slate-800 max-w-xs truncate">{{ $r->title }}</div>
                            @if ($r->description)
                                <div class="text-[11px] text-slate-500 mt-0.5 truncate max-w-xs">{{ $r->description }}</div>
                            @endif
                            @if ($r->notes)
                                <div class="text-[10px] text-rose-600 bg-rose-50 px-2 py-0.5 rounded mt-1 inline-block">
                                    Catatan: {{ $r->notes }}
                                </div>
                            @endif
                        </td>

                        <td class="py-3 px-4 text-right font-mono font-extrabold text-slate-900 whitespace-nowrap">
                            Rp {{ number_format($r->amount, 0, ',', '.') }}
                        </td>

                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            @if ($r->receipt_path)
                                <a href="{{ route('reimbursements.receipt', $r) }}" target="_blank"
                                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-[11px] font-semibold transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Lihat Nota
                                </a>
                            @else
                                <span class="text-slate-300 text-[11px]">Tidak Ada</span>
                            @endif
                        </td>

                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            @if ($r->status === 'PAID')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    DICAIRKAN
                                </span>
                                @if ($r->paid_at)
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $r->paid_at->format('d/m/Y') }}</div>
                                @endif
                            @elseif ($r->status === 'PENDING')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    MENUNGGU APPROVAL
                                </span>
                            @elseif ($r->status === 'REJECTED')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    DITOLAK
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                    {{ $r->status }}
                                </span>
                            @endif
                        </td>

                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            @if ($isApprover && $r->status === 'PENDING')
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button"
                                            @click="openApprove({{ json_encode($r) }})"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold shadow-xs transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Cairkan
                                    </button>
                                    <button type="button"
                                            @click="openReject({{ json_encode($r) }})"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 text-[11px] font-bold transition">
                                        Tolak
                                    </button>
                                </div>
                            @elseif ($r->status === 'PAID')
                                <span class="text-slate-400 text-[11px] font-mono">
                                    Disetujui oleh: {{ $r->approver?->name ?? 'Finance' }}
                                </span>
                            @elseif ($r->status === 'REJECTED')
                                <span class="text-rose-400 text-[11px]">
                                    Ditolak oleh: {{ $r->approver?->name ?? 'Finance' }}
                                </span>
                            @else
                                <span class="text-slate-400 text-[11px]">Sedang Diproses</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-xs text-slate-400">
                            Belum ada permohonan klaim reimbursement yang terdaftar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($reimbursements->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $reimbursements->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Approval & Pencairan Kas -->
    <div x-show="approveModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full border border-slate-200 shadow-2xl overflow-hidden"
             @click.away="approveModalOpen = false">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Persetujuan &amp; Pencairan Kas</h3>
                        <p class="text-[11px] text-slate-400">Otorisasi pencairan dana dan auto-posting jurnal pengeluaran.</p>
                    </div>
                </div>
                <button type="button" @click="approveModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg leading-none">&times;</button>
            </div>

            <form :action="approvalActionUrl" method="POST" class="p-5 space-y-4">
                @csrf
                <!-- Detail Info -->
                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100 space-y-1.5 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">No. Klaim:</span>
                        <span class="font-mono font-bold text-slate-800" x-text="selectedReimbursement ? selectedReimbursement.claim_number : ''"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Keperluan:</span>
                        <span class="font-semibold text-slate-800" x-text="selectedReimbursement ? selectedReimbursement.title : ''"></span>
                    </div>
                    <div class="flex items-center justify-between border-t border-slate-200/60 pt-1.5">
                        <span class="text-slate-500 font-bold">Nominal yang Dicairkan:</span>
                        <span class="font-mono font-extrabold text-emerald-700 text-sm" x-text="selectedReimbursement ? 'Rp ' + Number(selectedReimbursement.amount).toLocaleString('id-ID') : ''"></span>
                    </div>
                </div>

                <!-- Akun Kas / Bank Pembayar -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Sumber Dana Pembayaran (Kas / Bank) <span class="text-rose-500">*</span>
                    </label>
                    <select name="cash_account_id" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-hidden bg-white font-mono">
                        <option value="">— Pilih Akun Kas / Bank —</option>
                        @foreach ($cashBankAccounts as $cb)
                            <option value="{{ $cb->id }}" {{ $cb->account_code === '1101' ? 'selected' : '' }}>
                                {{ $cb->account_code }} - {{ $cb->account_name }} ({{ $cb->classification }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-slate-400 mt-1">Sistem akan secara otomatis mengkredit rekening Kas/Bank di atas.</p>
                </div>

                <!-- Akun Beban (Opsional) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Alokasi Akun Beban (Default: Beban Reimbursement 5104)
                    </label>
                    <select name="expense_account_id" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-hidden bg-white font-mono">
                        <option value="">— Gunakan Akun Beban Default (5104) —</option>
                        @foreach ($expenseAccounts as $exp)
                            <option value="{{ $exp->id }}">
                                {{ $exp->account_code }} - {{ $exp->account_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="approveModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition">
                        Setujui &amp; Posting Kas Keluar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Rejection -->
    <div x-show="rejectModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full border border-slate-200 shadow-2xl overflow-hidden"
             @click.away="rejectModalOpen = false">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Tolak Klaim Reimbursement</h3>
                        <p class="text-[11px] text-slate-400">Berikan alasan penolakan kepada karyawan terkait.</p>
                    </div>
                </div>
                <button type="button" @click="rejectModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg leading-none">&times;</button>
            </div>

            <form :action="rejectActionUrl" method="POST" class="p-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan / Alasan Penolakan <span class="text-rose-500">*</span></label>
                    <textarea name="notes" rows="3" required placeholder="Contoh: Bukti nota buram tidak terbaca, pengeluaran di luar ketentuan dinas..."
                              class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-rose-500 focus:outline-hidden"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="rejectModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-xs transition">
                        Konfirmasi Penolakan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
