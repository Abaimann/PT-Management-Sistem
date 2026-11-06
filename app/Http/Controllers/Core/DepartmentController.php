<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Http\Requests\Core\StoreDepartmentRequest;
use App\Http\Requests\Core\UpdateDepartmentRequest;
use App\Models\Core\AuditLog;
use App\Models\Core\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    /**
     * Display a listing of departments.
     */
    public function index(Request $request): View
    {
        $query = Department::query()->withCount(['positions', 'employees']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'ilike', "%{$search}%")
                    ->orWhere('name', 'ilike', "%{$search}%")
                    ->orWhere('cost_center', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $departments = $query->orderBy('name')->paginate(10)->withQueryString();

        $stats = [
            'total' => Department::count(),
            'active' => Department::where('is_active', true)->count(),
            'inactive' => Department::where('is_active', false)->count(),
        ];

        return view('departments.index', compact('departments', 'stats'));
    }

    /**
     * Show the form for creating a new department.
     */
    public function create(): View
    {
        return view('departments.create');
    }

    /**
     * Store a newly created department in storage.
     */
    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        $department = DB::transaction(function () use ($request) {
            $created = Department::create([
                'code' => $request->validated('code'),
                'name' => $request->validated('name'),
                'cost_center' => $request->validated('cost_center'),
                'is_active' => $request->validated('is_active', true),
            ]);

            AuditLog::create([
                'actor_id' => auth()->id(),
                'event' => 'create_department',
                'auditable_type' => Department::class,
                'auditable_id' => $created->id,
                'old_values' => null,
                'new_values' => $created->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);

            return $created;
        });

        return redirect()
            ->route('departments.index')
            ->with('success', "Departemen '{$department->name}' ({$department->code}) berhasil ditambahkan.");
    }

    /**
     * Show the form for editing the specified department.
     */
    public function edit(Department $department): View
    {
        return view('departments.edit', compact('department'));
    }

    /**
     * Update the specified department in storage.
     */
    public function update(UpdateDepartmentRequest $request, Department $department): RedirectResponse
    {
        DB::transaction(function () use ($request, $department) {
            $oldValues = $department->toArray();

            $department->update([
                'code' => $request->validated('code'),
                'name' => $request->validated('name'),
                'cost_center' => $request->validated('cost_center'),
                'is_active' => $request->validated('is_active', true),
            ]);

            AuditLog::create([
                'actor_id' => auth()->id(),
                'event' => 'update_department',
                'auditable_type' => Department::class,
                'auditable_id' => $department->id,
                'old_values' => $oldValues,
                'new_values' => $department->fresh()->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        });

        return redirect()
            ->route('departments.index')
            ->with('success', "Departemen '{$department->name}' berhasil diperbarui.");
    }

    /**
     * Remove the specified department from storage.
     */
    public function destroy(Department $department, Request $request): RedirectResponse
    {
        if ($department->positions()->exists() || $department->employees()->exists()) {
            return redirect()
                ->route('departments.index')
                ->with('error', "Departemen '{$department->name}' tidak dapat dihapus karena masih memiliki data jabatan atau karyawan terkait. Nonaktifkan status jika tidak lagi digunakan.");
        }

        DB::transaction(function () use ($department, $request) {
            $oldValues = $department->toArray();
            $departmentId = $department->id;

            $department->delete();

            AuditLog::create([
                'actor_id' => auth()->id(),
                'event' => 'delete_department',
                'auditable_type' => Department::class,
                'auditable_id' => $departmentId,
                'old_values' => $oldValues,
                'new_values' => null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        });

        return redirect()
            ->route('departments.index')
            ->with('success', "Departemen '{$department->name}' berhasil dihapus.");
    }
}
