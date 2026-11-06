<?php

namespace App\Http\Controllers\HRIS;

use App\Http\Controllers\Controller;
use App\Http\Requests\HRIS\StoreEmployeeRequest;
use App\Http\Requests\HRIS\UpdateEmployeeRequest;
use App\Models\Core\AuditLog;
use App\Models\Core\Department;
use App\Models\Core\Position;
use App\Models\Core\Role;
use App\Models\Core\User;
use App\Models\HRIS\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees.
     */
    public function index(Request $request): View
    {
        $query = Employee::query()->with(['department', 'position', 'supervisor', 'user.roles']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'ilike', "%{$search}%")
                    ->orWhere('nik', 'ilike', "%{$search}%")
                    ->orWhere('phone', 'ilike', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('email', 'ilike', "%{$search}%");
                    });
            });
        }

        if ($departmentId = $request->input('department_id')) {
            $query->where('department_id', $departmentId);
        }

        if ($positionId = $request->input('position_id')) {
            $query->where('position_id', $positionId);
        }

        if ($employmentStatus = $request->input('employment_status')) {
            $query->where('employment_status', $employmentStatus);
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $employees = $query->orderBy('full_name')->paginate(12)->withQueryString();

        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $positions = Position::where('is_active', true)->orderBy('name')->get();

        $stats = [
            'total' => Employee::count(),
            'active' => Employee::where('is_active', true)->count(),
            'permanent' => Employee::where('employment_status', 'PERMANENT')->count(),
            'contract' => Employee::where('employment_status', 'CONTRACT')->count(),
            'intern' => Employee::where('employment_status', 'INTERN')->count(),
        ];

        return view('employees.index', compact('employees', 'departments', 'positions', 'stats'));
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create(): View
    {
        $departments = Department::where('is_active', true)->with('positions')->orderBy('name')->get();
        $positions = Position::where('is_active', true)->orderBy('name')->get();
        $supervisors = Employee::where('is_active', true)->orderBy('full_name')->get();
        $roles = Role::orderBy('name')->get();

        return view('employees.create', compact('departments', 'positions', 'supervisors', 'roles'));
    }

    /**
     * Store a newly created employee in storage.
     */
    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $employee = DB::transaction(function () use ($request) {
            $userId = null;

            if ($request->boolean('create_user_account') && $request->filled('email')) {
                $user = User::create([
                    'name' => $request->validated('full_name'),
                    'email' => $request->validated('email'),
                    'password' => Hash::make($request->validated('password', 'Password123!')),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);

                if ($roleId = $request->validated('role_id')) {
                    $user->roles()->sync([$roleId]);
                } else {
                    $employeeRole = Role::where('code', 'employee')->first();
                    if ($employeeRole) {
                        $user->roles()->sync([$employeeRole->id]);
                    }
                }

                $userId = $user->id;
            }

            $createdEmployee = Employee::create([
                'user_id' => $userId,
                'nik' => $request->validated('nik'),
                'full_name' => $request->validated('full_name'),
                'gender' => $request->validated('gender'),
                'birth_place' => $request->validated('birth_place'),
                'birth_date' => $request->validated('birth_date'),
                'phone' => $request->validated('phone'),
                'address' => $request->validated('address'),
                'department_id' => $request->validated('department_id'),
                'position_id' => $request->validated('position_id'),
                'supervisor_id' => $request->validated('supervisor_id'),
                'employment_status' => $request->validated('employment_status'),
                'join_date' => $request->validated('join_date'),
                'resign_date' => $request->validated('resign_date'),
                'is_active' => $request->validated('is_active', true),
            ]);

            AuditLog::create([
                'actor_id' => auth()->id(),
                'event' => 'create_employee',
                'auditable_type' => Employee::class,
                'auditable_id' => $createdEmployee->id,
                'old_values' => null,
                'new_values' => $createdEmployee->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);

            return $createdEmployee;
        });

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', "Karyawan '{$employee->full_name}' ({$employee->nik}) berhasil didaftarkan ke sistem.");
    }

    /**
     * Display the specified employee profile dossier.
     */
    public function show(Employee $employee): View
    {
        $employee->load([
            'department',
            'position',
            'supervisor',
            'subordinates',
            'user.roles',
            'contracts' => fn ($q) => $q->orderBy('start_date', 'desc'),
            'attendanceRecords' => fn ($q) => $q->orderBy('date', 'desc')->limit(10),
            'assignedTasks' => fn ($q) => $q->orderBy('created_at', 'desc')->limit(5),
            'leaveBalances.leaveType',
        ]);

        return view('employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit(Employee $employee): View
    {
        $departments = Department::where('is_active', true)->with('positions')->orderBy('name')->get();
        $positions = Position::where('is_active', true)->orderBy('name')->get();
        $supervisors = Employee::where('id', '!=', $employee->id)->where('is_active', true)->orderBy('full_name')->get();
        $roles = Role::orderBy('name')->get();

        $employee->load('user.roles');

        return view('employees.edit', compact('employee', 'departments', 'positions', 'supervisors', 'roles'));
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        DB::transaction(function () use ($request, $employee) {
            $oldEmployeeValues = $employee->toArray();

            // Handle User Account synchronization
            if ($request->filled('email')) {
                if ($employee->user) {
                    $userUpdates = [
                        'name' => $request->validated('full_name'),
                        'email' => $request->validated('email'),
                    ];

                    if ($request->filled('password')) {
                        $userUpdates['password'] = Hash::make($request->validated('password'));
                    }

                    $employee->user->update($userUpdates);

                    if ($roleId = $request->validated('role_id')) {
                        $employee->user->roles()->sync([$roleId]);
                    }
                } else {
                    $newUser = User::create([
                        'name' => $request->validated('full_name'),
                        'email' => $request->validated('email'),
                        'password' => Hash::make($request->validated('password', 'Password123!')),
                        'is_active' => true,
                        'email_verified_at' => now(),
                    ]);

                    if ($roleId = $request->validated('role_id')) {
                        $newUser->roles()->sync([$roleId]);
                    }

                    $employee->user_id = $newUser->id;
                }
            }

            $employee->update([
                'user_id' => $employee->user_id,
                'nik' => $request->validated('nik'),
                'full_name' => $request->validated('full_name'),
                'gender' => $request->validated('gender'),
                'birth_place' => $request->validated('birth_place'),
                'birth_date' => $request->validated('birth_date'),
                'phone' => $request->validated('phone'),
                'address' => $request->validated('address'),
                'department_id' => $request->validated('department_id'),
                'position_id' => $request->validated('position_id'),
                'supervisor_id' => $request->validated('supervisor_id'),
                'employment_status' => $request->validated('employment_status'),
                'join_date' => $request->validated('join_date'),
                'resign_date' => $request->validated('resign_date'),
                'is_active' => $request->validated('is_active', true),
            ]);

            AuditLog::create([
                'actor_id' => auth()->id(),
                'event' => 'update_employee',
                'auditable_type' => Employee::class,
                'auditable_id' => $employee->id,
                'old_values' => $oldEmployeeValues,
                'new_values' => $employee->fresh()->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        });

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', "Data karyawan '{$employee->full_name}' berhasil diperbarui.");
    }

    /**
     * Remove or deactivate the specified employee.
     */
    public function destroy(Employee $employee, Request $request): RedirectResponse
    {
        $hasDependencies = $employee->attendanceRecords()->exists()
            || $employee->contracts()->exists()
            || $employee->assignedTasks()->exists()
            || $employee->subordinates()->exists();

        DB::transaction(function () use ($employee, $hasDependencies, $request) {
            $oldValues = $employee->toArray();

            if ($hasDependencies) {
                // Soft deactivate to maintain relational history
                $employee->update(['is_active' => false]);
                if ($employee->user) {
                    $employee->user->update(['is_active' => false]);
                }

                $event = 'deactivate_employee';
            } else {
                $employee->delete();
                $event = 'delete_employee';
            }

            AuditLog::create([
                'actor_id' => auth()->id(),
                'event' => $event,
                'auditable_type' => Employee::class,
                'auditable_id' => $employee->id,
                'old_values' => $oldValues,
                'new_values' => $hasDependencies ? $employee->fresh()->toArray() : null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        });

        $message = $hasDependencies
            ? "Karyawan '{$employee->full_name}' berhasil dinonaktifkan karena memiliki rekam jejak historis pada sistem."
            : "Data karyawan '{$employee->full_name}' berhasil dihapus.";

        return redirect()
            ->route('employees.index')
            ->with('success', $message);
    }
}
