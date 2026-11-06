<?php

namespace App\Http\Controllers;

use App\Models\Attendance\AttendanceRecord;
use App\Models\Core\AuditLog;
use App\Models\Core\Department;
use App\Models\Core\Position;
use App\Models\Core\User;
use App\Models\HRIS\Employee;
use App\Models\Operations\Task;
use App\Models\Payroll\PayrollRun;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the main ERP dashboard with role-based statistics.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = auth()->user();
        $user->loadMissing(['roles', 'employee.department', 'employee.position']);

        $currentEmployee = $user->employee;
        $primaryRole = $user->roles->first()?->code ?? 'employee';

        // 1. General Organization KPIs
        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('is_active', true)->count();
        $totalDepartments = Department::where('is_active', true)->count();
        $totalPositions = Position::where('is_active', true)->count();

        // 2. Attendance Statistics (Today)
        $today = Carbon::today()->toDateString();
        $attendanceToday = AttendanceRecord::whereDate('date', $today)->get();
        $presentTodayCount = $attendanceToday->whereIn('status', ['ON_TIME', 'PRESENT'])->count();
        $lateTodayCount = $attendanceToday->where('status', 'LATE')->count();
        $onLeaveTodayCount = $attendanceToday->whereIn('status', ['LEAVE', 'SICK'])->count();
        $totalCheckedIn = $presentTodayCount + $lateTodayCount;

        $attendanceRate = $activeEmployees > 0
            ? round(($totalCheckedIn / $activeEmployees) * 100, 1)
            : 0;

        // 3. Operational Tasks Statistics
        $tasksTodoCount = Task::whereIn('status', ['TODO', 'PENDING'])->count();
        $tasksInProgressCount = Task::where('status', 'IN_PROGRESS')->count();
        $tasksVerificationCount = Task::where('status', 'WAITING_VERIFICATION')->count();
        $tasksCompletedCount = Task::where('status', 'APPROVED')->count();
        $totalTasks = Task::count();

        // 4. Payroll Statistics
        $draftPayrollRunsCount = PayrollRun::where('status', 'DRAFT')->count();
        $latestPayrollRun = PayrollRun::latest('run_date')->first();

        // 5. Chart 1: Employee Distribution by Department
        $departmentsDistribution = Department::withCount('employees')
            ->where('is_active', true)
            ->orderByDesc('employees_count')
            ->limit(8)
            ->get();

        $deptChartLabels = $departmentsDistribution->pluck('name')->toArray();
        $deptChartData = $departmentsDistribution->pluck('employees_count')->toArray();

        // 6. Chart 2: 7-Day Attendance Trend
        $attendanceTrendLabels = [];
        $attendanceTrendPresent = [];
        $attendanceTrendLate = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $formattedDate = $date->isoFormat('D MMM');
            $dateStr = $date->toDateString();

            $attendanceTrendLabels[] = $formattedDate;
            $attendanceTrendPresent[] = AttendanceRecord::whereDate('date', $dateStr)
                ->whereIn('status', ['ON_TIME', 'PRESENT'])
                ->count();
            $attendanceTrendLate[] = AttendanceRecord::whereDate('date', $dateStr)
                ->where('status', 'LATE')
                ->count();
        }

        // 7. Chart 3: Task Status Distribution
        $taskChartData = [
            'TODO' => $tasksTodoCount,
            'IN_PROGRESS' => $tasksInProgressCount,
            'WAITING_VERIFICATION' => $tasksVerificationCount,
            'APPROVED' => $tasksCompletedCount,
        ];

        // 8. Recent Activities and Logs
        $recentAuditLogs = AuditLog::with('actor')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        $recentEmployees = Employee::with(['department', 'position'])
            ->latest('created_at')
            ->limit(5)
            ->get();

        // 9. Personal Context for Logged-In User / Employee
        $personalAttendanceToday = null;
        $personalPendingTasks = collect();
        $personalLeaveBalances = collect();

        if ($currentEmployee) {
            $personalAttendanceToday = AttendanceRecord::where('employee_id', $currentEmployee->id)
                ->whereDate('date', $today)
                ->first();

            $personalPendingTasks = Task::where('assigned_to', $currentEmployee->id)
                ->whereIn('status', ['TODO', 'IN_PROGRESS'])
                ->orderBy('due_date')
                ->limit(5)
                ->get();

            $personalLeaveBalances = $currentEmployee->leaveBalances()
                ->with('leaveType')
                ->get();
        }

        return view('dashboard.index', compact(
            'user',
            'currentEmployee',
            'primaryRole',
            'totalEmployees',
            'activeEmployees',
            'totalDepartments',
            'totalPositions',
            'totalCheckedIn',
            'presentTodayCount',
            'lateTodayCount',
            'onLeaveTodayCount',
            'attendanceRate',
            'tasksTodoCount',
            'tasksInProgressCount',
            'tasksVerificationCount',
            'tasksCompletedCount',
            'totalTasks',
            'draftPayrollRunsCount',
            'latestPayrollRun',
            'deptChartLabels',
            'deptChartData',
            'attendanceTrendLabels',
            'attendanceTrendPresent',
            'attendanceTrendLate',
            'taskChartData',
            'recentAuditLogs',
            'recentEmployees',
            'personalAttendanceToday',
            'personalPendingTasks',
            'personalLeaveBalances'
        ));
    }
}
