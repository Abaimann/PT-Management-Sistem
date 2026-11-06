<?php

namespace App\Http\Controllers\HRIS;

use App\Http\Controllers\Controller;
use App\Models\Attendance\AttendanceRecord;
use App\Models\Core\AuditLog;
use App\Models\Core\Department;
use App\Models\HRIS\Employee;
use App\Models\HRIS\LeaveBalance;
use App\Models\HRIS\LeaveRequest;
use App\Models\HRIS\LeaveType;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LeaveController extends Controller
{
    /**
     * Display a listing of leave requests and balances.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $isApprover = $user->hasRole(['super_admin', 'hr_admin', 'supervisor', 'director']);

        $query = LeaveRequest::query()
            ->with(['employee.department', 'employee.position', 'leaveType', 'approver'])
            ->orderByDesc('created_at');

        // Normal employee only sees their own requests
        if (! $isApprover && $user->employee) {
            $query->where('employee_id', $user->employee->id);
        }

        // Filters
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($leaveTypeId = $request->input('leave_type_id')) {
            $query->where('leave_type_id', $leaveTypeId);
        }

        if ($departmentId = $request->input('department_id')) {
            $query->whereHas('employee', fn ($q) => $q->where('department_id', $departmentId));
        }

        $leaveRequests = $query->paginate(12)->withQueryString();
        $leaveTypes = LeaveType::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        // Calculate current user's balances
        $currentYear = Carbon::now()->year;
        $employeeBalances = collect();
        if ($user->employee) {
            $employeeBalances = LeaveBalance::with('leaveType')
                ->where('employee_id', $user->employee->id)
                ->where('year', $currentYear)
                ->get();
        }

        $stats = [
            'pending_count' => LeaveRequest::where('status', 'PENDING')->count(),
            'approved_month' => LeaveRequest::where('status', 'APPROVED')
                ->whereMonth('start_date', Carbon::now()->month)
                ->count(),
            'total_active_leaves_today' => LeaveRequest::where('status', 'APPROVED')
                ->whereDate('start_date', '<=', Carbon::today())
                ->whereDate('end_date', '>=', Carbon::today())
                ->count(),
        ];

        return view('leaves.index', compact('leaveRequests', 'leaveTypes', 'departments', 'employeeBalances', 'isApprover', 'stats'));
    }

    /**
     * Show the form for creating a new leave request.
     */
    public function create(): View|RedirectResponse
    {
        $user = auth()->user();
        $employee = $user->employee;

        if (! $employee) {
            if ($user->hasRole('super_admin')) {
                $employee = Employee::where('is_active', true)->first();
            }

            if (! $employee) {
                return redirect()->route('employees.create')
                    ->with('error', 'Akun Anda belum memiliki profil karyawan. Silakan daftarkan profil karyawan terlebih dahulu.');
            }
        }

        $currentYear = Carbon::now()->year;
        $leaveTypes = LeaveType::where('is_active', true)->orderBy('name')->get();

        // Ensure default leave balance exists for each type
        foreach ($leaveTypes as $type) {
            LeaveBalance::firstOrCreate(
                [
                    'employee_id' => $employee->id,
                    'leave_type_id' => $type->id,
                    'year' => $currentYear,
                ],
                [
                    'total_quota' => $type->default_quota,
                    'used_quota' => 0,
                    'remaining_quota' => $type->default_quota,
                ]
            );
        }

        $balances = LeaveBalance::with('leaveType')
            ->where('employee_id', $employee->id)
            ->where('year', $currentYear)
            ->get();

        return view('leaves.create', compact('employee', 'leaveTypes', 'balances'));
    }

    /**
     * Store a newly created leave request in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'leave_type_id' => ['required', 'integer', 'exists:leave_types,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
            'attachment' => ['nullable', 'file', 'max:5120'], // 5MB max
        ]);

        $user = auth()->user();
        $employee = $user->employee ?? Employee::where('is_active', true)->first();

        if (! $employee) {
            return back()->with('error', 'Profil karyawan tidak ditemukan.');
        }

        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'));
        $totalDays = (int) $startDate->diffInDays($endDate) + 1;

        $leaveType = LeaveType::findOrFail($request->input('leave_type_id'));
        $year = $startDate->year;

        // Check if attachment is required
        if ($leaveType->requires_attachment && ! $request->hasFile('attachment')) {
            return back()
                ->withErrors(['attachment' => "Jenis cuti '{$leaveType->name}' mewajibkan dokumen surat/bukti lampiran."])
                ->withInput();
        }

        // Validate Leave Balance
        $balance = LeaveBalance::firstOrCreate(
            [
                'employee_id' => $employee->id,
                'leave_type_id' => $leaveType->id,
                'year' => $year,
            ],
            [
                'total_quota' => $leaveType->default_quota,
                'used_quota' => 0,
                'remaining_quota' => $leaveType->default_quota,
            ]
        );

        if ($balance->remaining_quota < $totalDays) {
            return back()
                ->withErrors([
                    'start_date' => "Sisa kuota cuti Anda ({$balance->remaining_quota} hari) tidak mencukupi untuk durasi pengajuan {$totalDays} hari.",
                ])
                ->withInput();
        }

        // Upload Attachment to Private Disk
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = sprintf('leave_%s_%s.%s', $employee->nik, uniqid(), $file->getClientOriginalExtension());
            $attachmentPath = $file->storeAs('leaves/attachments', $filename, 'local');
        }

        $leaveRequest = DB::transaction(function () use ($employee, $leaveType, $startDate, $endDate, $totalDays, $request, $attachmentPath) {
            $created = LeaveRequest::create([
                'employee_id' => $employee->id,
                'leave_type_id' => $leaveType->id,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'total_days' => $totalDays,
                'reason' => $request->input('reason'),
                'attachment_path' => $attachmentPath,
                'status' => 'PENDING',
            ]);

            AuditLog::create([
                'actor_id' => auth()->id(),
                'event' => 'create_leave_request',
                'auditable_type' => LeaveRequest::class,
                'auditable_id' => $created->id,
                'old_values' => null,
                'new_values' => $created->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);

            return $created;
        });

        return redirect()->route('leaves.index')
            ->with('success', "Permohonan cuti '{$leaveType->name}' selama {$totalDays} hari berhasil diajukan dan sedang menunggu persetujuan atasan.");
    }

    /**
     * Approve a leave request and deduct leave balance automatically.
     */
    public function approve(LeaveRequest $leaveRequest): RedirectResponse
    {
        $user = auth()->user();
        if (! $user->hasRole(['super_admin', 'hr_admin', 'supervisor', 'director'])) {
            abort(403, 'Akses ditolak. Anda tidak memiliki wewenang untuk menyetujui permohonan cuti.');
        }

        if ($leaveRequest->status !== 'PENDING') {
            return back()->with('error', 'Permohonan cuti ini sudah tidak dalam status pending.');
        }

        DB::transaction(function () use ($leaveRequest) {
            $user = auth()->user();
            $year = Carbon::parse($leaveRequest->start_date)->year;

            // 1. Deduct Leave Balance
            $balance = LeaveBalance::where('employee_id', $leaveRequest->employee_id)
                ->where('leave_type_id', $leaveRequest->leave_type_id)
                ->where('year', $year)
                ->first();

            if ($balance) {
                $balance->decrement('remaining_quota', $leaveRequest->total_days);
                $balance->increment('used_quota', $leaveRequest->total_days);
            }

            // 2. Mark Approved Status
            $leaveRequest->update([
                'status' => 'APPROVED',
                'approver_id' => $user->id,
                'approved_at' => now(),
            ]);

            // 3. Auto-populate Attendance records with 'LEAVE'
            $start = Carbon::parse($leaveRequest->start_date);
            $end = Carbon::parse($leaveRequest->end_date);
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                AttendanceRecord::updateOrCreate(
                    [
                        'employee_id' => $leaveRequest->employee_id,
                        'date' => $d->toDateString(),
                    ],
                    [
                        'status' => 'LEAVE',
                        'is_locked' => true,
                        'notes' => 'Cuti: '.$leaveRequest->leaveType->name.' ('.$leaveRequest->reason.')',
                    ]
                );
            }

            // 4. Audit Log
            AuditLog::create([
                'actor_id' => $user->id,
                'event' => 'approve_leave_request',
                'auditable_type' => LeaveRequest::class,
                'auditable_id' => $leaveRequest->id,
                'old_values' => ['status' => 'PENDING'],
                'new_values' => ['status' => 'APPROVED', 'approved_at' => now()],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);
        });

        return redirect()->route('leaves.index')
            ->with('success', "Permohonan cuti karyawan '{$leaveRequest->employee->full_name}' berhasil disetujui. Kuota cuti telah otomatis dipotong.");
    }

    /**
     * Reject a leave request with mandatory reason.
     */
    public function reject(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $user = auth()->user();
        if (! $user->hasRole(['super_admin', 'hr_admin', 'supervisor', 'director'])) {
            abort(403, 'Akses ditolak. Anda tidak memiliki wewenang untuk menolak permohonan cuti.');
        }

        $request->validate([
            'rejection_reason' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        if ($leaveRequest->status !== 'PENDING') {
            return back()->with('error', 'Permohonan cuti ini sudah tidak dalam status pending.');
        }

        DB::transaction(function () use ($request, $leaveRequest) {
            $user = auth()->user();

            $leaveRequest->update([
                'status' => 'REJECTED',
                'approver_id' => $user->id,
                'rejection_reason' => $request->input('rejection_reason'),
            ]);

            AuditLog::create([
                'actor_id' => $user->id,
                'event' => 'reject_leave_request',
                'auditable_type' => LeaveRequest::class,
                'auditable_id' => $leaveRequest->id,
                'old_values' => ['status' => 'PENDING'],
                'new_values' => ['status' => 'REJECTED', 'rejection_reason' => $request->input('rejection_reason')],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        });

        return redirect()->route('leaves.index')
            ->with('info', "Permohonan cuti karyawan '{$leaveRequest->employee->full_name}' telah ditolak.");
    }
}
