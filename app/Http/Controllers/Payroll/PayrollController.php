<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payroll\StorePayrollPeriodRequest;
use App\Models\Core\CompanySetting;
use App\Models\Payroll\PayrollPeriod;
use App\Models\Payroll\PayrollRun;
use App\Models\Payroll\Payslip;
use App\Services\Payroll\PayrollService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayrollController extends Controller
{
    public function __construct(
        protected PayrollService $payrollService
    ) {}

    /**
     * Display a listing of payroll periods or user payslips.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $canManagePayroll = $user->hasRole(['super_admin', 'payroll_officer', 'finance_officer', 'director'])
            || $user->hasPermission('payroll.view');

        $query = PayrollPeriod::with(['closedBy', 'latestPayrollRun']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $periods = $query->latest('start_date')->paginate(10)->withQueryString();

        $stats = [
            'total_periods' => PayrollPeriod::count(),
            'draft_periods' => PayrollPeriod::where('status', 'DRAFT')->count(),
            'calculated_periods' => PayrollPeriod::where('status', 'CALCULATED')->count(),
            'approved_periods' => PayrollPeriod::where('status', 'APPROVED')->count(),
            'closed_periods' => PayrollPeriod::where('status', 'CLOSED')->count(),
            'latest_payout' => PayrollRun::where('status', 'COMPLETED')->latest()->value('total_net_pay') ?? 0,
        ];

        return view('payroll.periods.index', compact('periods', 'stats', 'canManagePayroll'));
    }

    /**
     * Show form to create a new payroll period.
     */
    public function createPeriod(): View
    {
        $user = auth()->user();
        if (!$user->hasRole(['super_admin', 'payroll_officer', 'finance_officer', 'director']) && !$user->hasPermission('payroll.manage')) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk membuka periode payroll.');
        }

        // Suggestions for defaults
        $currentMonth = now();
        $suggestedCode = 'PR-' . $currentMonth->format('Ym');
        $suggestedName = 'Payroll Periode ' . $currentMonth->translatedFormat('F Y');
        $startDate = $currentMonth->copy()->startOfMonth()->toDateString();
        $endDate = $currentMonth->copy()->endOfMonth()->toDateString();
        $cutoffDate = $currentMonth->copy()->subDays(5)->toDateString();
        $paymentDate = $currentMonth->copy()->endOfMonth()->toDateString();

        return view('payroll.periods.create', compact(
            'suggestedCode',
            'suggestedName',
            'startDate',
            'endDate',
            'cutoffDate',
            'paymentDate'
        ));
    }

    /**
     * Store a newly created payroll period in storage.
     */
    public function storePeriod(StorePayrollPeriodRequest $request): RedirectResponse
    {
        $user = $request->user();
        if (!$user->hasRole(['super_admin', 'payroll_officer', 'finance_officer', 'director']) && !$user->hasPermission('payroll.manage')) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk membuka periode payroll.');
        }

        $validated = $request->validated();
        $validated['status'] = 'DRAFT';
        $validated['cutoff_start_date'] = $validated['cutoff_start_date'] ?? $validated['start_date'];
        $validated['cutoff_end_date'] = $validated['cutoff_end_date'] ?? $validated['cutoff_date'] ?? $validated['end_date'];
        $validated['cutoff_date'] = $validated['cutoff_date'] ?? $validated['cutoff_end_date'];

        $period = PayrollPeriod::create($validated);

        return redirect()->route('payroll.periods.show', $period)
            ->with('success', "Periode payroll '{$period->name}' berhasil dibuat dengan status DRAFT.");
    }

    /**
     * Display payroll period summary and employee payslips.
     */
    public function show(PayrollPeriod $period, Request $request): View
    {
        $user = $request->user();
        $canManagePayroll = $user->hasRole(['super_admin', 'payroll_officer', 'finance_officer', 'director'])
            || $user->hasPermission('payroll.view');

        if (!$canManagePayroll) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas untuk melihat rincian batch payroll.');
        }

        $latestRun = $period->payrollRuns()->with('processedBy')->latest()->first();

        $payslipsQuery = $latestRun
            ? $latestRun->payslips()->with(['employee.department', 'employee.position', 'items'])
            : Payslip::whereRaw('1 = 0');

        if ($request->filled('department_id')) {
            $payslipsQuery->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $payslipsQuery->whereHas('employee', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        $payslips = $payslipsQuery->paginate(15)->withQueryString();

        return view('payroll.periods.show', compact('period', 'latestRun', 'payslips'));
    }

    /**
     * Calculate draft payroll batch for the period.
     */
    public function calculate(PayrollPeriod $period): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->hasRole(['super_admin', 'payroll_officer', 'director']) && !$user->hasPermission('payroll.manage')) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas untuk mengeksekusi kalkulasi payroll.');
        }

        try {
            $run = $this->payrollService->calculatePeriod($period, $user);

            return redirect()->route('payroll.periods.show', $period)
                ->with('success', "Kalkulasi payroll berhasil dieksekusi untuk {$run->total_employees} karyawan. Total Net Pay: Rp " . number_format($run->total_net_pay, 0, ',', '.'));
        } catch (DomainException $e) {
            return redirect()->route('payroll.periods.show', $period)
                ->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return redirect()->route('payroll.periods.show', $period)
                ->with('error', 'Gagal memproses kalkulasi payroll: ' . $e->getMessage());
        }
    }

    /**
     * Approve payroll calculation by management or director.
     */
    public function approve(PayrollPeriod $period): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->hasRole(['super_admin', 'director', 'payroll_officer']) && !$user->hasPermission('payroll.approve')) {
            abort(403, 'Akses ditolak. Otorisasi persetujuan payroll hanya dapat dilakukan oleh Direksi atau Manajemen.');
        }

        try {
            $this->payrollService->approvePeriod($period, $user);

            return redirect()->route('payroll.periods.show', $period)
                ->with('success', "Periode payroll '{$period->name}' berhasil disetujui (APPROVED). Siap untuk penutupan.");
        } catch (DomainException $e) {
            return redirect()->route('payroll.periods.show', $period)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Close payroll period.
     * Enforces closing guard: must be CALCULATED and APPROVED.
     * Locks all attendance records and publishes payslips.
     */
    public function close(PayrollPeriod $period): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->hasRole(['super_admin', 'director', 'payroll_officer']) && !$user->hasPermission('payroll.approve')) {
            abort(403, 'Akses ditolak. Penutupan periode payroll membutuhkan otorisasi Direksi / Manajemen.');
        }

        try {
            $this->payrollService->closePeriod($period, $user);

            return redirect()->route('payroll.periods.show', $period)
                ->with('success', "Periode payroll '{$period->name}' telah resmi DITUTUP (CLOSED). Seluruh data absensi telah dikunci dan slip gaji telah diterbitkan.");
        } catch (DomainException $e) {
            return redirect()->route('payroll.periods.show', $period)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display an individual digital payslip.
     * Employees can view only their own payslip; payroll/admin/director can view all.
     */
    public function payslip(Payslip $payslip): View
    {
        $user = auth()->user();
        $canViewAll = $user->hasRole(['super_admin', 'payroll_officer', 'finance_officer', 'director'])
            || $user->hasPermission('payslip.view_all');

        $isOwnPayslip = $user->employee && ($user->employee->id === $payslip->employee_id);

        if (!$canViewAll && !$isOwnPayslip) {
            abort(403, 'Akses ditolak. Anda hanya diizinkan melihat slip gaji milik Anda sendiri.');
        }

        $payslip->load([
            'payrollRun.payrollPeriod',
            'employee.department',
            'employee.position',
            'items.salaryComponent',
        ]);

        $company = CompanySetting::first();

        return view('payroll.payslips.show', compact('payslip', 'company'));
    }

    /**
     * Employee self-service list of their own payslips.
     */
    public function myPayslips(Request $request): View
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            abort(403, 'Akun Anda tidak terhubung dengan data kepegawaian aktif.');
        }

        $payslips = Payslip::with(['payrollRun.payrollPeriod'])
            ->where('employee_id', $employee->id)
            ->whereIn('status', ['PUBLISHED', 'PAID', 'DRAFT'])
            ->latest()
            ->paginate(10);

        return view('payroll.payslips.my', compact('payslips', 'employee'));
    }
}
