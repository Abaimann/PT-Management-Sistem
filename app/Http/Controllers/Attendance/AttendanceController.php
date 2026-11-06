<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance\AttendanceCorrection;
use App\Models\Attendance\AttendanceRecord;
use App\Models\Core\AuditLog;
use App\Models\Core\Department;
use App\Models\HRIS\Employee;
use App\Services\Attendance\AttendanceService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    /**
     * Display a listing of attendance records.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = AttendanceRecord::query()
            ->with(['employee.department', 'employee.position', 'shift'])
            ->orderByDesc('date')
            ->orderByDesc('clock_in');

        // Non-admin / employee sees their own records only
        if (! $user->hasRole(['super_admin', 'hr_admin', 'director'])) {
            if ($user->employee) {
                $query->where('employee_id', $user->employee->id);
            }
        }

        // Filters
        if ($date = $request->input('date')) {
            $query->whereDate('date', $date);
        } else {
            // Default to current month or today if not filtered
            if (! $request->has('all_time')) {
                $query->whereDate('date', '>=', Carbon::today()->subDays(30));
            }
        }

        if ($departmentId = $request->input('department_id')) {
            $query->whereHas('employee', fn ($q) => $q->where('department_id', $departmentId));
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $records = $query->paginate(15)->withQueryString();
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        $today = Carbon::today()->toDateString();
        $stats = [
            'today_total' => AttendanceRecord::whereDate('date', $today)->count(),
            'today_on_time' => AttendanceRecord::whereDate('date', $today)->where('status', 'ON_TIME')->count(),
            'today_late' => AttendanceRecord::whereDate('date', $today)->where('status', 'LATE')->count(),
            'today_out_of_radius' => AttendanceRecord::whereDate('date', $today)->where('status', 'OUT_OF_RADIUS')->count(),
        ];

        return view('attendance.index', compact('records', 'departments', 'stats'));
    }

    /**
     * Show WebRTC Smart Camera & Geofence Scanning Interface.
     */
    public function create(): View|RedirectResponse
    {
        $user = auth()->user();
        $employee = $user->employee;

        if (! $employee) {
            // For super admins without employee profile, get first active employee for testing
            if ($user->hasRole('super_admin')) {
                $employee = Employee::where('is_active', true)->first();
            }

            if (! $employee) {
                return redirect()->route('employees.create')
                    ->with('error', 'Akun Anda belum terhubung dengan profil karyawan. Silakan buat profil karyawan terlebih dahulu.');
            }
        }

        $today = Carbon::today('Asia/Jakarta')->toDateString();
        $record = AttendanceRecord::where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->first();

        $company = $this->attendanceService->getCompanySetting();
        $shift = $this->attendanceService->getEmployeeShift($employee, Carbon::now('Asia/Jakarta'));

        return view('attendance.scan', compact('employee', 'record', 'company', 'shift'));
    }

    /**
     * Process employee check-in request.
     */
    public function checkIn(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'selfie' => ['required'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $user = auth()->user();
        $employee = $user->employee ?? Employee::where('is_active', true)->first();

        if (! $employee) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Profil karyawan tidak ditemukan.'], 404);
            }

            return back()->with('error', 'Profil karyawan tidak ditemukan.');
        }

        try {
            $record = $this->attendanceService->checkIn($employee, $request->all());

            $message = sprintf(
                'Check-in berhasil tercatat pada %s. Status: %s (%s).',
                $record->clock_in,
                $record->status,
                $record->status === 'ON_TIME' ? 'Tepat Waktu' : ($record->status === 'LATE' ? "Terlambat {$record->late_minutes} menit" : 'Di Luar Radius Kantor')
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => $record,
                ]);
            }

            return redirect()->route('attendance.index')->with('success', $message);
        } catch (Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Process employee check-out request.
     */
    public function checkOut(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'selfie' => ['nullable'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $user = auth()->user();
        $employee = $user->employee ?? Employee::where('is_active', true)->first();

        if (! $employee) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Profil karyawan tidak ditemukan.'], 404);
            }

            return back()->with('error', 'Profil karyawan tidak ditemukan.');
        }

        try {
            $record = $this->attendanceService->checkOut($employee, $request->all());

            $message = sprintf('Check-out berhasil tercatat pada %s. Terima kasih atas dedikasi kerja Anda hari ini!', $record->clock_out);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => $record,
                ]);
            }

            return redirect()->route('attendance.index')->with('success', $message);
        } catch (Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display listing of attendance corrections.
     */
    public function corrections(Request $request): View
    {
        $user = auth()->user();
        $query = AttendanceCorrection::query()
            ->with(['employee.department', 'attendanceRecord', 'approver'])
            ->orderByDesc('created_at');

        if (! $user->hasRole(['super_admin', 'hr_admin', 'supervisor', 'director'])) {
            if ($user->employee) {
                $query->where('employee_id', $user->employee->id);
            }
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $corrections = $query->paginate(15)->withQueryString();

        // List today's attendance records of current employee for easy dropdown selection
        $employeeRecords = collect();
        if ($user->employee) {
            $employeeRecords = AttendanceRecord::where('employee_id', $user->employee->id)
                ->orderByDesc('date')
                ->limit(10)
                ->get();
        }

        return view('attendance.corrections', compact('corrections', 'employeeRecords'));
    }

    /**
     * Submit an attendance correction request.
     */
    public function storeCorrection(Request $request): RedirectResponse
    {
        $request->validate([
            'attendance_record_id' => ['required', 'integer', 'exists:attendance_records,id'],
            'proposed_clock_in' => ['nullable', 'date_format:H:i'],
            'proposed_clock_out' => ['nullable', 'date_format:H:i'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
            'attachment' => ['nullable', 'file', 'max:5120'], // 5MB max
        ]);

        $record = AttendanceRecord::findOrFail($request->input('attendance_record_id'));

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = 'correction_'.uniqid().'.'.$file->getClientOriginalExtension();
            $attachmentPath = $file->storeAs('attendance/corrections', $filename, 'local');
        }

        $correction = AttendanceCorrection::create([
            'attendance_record_id' => $record->id,
            'employee_id' => $record->employee_id,
            'proposed_clock_in' => $request->input('proposed_clock_in'),
            'proposed_clock_out' => $request->input('proposed_clock_out'),
            'reason' => $request->input('reason'),
            'attachment_path' => $attachmentPath,
            'status' => 'PENDING',
        ]);

        AuditLog::create([
            'actor_id' => auth()->id(),
            'event' => 'create_attendance_correction',
            'auditable_type' => AttendanceCorrection::class,
            'auditable_id' => $correction->id,
            'old_values' => null,
            'new_values' => $correction->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('attendance.corrections')
            ->with('success', 'Permohonan koreksi absensi berhasil diajukan dan sedang menunggu review atasan / HRD.');
    }

    /**
     * Approve or reject an attendance correction.
     */
    public function approveCorrection(Request $request, AttendanceCorrection $correction): RedirectResponse
    {
        $user = auth()->user();
        if (! $user->hasRole(['super_admin', 'hr_admin', 'supervisor', 'director'])) {
            abort(403, 'Akses ditolak. Anda tidak memiliki wewenang untuk menyetujui koreksi absensi.');
        }

        $request->validate([
            'action' => ['required', 'in:approve,reject'],
            'rejection_reason' => ['nullable', 'required_if:action,reject', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($request, $correction) {
            $user = auth()->user();

            if ($request->input('action') === 'approve') {
                $record = $correction->attendanceRecord;

                if ($record) {
                    if ($correction->proposed_clock_in) {
                        $record->clock_in = $correction->proposed_clock_in;
                        $record->status = 'ON_TIME';
                        $record->late_minutes = 0;
                    }

                    if ($correction->proposed_clock_out) {
                        $record->clock_out = $correction->proposed_clock_out;
                    }

                    $record->notes = ($record->notes ? $record->notes.' | ' : '').'Koreksi disetujui oleh HRD';
                    $record->save();
                }

                $correction->update([
                    'status' => 'APPROVED',
                    'approver_id' => $user->id,
                    'approved_at' => now(),
                ]);
            } else {
                $correction->update([
                    'status' => 'REJECTED',
                    'approver_id' => $user->id,
                    'rejection_reason' => $request->input('rejection_reason'),
                ]);
            }

            AuditLog::create([
                'actor_id' => $user->id,
                'event' => 'review_attendance_correction',
                'auditable_type' => AttendanceCorrection::class,
                'auditable_id' => $correction->id,
                'old_values' => null,
                'new_values' => $correction->fresh()->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        });

        $statusText = $request->input('action') === 'approve' ? 'disetujui' : 'ditolak';

        return redirect()->route('attendance.corrections')
            ->with('success', "Pengajuan koreksi absensi berhasil {$statusText}.");
    }
}
