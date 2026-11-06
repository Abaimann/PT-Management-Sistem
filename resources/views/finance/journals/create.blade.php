@extends('layouts.app')

@section('title', 'Buat Ayat Jurnal Umum')
@section('page_title', 'Buat Jurnal')

@section('content')
<div class="space-y-6" x-data="journalForm()">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('finance.journals.index') }}" class="hover:text-indigo-600 transition">&larr; Kembali ke Jurnal</a>
                <span>&bull;</span>
                <span>Pencatatan Baru</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Buat Ayat Jurnal Umum</h1>
            <p class="text-xs text-slate-500 mt-1">Entri transaksi double-entry berimbang. Minimal 2 baris transaksi debet dan kredit.</p>
        </div>
    </div>

    <!-- Main Entry Form -->
    <form action="{{ route('finance.journals.store') }}" method="POST" id="journalForm">
        @csrf
        <input type="hidden" name="action" x-model="actionType">

        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 space-y-6">

            <!-- Transaction Header Details -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pb-6 border-b border-slate-100">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Transaksi <span class="text-rose-500">*</span></label>
                    <input type="date" name="date" required value="{{ old('date', now()->toDateString()) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan / Deskripsi Transaksi <span class="text-rose-500">*</span></label>
                    <input type="text" name="description" required value="{{ old('description') }}" placeholder="Contoh: Pembayaran sewa kantor bulan November, Pembelian ATK, dll."
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                </div>
            </div>

            <!-- Dynamic Lines Section -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Baris Rekening (Debet / Kredit)</h3>
                    <button type="button" @click="addLine()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Tambah Baris
                    </button>
                </div>

                <div class="overflow-x-auto border border-slate-200 rounded-2xl">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-200">
                                <th class="py-3 px-4 w-1/3">Akun Chart of Account</th>
                                <th class="py-3 px-4 w-1/4">Keterangan Baris (Opsional)</th>
                                <th class="py-3 px-4 w-1/6 text-right">Debit (Rp)</th>
                                <th class="py-3 px-4 w-1/6 text-right">Kredit (Rp)</th>
                                <th class="py-3 px-3 w-10 text-center"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            <template x-for="(line, index) in lines" :key="index">
                                <tr class="hover:bg-slate-50/50 transition">
                                    <!-- CoA Selection -->
                                    <td class="py-2.5 px-4">
                                        <select :name="`lines[${index}][chart_of_account_id]`"
                                                x-model="line.chart_of_account_id"
                                                required
                                                class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-hidden bg-white font-mono">
                                            <option value="">— Pilih Akun CoA —</option>
                                            @foreach ($accounts as $acc)
                                                <option value="{{ $acc->id }}">
                                                    {{ $acc->account_code }} - {{ $acc->account_name }} ({{ $acc->classification }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <!-- Description per line -->
                                    <td class="py-2.5 px-4">
                                        <input type="text"
                                               :name="`lines[${index}][description]`"
                                               x-model="line.description"
                                               placeholder="Catatan baris..."
                                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                                    </td>

                                    <!-- Debit -->
                                    <td class="py-2.5 px-4">
                                        <input type="number"
                                               step="0.01"
                                               min="0"
                                               :name="`lines[${index}][debit]`"
                                               x-model.number="line.debit"
                                               @input="onDebitInput(index)"
                                               placeholder="0.00"
                                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs text-right font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                                    </td>

                                    <!-- Credit -->
                                    <td class="py-2.5 px-4">
                                        <input type="number"
                                               step="0.01"
                                               min="0"
                                               :name="`lines[${index}][credit]`"
                                               x-model.number="line.credit"
                                               @input="onCreditInput(index)"
                                               placeholder="0.00"
                                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs text-right font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                                    </td>

                                    <!-- Delete Row Button -->
                                    <td class="py-2.5 px-3 text-center">
                                        <button type="button"
                                                @click="removeLine(index)"
                                                :disabled="lines.length <= 2"
                                                :class="{ 'opacity-30 cursor-not-allowed': lines.length <= 2 }"
                                                class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 flex items-center justify-center transition">
                                            &times;
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <!-- Live Calculation Summary Footer -->
                        <tfoot>
                            <tr class="bg-slate-50 font-bold text-xs border-t-2 border-slate-200">
                                <td colspan="2" class="py-3 px-4 text-right uppercase tracking-wider text-slate-600">
                                    Total Keseimbangan:
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-slate-900" x-text="formatRupiah(totalDebit)"></td>
                                <td class="py-3 px-4 text-right font-mono text-slate-900" x-text="formatRupiah(totalCredit)"></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Live Balancing Indicator Banner -->
            <div class="p-4 rounded-2xl border transition flex flex-col sm:flex-row items-center justify-between gap-3"
                 :class="{
                     'bg-emerald-50 border-emerald-200 text-emerald-800': isBalanced,
                     'bg-rose-50 border-rose-200 text-rose-800': !isBalanced && (totalDebit > 0 || totalCredit > 0),
                     'bg-slate-50 border-slate-200 text-slate-600': totalDebit === 0 && totalCredit === 0
                 }">
                <div class="flex items-center gap-3">
                    <template x-if="isBalanced">
                        <div class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold">
                            &check;
                        </div>
                    </template>
                    <template x-if="!isBalanced && (totalDebit > 0 || totalCredit > 0)">
                        <div class="w-8 h-8 rounded-full bg-rose-500 text-white flex items-center justify-center font-bold">
                            !
                        </div>
                    </template>
                    <template x-if="totalDebit === 0 && totalCredit === 0">
                        <div class="w-8 h-8 rounded-full bg-slate-300 text-white flex items-center justify-center font-bold">
                            ?
                        </div>
                    </template>

                    <div>
                        <div class="text-xs font-bold" x-text="isBalanced ? 'Ayat Jurnal Telah Seimbang (Balanced)' : (totalDebit === 0 && totalCredit === 0 ? 'Masukkan nominal debit dan kredit' : 'Ayat Jurnal Tidak Seimbang (Unbalanced)')"></div>
                        <div class="text-[11px] opacity-80" x-text="isBalanced ? 'Total Debit sama persis dengan Total Kredit. Jurnal siap diposting ke Buku Besar.' : `Selisih antara Debit dan Kredit: ${formatRupiah(difference)}`"></div>
                    </div>
                </div>

                <div class="flex items-center gap-2 self-end sm:self-auto">
                    <!-- Simpan Draft -->
                    <button type="button"
                            @click="submitAs('draft')"
                            class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                        Simpan Draft
                    </button>

                    <!-- Posting Sekarang -->
                    <button type="button"
                            @click="submitAs('post')"
                            :disabled="!isBalanced"
                            :class="{ 'opacity-50 cursor-not-allowed': !isBalanced }"
                            class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs transition">
                        Posting ke Buku Besar
                    </button>
                </div>
            </div>

        </div>
    </form>

</div>

@push('scripts')
<script>
    function journalForm() {
        return {
            actionType: 'post',
            lines: [
                { chart_of_account_id: '', description: '', debit: 0, credit: 0 },
                { chart_of_account_id: '', description: '', debit: 0, credit: 0 }
            ],

            addLine() {
                this.lines.push({ chart_of_account_id: '', description: '', debit: 0, credit: 0 });
            },

            removeLine(index) {
                if (this.lines.length > 2) {
                    this.lines.splice(index, 1);
                }
            },

            onDebitInput(index) {
                if (this.lines[index].debit > 0) {
                    this.lines[index].credit = 0;
                }
            },

            onCreditInput(index) {
                if (this.lines[index].credit > 0) {
                    this.lines[index].debit = 0;
                }
            },

            get totalDebit() {
                return this.lines.reduce((acc, l) => acc + (parseFloat(l.debit) || 0), 0);
            },

            get totalCredit() {
                return this.lines.reduce((acc, l) => acc + (parseFloat(l.credit) || 0), 0);
            },

            get difference() {
                return Math.abs(this.totalDebit - this.totalCredit);
            },

            get isBalanced() {
                return this.totalDebit > 0 && this.difference < 0.01;
            },

            formatRupiah(num) {
                return 'Rp ' + Number(num).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },

            submitAs(action) {
                this.actionType = action;
                if (action === 'post' && !this.isBalanced) {
                    alert('Jurnal belum seimbang! Pastikan total debit sama dengan total kredit.');
                    return;
                }
                document.getElementById('journalForm').submit();
            }
        }
    }
</script>
@endpush
@endsection
