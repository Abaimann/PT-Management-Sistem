<?php

namespace App\Services\Finance;

use App\Models\Core\User;
use App\Models\Finance\ChartOfAccount;
use App\Models\Finance\JournalEntry;
use App\Models\Finance\JournalLine;
use App\Models\Finance\Reimbursement;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AccountingService
{
    /**
     * Create a double-entry balanced journal entry.
     *
     * @param string $description
     * @param array $lines List of lines: [['chart_of_account_id' => int, 'debit' => float, 'credit' => float, 'description' => ?string]]
     * @param string|null $refType
     * @param int|null $refId
     * @param string $status 'POSTED' or 'DRAFT'
     * @param int|null $createdBy
     * @param string|null $date
     * @return JournalEntry
     * @throws InvalidArgumentException
     */
    public function createBalancedJournal(
        string $description,
        array $lines,
        ?string $refType = null,
        ?int $refId = null,
        string $status = 'POSTED',
        ?int $createdBy = null,
        ?string $date = null
    ): JournalEntry {
        if (count($lines) < 2) {
            throw new InvalidArgumentException('Ayat jurnal minimal harus memiliki 2 baris transaksi (debet dan kredit).');
        }

        $totalDebit = 0.0;
        $totalCredit = 0.0;
        $formattedLines = [];

        foreach ($lines as $index => $line) {
            $accountId = $line['chart_of_account_id'] ?? $line['account_id'] ?? null;
            if (!$accountId) {
                throw new InvalidArgumentException("Baris ke-" . ($index + 1) . " wajib memilih akun Chart of Account.");
            }

            $debit = (float) ($line['debit'] ?? 0);
            $credit = (float) ($line['credit'] ?? 0);

            if ($debit < 0 || $credit < 0) {
                throw new InvalidArgumentException("Nilai debet dan kredit tidak boleh bernilai negatif pada baris ke-" . ($index + 1) . ".");
            }

            if ($debit == 0 && $credit == 0) {
                continue; // Abaikan baris kosong
            }

            $totalDebit += $debit;
            $totalCredit += $credit;

            $formattedLines[] = [
                'chart_of_account_id' => (int) $accountId,
                'description' => $line['description'] ?? null,
                'debit' => $debit,
                'credit' => $credit,
            ];
        }

        if (count($formattedLines) < 2) {
            throw new InvalidArgumentException('Ayat jurnal minimal harus memiliki 2 baris transaksi aktif.');
        }

        if ($status === 'POSTED') {
            if ($totalDebit <= 0) {
                throw new InvalidArgumentException('Total transaksi jurnal harus lebih besar dari nol.');
            }

            if (bccomp((string) round($totalDebit, 2), (string) round($totalCredit, 2), 2) !== 0) {
                throw new InvalidArgumentException(sprintf(
                    'Jurnal tidak seimbang! Total Debit (Rp %s) tidak sama dengan Total Kredit (Rp %s). Selisih: Rp %s.',
                    number_format($totalDebit, 2, ',', '.'),
                    number_format($totalCredit, 2, ',', '.'),
                    number_format(abs($totalDebit - $totalCredit), 2, ',', '.')
                ));
            }
        }

        return DB::transaction(function () use ($description, $formattedLines, $refType, $refId, $status, $createdBy, $date) {
            $journalDate = $date ? Carbon::parse($date)->toDateString() : now()->toDateString();
            $entryNumber = $this->generateEntryNumber($journalDate);

            $journal = JournalEntry::create([
                'entry_number' => $entryNumber,
                'date' => $journalDate,
                'reference_type' => $refType,
                'reference_id' => $refId,
                'description' => $description,
                'status' => $status,
                'posted_at' => $status === 'POSTED' ? now() : null,
                'created_by' => $createdBy ?? auth()->id(),
            ]);

            foreach ($formattedLines as $lineData) {
                $journal->lines()->create($lineData);
            }

            return $journal->load(['lines.chartOfAccount', 'createdBy']);
        });
    }

    /**
     * Authorize and post a draft journal entry.
     *
     * @param JournalEntry $journal
     * @return JournalEntry
     * @throws InvalidArgumentException
     */
    public function postJournal(JournalEntry $journal): JournalEntry
    {
        if ($journal->status === 'POSTED') {
            return $journal;
        }

        $journal->load('lines');

        if ($journal->lines->count() < 2) {
            throw new InvalidArgumentException('Jurnal harus memiliki minimal 2 baris transaksi untuk diposting.');
        }

        $totalDebit = (float) $journal->lines->sum('debit');
        $totalCredit = (float) $journal->lines->sum('credit');

        if ($totalDebit <= 0) {
            throw new InvalidArgumentException('Total transaksi jurnal harus lebih besar dari nol untuk diposting.');
        }

        foreach ($journal->lines as $line) {
            if ($line->debit < 0 || $line->credit < 0) {
                throw new InvalidArgumentException('Ditemukan nilai debit atau kredit negatif pada baris jurnal.');
            }
        }

        if (bccomp((string) round($totalDebit, 2), (string) round($totalCredit, 2), 2) !== 0) {
            throw new InvalidArgumentException(sprintf(
                'Tidak dapat memposting jurnal tidak seimbang! Total Debit (Rp %s) != Total Kredit (Rp %s).',
                number_format($totalDebit, 2, ',', '.'),
                number_format($totalCredit, 2, ',', '.')
            ));
        }

        $journal->update([
            'status' => 'POSTED',
            'posted_at' => now(),
        ]);

        return $journal;
    }

    /**
     * Approve and disburse an employee reimbursement claim with auto-journaling.
     *
     * @param Reimbursement $reimbursement
     * @param int $cashBankAccountId
     * @param int|null $expenseAccountId
     * @param User|null $approver
     * @return JournalEntry
     * @throws InvalidArgumentException
     */
    public function disburseReimbursement(
        Reimbursement $reimbursement,
        int $cashBankAccountId,
        ?int $expenseAccountId = null,
        ?User $approver = null
    ): JournalEntry {
        if ($reimbursement->status === 'PAID') {
            throw new InvalidArgumentException('Klaim reimbursement ini sudah pernah dicairkan.');
        }

        $cashAccount = ChartOfAccount::findOrFail($cashBankAccountId);

        // Cari akun beban default jika tidak disediakan (5104 Beban Reimbursement)
        if (!$expenseAccountId) {
            $expenseAccount = ChartOfAccount::where('account_code', '5104')->first()
                ?? ChartOfAccount::where('classification', 'EXPENSE')->where('is_active', true)->first();

            if (!$expenseAccount) {
                throw new InvalidArgumentException('Akun beban operasional / reimbursement tidak ditemukan.');
            }
            $expenseAccountId = $expenseAccount->id;
        } else {
            $expenseAccount = ChartOfAccount::findOrFail($expenseAccountId);
        }

        $amount = (float) $reimbursement->amount;
        if ($amount <= 0) {
            throw new InvalidArgumentException('Nominal reimbursement harus lebih besar dari nol.');
        }

        $description = sprintf(
            'Pencairan Reimbursement #%s: %s (Karyawan: %s)',
            $reimbursement->claim_number,
            $reimbursement->title,
            $reimbursement->employee?->full_name ?? 'Pegawai'
        );

        $lines = [
            [
                'chart_of_account_id' => $expenseAccountId,
                'description' => "Beban: {$reimbursement->title}",
                'debit' => $amount,
                'credit' => 0.0,
            ],
            [
                'chart_of_account_id' => $cashBankAccountId,
                'description' => "Kas Keluar: {$reimbursement->claim_number}",
                'debit' => 0.0,
                'credit' => $amount,
            ],
        ];

        return DB::transaction(function () use ($reimbursement, $description, $lines, $approver) {
            $journal = $this->createBalancedJournal(
                description: $description,
                lines: $lines,
                refType: 'Reimbursement',
                refId: $reimbursement->id,
                status: 'POSTED',
                createdBy: $approver?->id ?? auth()->id()
            );

            $reimbursement->update([
                'status' => 'PAID',
                'approver_id' => $approver?->id ?? auth()->id(),
                'paid_at' => now(),
            ]);

            return $journal;
        });
    }

    /**
     * Get high-level financial metrics for the executive finance dashboard.
     *
     * @return array
     */
    public function getFinancialMetrics(): array
    {
        // 1. Total Kas & Bank (Akun Aset bertipe Kas/Bank: 1101, 1102, 1103 atau kode diawali 110)
        $cashBankAccounts = ChartOfAccount::where('classification', 'ASSET')
            ->where(function ($q) {
                $q->where('account_code', 'like', '110%')
                  ->orWhere('account_name', 'ilike', '%kas%')
                  ->orWhere('account_name', 'ilike', '%bank%');
            })
            ->pluck('id');

        $cashBankBalance = 0.0;
        if ($cashBankAccounts->isNotEmpty()) {
            $debits = JournalLine::whereIn('chart_of_account_id', $cashBankAccounts)
                ->whereHas('journalEntry', fn ($q) => $q->where('status', 'POSTED'))
                ->sum('debit');

            $credits = JournalLine::whereIn('chart_of_account_id', $cashBankAccounts)
                ->whereHas('journalEntry', fn ($q) => $q->where('status', 'POSTED'))
                ->sum('credit');

            $cashBankBalance = (float) ($debits - $credits);
        }

        // 2. Pengeluaran Bulan Ini (Akun Beban / EXPENSE)
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();

        $expenseAccounts = ChartOfAccount::where('classification', 'EXPENSE')->pluck('id');
        $monthlyExpense = 0.0;
        if ($expenseAccounts->isNotEmpty()) {
            $expenseDebits = JournalLine::whereIn('chart_of_account_id', $expenseAccounts)
                ->whereHas('journalEntry', function ($q) use ($startOfMonth, $endOfMonth) {
                    $q->where('status', 'POSTED')
                      ->whereBetween('date', [$startOfMonth, $endOfMonth]);
                })
                ->sum('debit');

            $expenseCredits = JournalLine::whereIn('chart_of_account_id', $expenseAccounts)
                ->whereHas('journalEntry', function ($q) use ($startOfMonth, $endOfMonth) {
                    $q->where('status', 'POSTED')
                      ->whereBetween('date', [$startOfMonth, $endOfMonth]);
                })
                ->sum('credit');

            $monthlyExpense = (float) ($expenseDebits - $expenseCredits);
        }

        // 3. Pendapatan Bulan Ini (Akun Pendapatan / REVENUE)
        $revenueAccounts = ChartOfAccount::where('classification', 'REVENUE')->pluck('id');
        $monthlyRevenue = 0.0;
        if ($revenueAccounts->isNotEmpty()) {
            $revCredits = JournalLine::whereIn('chart_of_account_id', $revenueAccounts)
                ->whereHas('journalEntry', function ($q) use ($startOfMonth, $endOfMonth) {
                    $q->where('status', 'POSTED')
                      ->whereBetween('date', [$startOfMonth, $endOfMonth]);
                })
                ->sum('credit');

            $revDebits = JournalLine::whereIn('chart_of_account_id', $revenueAccounts)
                ->whereHas('journalEntry', function ($q) use ($startOfMonth, $endOfMonth) {
                    $q->where('status', 'POSTED')
                      ->whereBetween('date', [$startOfMonth, $endOfMonth]);
                })
                ->sum('debit');

            $monthlyRevenue = (float) ($revCredits - $revDebits);
        }

        // 4. Pending Reimbursement
        $pendingReimbursementsCount = Reimbursement::where('status', 'PENDING')->count();
        $pendingReimbursementsAmount = (float) Reimbursement::where('status', 'PENDING')->sum('amount');

        // 5. Recent Journals
        $recentJournals = JournalEntry::with(['lines.chartOfAccount', 'createdBy'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(7)
            ->get();

        // 6. Recent Reimbursements
        $recentReimbursements = Reimbursement::with(['employee.department', 'approver'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // 7. General Stats
        $totalJournalsCount = JournalEntry::count();
        $postedJournalsCount = JournalEntry::where('status', 'POSTED')->count();
        $draftJournalsCount = JournalEntry::where('status', 'DRAFT')->count();

        return [
            'cash_bank_balance' => $cashBankBalance,
            'monthly_expense' => $monthlyExpense,
            'monthly_revenue' => $monthlyRevenue,
            'pending_reimbursements_count' => $pendingReimbursementsCount,
            'pending_reimbursements_amount' => $pendingReimbursementsAmount,
            'recent_journals' => $recentJournals,
            'recent_reimbursements' => $recentReimbursements,
            'total_journals_count' => $totalJournalsCount,
            'posted_journals_count' => $postedJournalsCount,
            'draft_journals_count' => $draftJournalsCount,
        ];
    }

    /**
     * Get General Ledger (Buku Besar) for a specific account with running balance.
     *
     * @param int $accountId
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getGeneralLedger(int $accountId, ?string $startDate = null, ?string $endDate = null): array
    {
        $account = ChartOfAccount::findOrFail($accountId);
        $isDebitNormal = strtoupper($account->normal_balance) === 'DEBIT';

        // 1. Saldo Awal sebelum startDate
        $openingBalance = 0.0;
        if ($startDate) {
            $priorLines = JournalLine::where('chart_of_account_id', $accountId)
                ->whereHas('journalEntry', function ($q) use ($startDate) {
                    $q->where('status', 'POSTED')
                      ->where('date', '<', $startDate);
                })
                ->get();

            $priorDebit = (float) $priorLines->sum('debit');
            $priorCredit = (float) $priorLines->sum('credit');

            $openingBalance = $isDebitNormal ? ($priorDebit - $priorCredit) : ($priorCredit - $priorDebit);
        }

        // 2. Transaksi dalam rentang tanggal
        $query = JournalLine::where('chart_of_account_id', $accountId)
            ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'POSTED');
                if ($startDate) {
                    $q->where('date', '>=', $startDate);
                }
                if ($endDate) {
                    $q->where('date', '<=', $endDate);
                }
            })
            ->with('journalEntry')
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->orderBy('journal_entries.date', 'asc')
            ->orderBy('journal_entries.id', 'asc')
            ->select('journal_lines.*');

        $lines = $query->get();

        // 3. Hitung running balance
        $runningBalance = $openingBalance;
        $totalPeriodDebit = 0.0;
        $totalPeriodCredit = 0.0;
        $transactions = [];

        foreach ($lines as $line) {
            $debit = (float) $line->debit;
            $credit = (float) $line->credit;

            $totalPeriodDebit += $debit;
            $totalPeriodCredit += $credit;

            if ($isDebitNormal) {
                $runningBalance += ($debit - $credit);
            } else {
                $runningBalance += ($credit - $debit);
            }

            $transactions[] = [
                'line_id' => $line->id,
                'entry_id' => $line->journalEntry->id,
                'entry_number' => $line->journalEntry->entry_number,
                'date' => $line->journalEntry->date->toDateString(),
                'description' => $line->description ?: $line->journalEntry->description,
                'debit' => $debit,
                'credit' => $credit,
                'running_balance' => $runningBalance,
            ];
        }

        return [
            'account' => $account,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'opening_balance' => $openingBalance,
            'transactions' => $transactions,
            'total_debit' => $totalPeriodDebit,
            'total_credit' => $totalPeriodCredit,
            'closing_balance' => $runningBalance,
        ];
    }

    /**
     * Generate Trial Balance (Neraca Saldo).
     *
     * @param string|null $asOfDate
     * @return array
     */
    public function getTrialBalance(?string $asOfDate = null): array
    {
        $asOf = $asOfDate ? Carbon::parse($asOfDate)->toDateString() : now()->toDateString();

        // Ambil seluruh akun postable (Level 3 yang memiliki transaksi atau tanpa anak)
        $accounts = ChartOfAccount::orderBy('account_code')->get();

        $rows = [];
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($accounts as $account) {
            // Hitung mutasi dari jurnal POSTED up to asOf
            $lines = JournalLine::where('chart_of_account_id', $account->id)
                ->whereHas('journalEntry', function ($q) use ($asOf) {
                    $q->where('status', 'POSTED')
                      ->where('date', '<=', $asOf);
                })
                ->get();

            $sumDebit = (float) $lines->sum('debit');
            $sumCredit = (float) $lines->sum('credit');

            // Skip jika akun header murni tanpa transaksi
            if ($sumDebit == 0 && $sumCredit == 0 && $account->children()->exists()) {
                continue;
            }

            $net = $sumDebit - $sumCredit;
            $rowDebit = 0.0;
            $rowCredit = 0.0;

            if (strtoupper($account->normal_balance) === 'DEBIT') {
                if ($net >= 0) {
                    $rowDebit = $net;
                } else {
                    $rowCredit = abs($net);
                }
            } else {
                $netCredit = $sumCredit - $sumDebit;
                if ($netCredit >= 0) {
                    $rowCredit = $netCredit;
                } else {
                    $rowDebit = abs($netCredit);
                }
            }

            $totalDebit += $rowDebit;
            $totalCredit += $rowCredit;

            $rows[] = [
                'id' => $account->id,
                'account_code' => $account->account_code,
                'account_name' => $account->account_name,
                'classification' => $account->classification,
                'normal_balance' => $account->normal_balance,
                'debit' => $rowDebit,
                'credit' => $rowCredit,
            ];
        }

        $isBalanced = bccomp((string) round($totalDebit, 2), (string) round($totalCredit, 2), 2) === 0;

        return [
            'as_of_date' => $asOf,
            'rows' => $rows,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'difference' => abs($totalDebit - $totalCredit),
            'is_balanced' => $isBalanced,
        ];
    }

    /**
     * Generate sequential unique entry number: JV-YYYYMMDD-XXXX
     *
     * @param string $date
     * @return string
     */
    protected function generateEntryNumber(string $date): string
    {
        $dateStr = Carbon::parse($date)->format('Ymd');
        $prefix = "JV-{$dateStr}-";

        $latest = JournalEntry::where('entry_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $lastSeq = (int) substr($latest->entry_number, -4);
            $seq = str_pad((string) ($lastSeq + 1), 4, '0', STR_PAD_LEFT);
        } else {
            $seq = '0001';
        }

        return "{$prefix}{$seq}";
    }
}
