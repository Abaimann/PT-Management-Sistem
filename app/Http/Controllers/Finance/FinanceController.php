<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\ChartOfAccount;
use App\Models\Finance\JournalEntry;
use App\Models\Finance\JournalLine;
use App\Services\Finance\AccountingService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class FinanceController extends Controller
{
    public function __construct(
        protected AccountingService $accountingService
    ) {}

    /**
     * Financial Overview Dashboard.
     */
    public function index(): View
    {
        $metrics = $this->accountingService->getFinancialMetrics();

        return view('finance.index', compact('metrics'));
    }

    /**
     * Chart of Accounts (CoA) Master Index.
     */
    public function accounts(Request $request): View
    {
        $classification = $request->query('classification');

        $query = ChartOfAccount::with(['parent', 'children'])->orderBy('account_code');
        if ($classification && in_array($classification, ['ASSET', 'LIABILITY', 'EQUITY', 'REVENUE', 'EXPENSE'])) {
            $query->where('classification', $classification);
        }

        $accounts = $query->get();

        // Hitung saldo berjalan untuk setiap akun
        $accountIds = $accounts->pluck('id');
        $lines = JournalLine::whereIn('chart_of_account_id', $accountIds)
            ->whereHas('journalEntry', fn ($q) => $q->where('status', 'POSTED'))
            ->get()
            ->groupBy('chart_of_account_id');

        $balances = [];
        foreach ($accounts as $acc) {
            $accLines = $lines->get($acc->id, collect());
            $deb = (float) $accLines->sum('debit');
            $crd = (float) $accLines->sum('credit');

            if (strtoupper($acc->normal_balance) === 'DEBIT') {
                $balances[$acc->id] = $deb - $crd;
            } else {
                $balances[$acc->id] = $crd - $deb;
            }
        }

        $parentAccounts = ChartOfAccount::whereNull('parent_id')
            ->orWhereIn('account_code', ['1100', '1200', '2100', '3100', '4100', '5100', '5200'])
            ->orderBy('account_code')
            ->get();

        return view('finance.accounts.index', compact('accounts', 'balances', 'classification', 'parentAccounts'));
    }

    /**
     * Store newly created Chart of Account.
     */
    public function storeAccount(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'account_code' => ['required', 'string', 'max:30', 'unique:chart_of_accounts,account_code'],
            'account_name' => ['required', 'string', 'max:255'],
            'classification' => ['required', 'string', 'in:ASSET,LIABILITY,EQUITY,REVENUE,EXPENSE'],
            'normal_balance' => ['required', 'string', 'in:DEBIT,CREDIT'],
            'parent_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : (bool) ($request->input('is_active', true));

        ChartOfAccount::create($validated);

        return redirect()->route('finance.accounts.index')
            ->with('success', "Akun CoA {$validated['account_code']} - {$validated['account_name']} berhasil ditambahkan.");
    }

    /**
     * Transaction Journals List.
     */
    public function journals(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = JournalEntry::with(['lines.chartOfAccount', 'createdBy'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc');

        if ($status && in_array($status, ['DRAFT', 'POSTED', 'VOID'])) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('entry_number', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        $journals = $query->paginate(15)->withQueryString();

        return view('finance.journals.index', compact('journals', 'status', 'search', 'startDate', 'endDate'));
    }

    /**
     * Show form to create a new double-entry journal.
     */
    public function createJournal(): View
    {
        $accounts = ChartOfAccount::where('is_active', true)
            ->orderBy('account_code')
            ->get();

        return view('finance.journals.create', compact('accounts'));
    }

    /**
     * Store a double-entry journal transaction.
     */
    public function storeJournal(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:500'],
            'action' => ['required', 'string', 'in:draft,post'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.chart_of_account_id' => ['required', 'exists:chart_of_accounts,id'],
            'lines.*.debit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.description' => ['nullable', 'string', 'max:255'],
        ], [
            'lines.min' => 'Transaksi jurnal minimal harus memiliki 2 baris (Debet dan Kredit).',
            'lines.*.chart_of_account_id.required' => 'Setiap baris wajib memilih akun Chart of Account.',
        ]);

        $status = $validated['action'] === 'draft' ? 'DRAFT' : 'POSTED';

        try {
            $journal = $this->accountingService->createBalancedJournal(
                description: $validated['description'],
                lines: $validated['lines'],
                status: $status,
                createdBy: auth()->id(),
                date: $validated['date']
            );

            $msg = $status === 'POSTED'
                ? "Jurnal #{$journal->entry_number} berhasil diposting ke Buku Besar!"
                : "Jurnal #{$journal->entry_number} berhasil disimpan sebagai Draft.";

            return redirect()->route('finance.journals.index')->with('success', $msg);
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Terjadi kesalahan sistem saat menyimpan jurnal: ' . $e->getMessage());
        }
    }

    /**
     * Authorize posting a draft journal.
     */
    public function postJournal(JournalEntry $journal): RedirectResponse
    {
        try {
            $this->accountingService->postJournal($journal);

            return redirect()->route('finance.journals.index')
                ->with('success', "Jurnal #{$journal->entry_number} berhasil diposting ke Buku Besar.");
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memposting jurnal: ' . $e->getMessage());
        }
    }

    /**
     * General Ledger (Buku Besar) Report.
     */
    public function generalLedger(Request $request): View
    {
        $accounts = ChartOfAccount::where('is_active', true)
            ->orderBy('account_code')
            ->get();

        $selectedAccountId = (int) $request->query('account_id', $accounts->first()?->id ?? 0);
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->endOfMonth()->toDateString());

        $ledgerData = null;
        if ($selectedAccountId > 0) {
            $ledgerData = $this->accountingService->getGeneralLedger($selectedAccountId, $startDate, $endDate);
        }

        return view('finance.reports.general_ledger', compact('accounts', 'selectedAccountId', 'startDate', 'endDate', 'ledgerData'));
    }

    /**
     * Trial Balance (Neraca Saldo) Report.
     */
    public function trialBalance(Request $request): View
    {
        $asOfDate = $request->query('as_of_date', now()->toDateString());
        $trialBalance = $this->accountingService->getTrialBalance($asOfDate);

        return view('finance.reports.trial_balance', compact('trialBalance', 'asOfDate'));
    }
}
