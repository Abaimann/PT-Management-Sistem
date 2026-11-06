<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Core\AuditLog;
use App\Models\Core\Department;
use App\Models\Core\Position;
use App\Models\HRIS\Employee;
use App\Models\Operations\Task;
use App\Models\Operations\TaskAttachment;
use App\Models\Operations\TaskReview;
use App\Models\Operations\WorkTarget;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TaskController extends Controller
{
    /**
     * Display a listing of tasks with filtering and KPI metrics.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $employee = $user->employee;

        $query = Task::with([
            'assignedBy.user',
            'assignedTo.user',
            'assignedTo.department',
            'assignedTo.position',
            'workTarget',
            'attachments',
            'reviews',
        ]);

        // Filter: Tab Scope ('all', 'assigned_to_me', 'assigned_by_me')
        $scope = $request->input('scope', 'all');
        if ($scope === 'assigned_to_me' && $employee) {
            $query->where('assigned_to', $employee->id);
        } elseif ($scope === 'assigned_by_me' && $employee) {
            $query->where('assigned_by', $employee->id);
        }

        // Filter: Status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filter: Priority
        if ($priority = $request->input('priority')) {
            $query->where('priority', $priority);
        }

        // Filter: Search Keyword (Title / Description / Assignee)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%")
                    ->orWhereHas('assignedTo', function ($eq) use ($search) {
                        $eq->where('full_name', 'ilike', "%{$search}%");
                    });
            });
        }

        // Filter: Due Date From & To
        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('due_date', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('due_date', '<=', $dateTo);
        }

        // Summary KPI Metrics
        $stats = [
            'total' => Task::count(),
            'todo' => Task::where('status', 'TODO')->count(),
            'in_progress' => Task::where('status', 'IN_PROGRESS')->count(),
            'waiting_verification' => Task::where('status', 'WAITING_VERIFICATION')->count(),
            'revision' => Task::where('status', 'REVISION')->count(),
            'approved' => Task::where('status', 'APPROVED')->count(),
        ];

        $tasks = $query->orderByRaw("
            CASE 
                WHEN status = 'WAITING_VERIFICATION' THEN 1
                WHEN status = 'REVISION' THEN 2
                WHEN status = 'IN_PROGRESS' THEN 3
                WHEN status = 'TODO' THEN 4
                ELSE 5
            END
        ")->orderBy('due_date', 'asc')->paginate(12)->withQueryString();

        $employees = Employee::where('is_active', true)->orderBy('full_name')->get();

        return view('operations.tasks.index', compact('tasks', 'stats', 'employees', 'scope'));
    }

    /**
     * Show the form for creating a new task.
     */
    public function create(): View
    {
        $employees = Employee::where('is_active', true)
            ->with(['department', 'position'])
            ->orderBy('full_name')
            ->get();

        $workTargets = WorkTarget::whereIn('status', ['DRAFT', 'ACTIVE'])
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();

        return view('operations.tasks.create', compact('employees', 'workTargets'));
    }

    /**
     * Store a newly created task.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'assigned_to' => ['required', 'exists:employees,id'],
            'work_target_id' => ['nullable', 'exists:work_targets,id'],
            'priority' => ['required', Rule::in(['LOW', 'MEDIUM', 'HIGH', 'URGENT'])],
            'due_date' => ['nullable', 'date'],
        ]);

        $assignorEmployee = $this->resolveCurrentEmployee();

        $task = DB::transaction(function () use ($validated, $assignorEmployee, $request) {
            $created = Task::create([
                'work_target_id' => $validated['work_target_id'] ?? null,
                'assigned_by' => $assignorEmployee->id,
                'assigned_to' => $validated['assigned_to'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'priority' => $validated['priority'],
                'status' => 'TODO',
                'due_date' => $validated['due_date'] ?? null,
            ]);

            AuditLog::create([
                'actor_id' => auth()->id(),
                'event' => 'create_task',
                'auditable_type' => Task::class,
                'auditable_id' => $created->id,
                'old_values' => null,
                'new_values' => $created->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);

            return $created;
        });

        return redirect()->route('tasks.show', $task)
            ->with('success', "Tugas '{$task->title}' berhasil ditugaskan.");
    }

    /**
     * Display the specified task dossier.
     */
    public function show(Task $task): View
    {
        $task->load([
            'assignedBy.user',
            'assignedTo.user',
            'assignedTo.department',
            'assignedTo.position',
            'workTarget.department',
            'attachments.uploadedBy',
            'reviews.reviewer.user',
        ]);

        $currentUserEmployee = auth()->user()->employee;
        $isAssignee = $currentUserEmployee && $task->assigned_to === $currentUserEmployee->id;
        $isAssignor = $currentUserEmployee && $task->assigned_by === $currentUserEmployee->id;
        $isSupervisor = auth()->user()->hasRole(['super_admin', 'supervisor', 'director']);

        return view('operations.tasks.show', compact('task', 'isAssignee', 'isAssignor', 'isSupervisor'));
    }

    /**
     * Update task progress or status.
     */
    public function updateStatus(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['TODO', 'IN_PROGRESS', 'WAITING_VERIFICATION'])],
        ]);

        $oldValues = $task->toArray();

        $task->update([
            'status' => $validated['status'],
        ]);

        AuditLog::create([
            'actor_id' => auth()->id(),
            'event' => 'update_task_status',
            'auditable_type' => Task::class,
            'auditable_id' => $task->id,
            'old_values' => $oldValues,
            'new_values' => $task->fresh()->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('tasks.show', $task)
            ->with('success', "Status tugas berhasil diubah menjadi '{$task->status}'.");
    }

    /**
     * Submit evidence / completion proof for the task.
     * Stored in private storage 'storage/app/private/tasks/evidences/'.
     */
    public function submitEvidence(Request $request, Task $task): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,zip,rar,jpg,jpeg,png'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $task) {
            $file = $request->file('file');
            $storedPath = $file->store('tasks/evidences', 'local');

            $attachment = TaskAttachment::create([
                'task_id' => $task->id,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $storedPath,
                'file_size' => $file->getSize(),
                'file_type' => $file->getClientMimeType(),
                'uploaded_by' => auth()->id(),
            ]);

            $oldValues = $task->toArray();

            // Automatically transition to WAITING_VERIFICATION
            $task->update([
                'status' => 'WAITING_VERIFICATION',
            ]);

            AuditLog::create([
                'actor_id' => auth()->id(),
                'event' => 'submit_task_evidence',
                'auditable_type' => Task::class,
                'auditable_id' => $task->id,
                'old_values' => $oldValues,
                'new_values' => array_merge($task->fresh()->toArray(), [
                    'attachment_id' => $attachment->id,
                    'file_name' => $attachment->file_name,
                    'notes' => $request->input('notes'),
                ]),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        });

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Bukti pengerjaan tugas berhasil diunggah. Tugas kini Menunggu Verifikasi supervisor.');
    }

    /**
     * Supervisor review: APPROVED, REVISION, or REJECTED.
     */
    public function review(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', Rule::in(['APPROVED', 'REVISION', 'REJECTED'])],
            'score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'feedback' => ['required', 'string'],
            'revision_notes' => ['required_if:decision,REVISION', 'nullable', 'string'],
        ]);

        $reviewerEmployee = $this->resolveCurrentEmployee();

        DB::transaction(function () use ($validated, $task, $reviewerEmployee, $request) {
            $decision = $validated['decision'];
            $oldValues = $task->toArray();

            if ($decision === 'APPROVED') {
                $task->status = 'APPROVED';
                $task->completed_at = now();
            } elseif ($decision === 'REVISION') {
                $task->status = 'REVISION';
            } else {
                $task->status = 'TODO';
            }
            $task->save();

            $review = TaskReview::create([
                'task_id' => $task->id,
                'reviewer_id' => $reviewerEmployee->id,
                'score' => $validated['score'] ?? null,
                'feedback' => $validated['feedback'],
                'revision_notes' => $validated['revision_notes'] ?? null,
                'decision' => $decision,
                'reviewed_at' => now(),
            ]);

            AuditLog::create([
                'actor_id' => auth()->id(),
                'event' => 'review_task',
                'auditable_type' => Task::class,
                'auditable_id' => $task->id,
                'old_values' => $oldValues,
                'new_values' => [
                    'task' => $task->fresh()->toArray(),
                    'review' => $review->toArray(),
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        });

        return redirect()->route('tasks.show', $task)
            ->with('success', "Evaluasi tugas berhasil disimpan dengan keputusan: {$validated['decision']}.");
    }

    /**
     * Download task attachment file safely from private storage.
     */
    public function downloadAttachment(Task $task, TaskAttachment $attachment): StreamedResponse
    {
        if ($attachment->task_id !== $task->id) {
            abort(404);
        }

        if (! Storage::disk('local')->exists($attachment->file_path)) {
            abort(404, 'File lampiran tidak ditemukan di storage server.');
        }

        return Storage::disk('local')->download($attachment->file_path, $attachment->file_name);
    }

    /**
     * Helper to resolve the authenticated user's employee record.
     */
    protected function resolveCurrentEmployee(): Employee
    {
        $employee = auth()->user()->employee;

        if (! $employee) {
            $dept = Department::firstOrCreate(['code' => 'OPS'], ['name' => 'Operasional']);
            $pos = Position::firstOrCreate(
                ['name' => 'Supervisor Operasional', 'department_id' => $dept->id],
                ['grade_level' => 'Senior', 'is_active' => true]
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
