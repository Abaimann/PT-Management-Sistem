<?php

namespace App\Http\Controllers\GeneralAffairs;

use App\Http\Controllers\Controller;
use App\Models\Core\Department;
use App\Models\Core\Position;
use App\Models\HRIS\Employee;
use App\Models\Inventory\FacilityRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GeneralAffairsController extends Controller
{
    /**
     * Pengajuan kebutuhan ATK / perbaikan fasilitas kerja kantor (/ga/requests).
     */
    public function requests(Request $request): View
    {
        $user = auth()->user();
        $isPrivileged = $user->hasRole(['super_admin', 'inventory_ga', 'director']);

        $query = FacilityRequest::with(['employee.user', 'department']);

        // Normal employee only views their own requests, unless privileged
        if (! $isPrivileged && $user->employee) {
            $query->where('employee_id', $user->employee->id);
        }

        if ($type = $request->input('request_type')) {
            $query->where('request_type', $type);
        }

        if ($priority = $request->input('priority')) {
            $query->where('priority', $priority);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        $facilityRequests = $query->latest('created_at')->paginate(15)->withQueryString();
        $departments = Department::orderBy('name')->get();

        return view('ga.requests.index', compact('facilityRequests', 'departments', 'isPrivileged'));
    }

    /**
     * Simpan permohonan fasilitas / ATK / perbaikan sarana.
     */
    public function storeRequest(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'request_type' => ['required', 'in:STATIONERY,EQUIPMENT,FACILITY_REPAIR,VEHICLE'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'quantity' => ['required', 'integer', 'min:1'],
            'priority' => ['required', 'in:LOW,MEDIUM,HIGH,URGENT'],
            'department_id' => ['nullable', 'exists:departments,id'],
        ]);

        $employee = $this->resolveCurrentEmployee();
        $departmentId = $validated['department_id'] ?? $employee->department_id;

        FacilityRequest::create([
            'employee_id' => $employee->id,
            'department_id' => $departmentId,
            'request_type' => $validated['request_type'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'quantity' => $validated['quantity'],
            'priority' => $validated['priority'],
            'status' => 'PENDING',
        ]);

        return redirect()->route('ga.requests.index')
            ->with('success', 'Permohonan fasilitas berhasil diajukan ke tim General Affairs.');
    }

    /**
     * Otorisasi dan update penyelesaian oleh GA.
     */
    public function updateStatus(Request $request, FacilityRequest $facilityRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:PENDING,APPROVED,IN_PROGRESS,RESOLVED,REJECTED'],
            'resolution_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $updateData = ['status' => $validated['status']];

        if ($validated['status'] === 'RESOLVED') {
            $updateData['resolved_at'] = now();
        }

        if (! empty($validated['resolution_notes'])) {
            $updateData['description'] = $facilityRequest->description
                . "\n\n[Catatan Tindak Lanjut GA]: " . $validated['resolution_notes'];
        }

        $facilityRequest->update($updateData);

        return redirect()->route('ga.requests.index')
            ->with('success', "Status permohonan '{$facilityRequest->title}' berhasil diperbarui menjadi {$validated['status']}.");
    }

    /**
     * Helper to resolve the authenticated user's employee record.
     */
    protected function resolveCurrentEmployee(): Employee
    {
        $employee = auth()->user()->employee;

        if (! $employee) {
            $dept = Department::firstOrCreate(['code' => 'GA'], ['name' => 'General Affairs & Umum']);
            $pos = Position::firstOrCreate(
                ['name' => 'General Staff', 'department_id' => $dept->id],
                ['grade_level' => 'Staff', 'is_active' => true]
            );

            $employee = Employee::firstOrCreate(
                ['user_id' => auth()->id()],
                [
                    'nik' => 'EMP-'.str_pad(auth()->id(), 5, '0', STR_PAD_LEFT),
                    'full_name' => auth()->user()->name,
                    'gender' => 'MALE',
                    'birth_date' => '1990-01-01',
                    'department_id' => $dept->id,
                    'position_id' => $pos->id,
                    'employment_status' => 'PERMANENT',
                    'join_date' => now(),
                    'is_active' => true,
                ]
            );
        }

        return $employee;
    }
}
