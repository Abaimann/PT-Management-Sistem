<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Http\Requests\Core\StorePositionRequest;
use App\Http\Requests\Core\UpdatePositionRequest;
use App\Models\Core\AuditLog;
use App\Models\Core\Department;
use App\Models\Core\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PositionController extends Controller
{
    /**
     * Display a listing of positions.
     */
    public function index(Request $request): View
    {
        $query = Position::query()->with('department')->withCount('employees');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('grade_level', 'ilike', "%{$search}%");
            });
        }

        if ($departmentId = $request->input('department_id')) {
            $query->where('department_id', $departmentId);
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $positions = $query->orderBy('name')->paginate(10)->withQueryString();
        $departments = Department::orderBy('name')->get();

        $stats = [
            'total' => Position::count(),
            'active' => Position::where('is_active', true)->count(),
            'inactive' => Position::where('is_active', false)->count(),
            'departments_count' => Department::has('positions')->count(),
        ];

        return view('positions.index', compact('positions', 'departments', 'stats'));
    }

    /**
     * Show the form for creating a new position.
     */
    public function create(): View
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('positions.create', compact('departments'));
    }

    /**
     * Store a newly created position in storage.
     */
    public function store(StorePositionRequest $request): RedirectResponse
    {
        $position = DB::transaction(function () use ($request) {
            $created = Position::create([
                'department_id' => $request->validated('department_id'),
                'name' => $request->validated('name'),
                'grade_level' => $request->validated('grade_level'),
                'is_active' => $request->validated('is_active', true),
            ]);

            AuditLog::create([
                'actor_id' => auth()->id(),
                'event' => 'create_position',
                'auditable_type' => Position::class,
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
            ->route('positions.index')
            ->with('success', "Jabatan '{$position->name}' berhasil ditambahkan.");
    }

    /**
     * Show the form for editing the specified position.
     */
    public function edit(Position $position): View
    {
        $departments = Department::orderBy('name')->get();

        return view('positions.edit', compact('position', 'departments'));
    }

    /**
     * Update the specified position in storage.
     */
    public function update(UpdatePositionRequest $request, Position $position): RedirectResponse
    {
        DB::transaction(function () use ($request, $position) {
            $oldValues = $position->toArray();

            $position->update([
                'department_id' => $request->validated('department_id'),
                'name' => $request->validated('name'),
                'grade_level' => $request->validated('grade_level'),
                'is_active' => $request->validated('is_active', true),
            ]);

            AuditLog::create([
                'actor_id' => auth()->id(),
                'event' => 'update_position',
                'auditable_type' => Position::class,
                'auditable_id' => $position->id,
                'old_values' => $oldValues,
                'new_values' => $position->fresh()->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        });

        return redirect()
            ->route('positions.index')
            ->with('success', "Jabatan '{$position->name}' berhasil diperbarui.");
    }

    /**
     * Remove the specified position from storage.
     */
    public function destroy(Position $position, Request $request): RedirectResponse
    {
        if ($position->employees()->exists()) {
            return redirect()
                ->route('positions.index')
                ->with('error', "Jabatan '{$position->name}' tidak dapat dihapus karena masih digunakan oleh karyawan. Nonaktifkan status jabatan jika sudah tidak dipakai.");
        }

        DB::transaction(function () use ($position, $request) {
            $oldValues = $position->toArray();
            $positionId = $position->id;

            $position->delete();

            AuditLog::create([
                'actor_id' => auth()->id(),
                'event' => 'delete_position',
                'auditable_type' => Position::class,
                'auditable_id' => $positionId,
                'old_values' => $oldValues,
                'new_values' => null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        });

        return redirect()
            ->route('positions.index')
            ->with('success', "Jabatan '{$position->name}' berhasil dihapus.");
    }
}
