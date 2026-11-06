<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\ChartOfAccount;
use App\Models\Finance\Reimbursement;
use App\Services\Finance\AccountingService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReimbursementController extends Controller
{
    public function __construct(
        protected AccountingService $accountingService
    ) {}

    /**
     * Display list of reimbursement claims.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $isApprover = $user->hasRole(['super_admin', 'finance_officer', 'director']);

        $status = $request->query('status');
        $search = $request->query('search');

        $query = Reimbursement::with(['employee.department', 'approver'])
            ->orderBy('created_at', 'desc');

        // Jika bukan staf finance/admin, batasi hanya klaim pribadi
        if (!$isApprover) {
            $employeeId = $user->employee?->id ?? 0;
            $query->where('employee_id', $employeeId);
        }

        if ($status && in_array($status, ['PENDING', 'APPROVED', 'PAID', 'REJECTED'])) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('claim_number', 'ilike', "%{$search}%")
                  ->orWhere('title', 'ilike', "%{$search}%")
                  ->orWhereHas('employee', function ($eq) use ($search) {
                      $eq->where('full_name', 'ilike', "%{$search}%");
                  });
            });
        }

        $reimbursements = $query->paginate(15)->withQueryString();

        // Akun Kas & Bank untuk modal approval pencairan
        $cashBankAccounts = ChartOfAccount::where('classification', 'ASSET')
            ->where(function ($q) {
                $q->where('account_code', 'like', '110%')
                  ->orWhere('account_name', 'ilike', '%kas%')
                  ->orWhere('account_name', 'ilike', '%bank%');
            })
            ->where('is_active', true)
            ->orderBy('account_code')
            ->get();

        // Akun Beban untuk opsional pilihan akun beban
        $expenseAccounts = ChartOfAccount::where('classification', 'EXPENSE')
            ->where('is_active', true)
            ->orderBy('account_code')
            ->get();

        return view('finance.reimbursements.index', compact(
            'reimbursements',
            'isApprover',
            'status',
            'search',
            'cashBankAccounts',
            'expenseAccounts'
        ));
    }

    /**
     * Show form to submit a new reimbursement claim.
     */
    public function create(): View|RedirectResponse
    {
        $user = auth()->user();
        if (!$user->employee) {
            return redirect()->route('reimbursements.index')
                ->with('error', 'Profil karyawan belum ditemukan untuk akun Anda. Harap hubungi HR/Admin.');
        }

        return view('finance.reimbursements.create');
    }

    /**
     * Store submitted reimbursement claim with uploaded receipt.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->employee) {
            return redirect()->route('reimbursements.index')
                ->with('error', 'Profil karyawan belum ditemukan untuk akun Anda.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'amount' => ['required', 'numeric', 'min:1000', 'max:100000000'],
            'receipt' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'], // Max 5MB
        ], [
            'amount.min' => 'Nominal reimbursement minimal Rp 1.000.',
            'receipt.required' => 'Wajib mengunggah bukti nota / kuitansi fisik pengeluaran.',
            'receipt.mimes' => 'Format file kuitansi harus berupa PDF, JPG, JPEG, PNG, atau WEBP.',
            'receipt.max' => 'Ukuran file kuitansi tidak boleh lebih dari 5 MB.',
        ]);

        // Simpan file ke private storage
        $receiptPath = $request->file('receipt')->store('finance/receipts', 'local');

        $claimNumber = $this->generateClaimNumber();

        Reimbursement::create([
            'employee_id' => $user->employee->id,
            'claim_number' => $claimNumber,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'amount' => $validated['amount'],
            'receipt_path' => $receiptPath,
            'status' => 'PENDING',
        ]);

        return redirect()->route('reimbursements.index')
            ->with('success', "Pengajuan reimbursement #{$claimNumber} berhasil diajukan dan menunggu verifikasi Finance.");
    }

    /**
     * Approve and disburse reimbursement claim with automatic journal posting.
     */
    public function approve(Request $request, Reimbursement $reimbursement): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->hasRole(['super_admin', 'finance_officer', 'director'])) {
            abort(403, 'Akses ditolak. Anda tidak memiliki wewenang untuk menyetujui klaim reimbursement.');
        }

        $validated = $request->validate([
            'cash_account_id' => ['required', 'exists:chart_of_accounts,id'],
            'expense_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
        ], [
            'cash_account_id.required' => 'Pilih akun Kas/Bank yang digunakan untuk pencairan dana.',
        ]);

        try {
            $journal = $this->accountingService->disburseReimbursement(
                reimbursement: $reimbursement,
                cashBankAccountId: (int) $validated['cash_account_id'],
                expenseAccountId: !empty($validated['expense_account_id']) ? (int) $validated['expense_account_id'] : null,
                approver: $user
            );

            return redirect()->route('reimbursements.index')
                ->with('success', "Klaim reimbursement #{$reimbursement->claim_number} berhasil dicairkan! Jurnal Kas Keluar #{$journal->entry_number} telah diposting otomatis.");
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memproses pencairan: ' . $e->getMessage());
        }
    }

    /**
     * Reject reimbursement claim with notes.
     */
    public function reject(Request $request, Reimbursement $reimbursement): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->hasRole(['super_admin', 'finance_officer', 'director'])) {
            abort(403, 'Akses ditolak. Anda tidak memiliki wewenang untuk menolak klaim reimbursement.');
        }

        $validated = $request->validate([
            'notes' => ['required', 'string', 'max:500'],
        ], [
            'notes.required' => 'Wajib mencantumkan alasan / catatan penolakan klaim.',
        ]);

        $reimbursement->update([
            'status' => 'REJECTED',
            'notes' => $validated['notes'],
            'approver_id' => $user->id,
        ]);

        return redirect()->route('reimbursements.index')
            ->with('success', "Klaim reimbursement #{$reimbursement->claim_number} telah ditolak dengan catatan.");
    }

    /**
     * Download or view private receipt file securely.
     */
    public function receipt(Reimbursement $reimbursement): StreamedResponse|RedirectResponse
    {
        $user = auth()->user();
        $isOwner = $user->employee && $user->employee->id === $reimbursement->employee_id;
        $isStaff = $user->hasRole(['super_admin', 'finance_officer', 'director']);

        if (!$isOwner && !$isStaff) {
            abort(403, 'Akses file kuitansi ditolak.');
        }

        if (!$reimbursement->receipt_path || !Storage::disk('local')->exists($reimbursement->receipt_path)) {
            return back()->with('error', 'File kuitansi tidak ditemukan di penyimpanan server.');
        }

        return Storage::disk('local')->response($reimbursement->receipt_path);
    }

    /**
     * Generate unique sequential claim number: RMB-YYYYMMDD-XXXX
     */
    protected function generateClaimNumber(): string
    {
        $dateStr = now()->format('Ymd');
        $prefix = "RMB-{$dateStr}-";

        $latest = Reimbursement::where('claim_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $lastSeq = (int) substr($latest->claim_number, -4);
            $seq = str_pad((string) ($lastSeq + 1), 4, '0', STR_PAD_LEFT);
        } else {
            $seq = '0001';
        }

        return "{$prefix}{$seq}";
    }
}
