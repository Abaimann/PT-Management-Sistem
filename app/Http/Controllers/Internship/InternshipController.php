<?php

namespace App\Http\Controllers\Internship;

use App\Http\Controllers\Controller;
use App\Models\Core\AuditLog;
use App\Models\Core\Department;
use App\Models\Core\Position;
use App\Models\Core\Role;
use App\Models\Core\User;
use App\Models\HRIS\Employee;
use App\Models\Internship\Intern;
use App\Models\Internship\InternshipEvaluation;
use App\Models\Internship\InternshipLogbook;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InternshipController extends Controller
{
    /**
     * Display a listing of interns.
     */
    public function index(Request $request): View
    {
        $query = Intern::with([
            'user',
            'mentor.department',
            'department',
            'evaluations',
            'logbooks',
        ]);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'ilike', "%{$search}%")
                    ->orWhere('intern_code', 'ilike', "%{$search}%")
                    ->orWhere('institution', 'ilike', "%{$search}%")
                    ->orWhere('major', 'ilike', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($deptId = $request->input('department_id')) {
            $query->where('department_id', $deptId);
        }

        if ($mentorId = $request->input('mentor_id')) {
            $query->where('mentor_id', $mentorId);
        }

        $stats = [
            'total' => Intern::count(),
            'active' => Intern::where('status', 'ACTIVE')->count(),
            'completed' => Intern::where('status', 'COMPLETED')->count(),
            'total_logbooks_verified' => InternshipLogbook::where('status', 'VERIFIED')->count(),
        ];

        $interns = $query->orderByRaw("
            CASE 
                WHEN status = 'ACTIVE' THEN 1 
                WHEN status = 'COMPLETED' THEN 2 
                ELSE 3 
            END
        ")->orderBy('full_name')->paginate(12)->withQueryString();

        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $mentors = Employee::where('is_active', true)->orderBy('full_name')->get();

        return view('internship.index', compact('interns', 'stats', 'departments', 'mentors'));
    }

    /**
     * Show the form for registering a new intern.
     */
    public function create(): View
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $mentors = Employee::where('is_active', true)
            ->with(['department', 'position'])
            ->orderBy('full_name')
            ->get();

        return view('internship.create', compact('departments', 'mentors'));
    }

    /**
     * Store a newly created intern.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'institution' => ['required', 'string', 'max:255'],
            'major' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'department_id' => ['required', 'exists:departments,id'],
            'mentor_id' => ['required', 'exists:employees,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'create_user_account' => ['nullable', 'boolean'],
            'email' => ['required_if:create_user_account,1', 'nullable', 'email', 'unique:users,email'],
            'password' => ['required_if:create_user_account,1', 'nullable', 'string', 'min:8'],
        ]);

        $intern = DB::transaction(function () use ($validated, $request) {
            $userId = null;

            if ($request->boolean('create_user_account')) {
                $user = User::create([
                    'name' => $validated['full_name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);

                $internRole = Role::firstOrCreate(['code' => 'intern'], [
                    'name' => 'Anak Magang',
                    'description' => 'Akses terbatas untuk absensi & logbook magang.',
                ]);
                $user->roles()->syncWithoutDetaching([$internRole->id]);

                $userId = $user->id;
            }

            // Generate clean code INT-YYYYMM-0001
            $prefix = 'INT-'.date('Ym').'-';
            $latest = Intern::where('intern_code', 'like', "{$prefix}%")
                ->latest('id')
                ->first();
            $seq = $latest ? ((int) substr($latest->intern_code, -4)) + 1 : 1;
            $internCode = $prefix.str_pad($seq, 4, '0', STR_PAD_LEFT);

            $created = Intern::create([
                'user_id' => $userId,
                'mentor_id' => $validated['mentor_id'],
                'department_id' => $validated['department_id'],
                'intern_code' => $internCode,
                'full_name' => $validated['full_name'],
                'institution' => $validated['institution'],
                'major' => $validated['major'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'status' => 'ACTIVE',
            ]);

            AuditLog::create([
                'actor_id' => auth()->id(),
                'event' => 'create_intern',
                'auditable_type' => Intern::class,
                'auditable_id' => $created->id,
                'old_values' => null,
                'new_values' => $created->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);

            return $created;
        });

        return redirect()->route('internship.show', $intern)
            ->with('success', "Peserta magang '{$intern->full_name}' ({$intern->intern_code}) berhasil didaftarkan.");
    }

    /**
     * Display the specified intern dossier.
     */
    public function show(Intern $intern): View
    {
        $intern->load([
            'user',
            'mentor.department',
            'mentor.position',
            'department',
            'logbooks.verifiedBy',
            'evaluations.evaluator',
        ]);

        $logbooksCount = [
            'total' => $intern->logbooks->count(),
            'verified' => $intern->logbooks->where('status', 'VERIFIED')->count(),
            'rejected' => $intern->logbooks->where('status', 'REJECTED')->count(),
            'submitted' => $intern->logbooks->where('status', 'SUBMITTED')->count(),
        ];

        $latestEvaluation = $intern->evaluations->sortByDesc('evaluated_at')->first();

        $currentUser = auth()->user();
        $isMentor = $currentUser->employee && $intern->mentor_id === $currentUser->employee->id;
        $isHRorAdmin = $currentUser->hasRole(['super_admin', 'hr_admin', 'director']);

        return view('internship.show', compact('intern', 'logbooksCount', 'latestEvaluation', 'isMentor', 'isHRorAdmin'));
    }

    /**
     * Display daily logbook submissions and verifications.
     */
    public function logbooks(Request $request): View
    {
        $currentUser = auth()->user();
        $currentIntern = $currentUser->intern;
        $currentEmployee = $currentUser->employee;
        $isMentorOrAdmin = $currentUser->hasRole(['super_admin', 'hr_admin', 'supervisor', 'director']);

        $query = InternshipLogbook::with(['intern.mentor', 'intern.department', 'verifiedBy']);

        // If logged-in user is an intern, lock view to their own logbooks
        if ($currentIntern && ! $isMentorOrAdmin) {
            $query->where('intern_id', $currentIntern->id);
        } else {
            // Filter by intern
            if ($internId = $request->input('intern_id')) {
                $query->where('intern_id', $internId);
            }
            // Filter by status
            if ($status = $request->input('status')) {
                $query->where('status', $status);
            }
        }

        // Filter by date
        if ($date = $request->input('date')) {
            $query->whereDate('date', $date);
        }

        $logbooks = $query->orderByDesc('date')->paginate(15)->withQueryString();

        // Pending verifications for mentor / admin
        $pendingQuery = InternshipLogbook::with(['intern.mentor', 'intern.department'])
            ->where('status', 'SUBMITTED');

        if ($currentEmployee && ! $currentUser->hasRole(['super_admin', 'hr_admin'])) {
            $pendingQuery->whereHas('intern', function ($q) use ($currentEmployee) {
                $q->where('mentor_id', $currentEmployee->id);
            });
        }

        $pendingVerifications = $pendingQuery->orderBy('date')->limit(10)->get();
        $activeInterns = Intern::where('status', 'ACTIVE')->orderBy('full_name')->get();

        return view('internship.logbooks', compact(
            'logbooks',
            'currentIntern',
            'pendingVerifications',
            'activeInterns',
            'isMentorOrAdmin'
        ));
    }

    /**
     * Store daily logbook record by intern.
     */
    public function storeLogbook(Request $request): RedirectResponse
    {
        $currentUser = auth()->user();
        $internId = $currentUser->intern?->id;

        // If not an intern but admin/mentor submitting on behalf
        if (! $internId) {
            $request->validate(['intern_id' => ['required', 'exists:interns,id']]);
            $internId = $request->input('intern_id');
        }

        $validated = $request->validate([
            'date' => [
                'required',
                'date',
                Rule::unique('internship_logbooks')->where('intern_id', $internId),
            ],
            'activity_description' => ['required', 'string', 'min:10'],
            'output_url' => ['nullable', 'url', 'max:255'],
        ], [
            'date.unique' => 'Anda sudah mengisi logbook harian untuk tanggal ini.',
        ]);

        $logbook = InternshipLogbook::create([
            'intern_id' => $internId,
            'date' => $validated['date'],
            'activity_description' => $validated['activity_description'],
            'output_url' => $validated['output_url'] ?? null,
            'status' => 'SUBMITTED',
        ]);

        AuditLog::create([
            'actor_id' => auth()->id(),
            'event' => 'submit_internship_logbook',
            'auditable_type' => InternshipLogbook::class,
            'auditable_id' => $logbook->id,
            'old_values' => null,
            'new_values' => $logbook->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('internship.logbooks')
            ->with('success', 'Catatan logbook harian Anda berhasil dikirim dan menunggu verifikasi pembimbing.');
    }

    /**
     * Mentor verification of daily logbook.
     */
    public function verifyLogbook(Request $request, InternshipLogbook $logbook): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['VERIFIED', 'REJECTED'])],
            'verification_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $verifierEmployee = $this->resolveCurrentEmployee();
        $oldValues = $logbook->toArray();

        $logbook->update([
            'status' => $validated['status'],
            'verified_by' => $verifierEmployee->id,
            'verification_notes' => $validated['verification_notes'] ?? null,
            'verified_at' => now(),
        ]);

        AuditLog::create([
            'actor_id' => auth()->id(),
            'event' => 'verify_internship_logbook',
            'auditable_type' => InternshipLogbook::class,
            'auditable_id' => $logbook->id,
            'old_values' => $oldValues,
            'new_values' => $logbook->fresh()->toArray(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', "Logbook tanggal {$logbook->date->format('d M Y')} berhasil diverifikasi ({$validated['status']}).");
    }

    /**
     * Evaluate final internship performance and score.
     */
    public function evaluate(Request $request, Intern $intern): RedirectResponse
    {
        $validated = $request->validate([
            'discipline_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'technical_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'teamwork_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'communication_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'recommendation' => ['nullable', 'string'],
            'certificate_number' => ['nullable', 'string', 'max:50'],
            'evaluated_at' => ['required', 'date'],
            'mark_completed' => ['nullable', 'boolean'],
        ]);

        $finalScore = round(
            ($validated['discipline_score'] +
             $validated['technical_score'] +
             $validated['teamwork_score'] +
             $validated['communication_score']) / 4,
            2
        );

        $evaluatorEmployee = $this->resolveCurrentEmployee();

        DB::transaction(function () use ($validated, $intern, $evaluatorEmployee, $finalScore, $request) {
            $evaluation = InternshipEvaluation::updateOrCreate(
                ['intern_id' => $intern->id],
                [
                    'evaluator_id' => $evaluatorEmployee->id,
                    'discipline_score' => $validated['discipline_score'],
                    'technical_score' => $validated['technical_score'],
                    'teamwork_score' => $validated['teamwork_score'],
                    'communication_score' => $validated['communication_score'],
                    'final_score' => $finalScore,
                    'recommendation' => $validated['recommendation'] ?? null,
                    'certificate_number' => $validated['certificate_number'] ?? ('CERT-INT-'.$intern->id.'-'.date('Y')),
                    'evaluated_at' => $validated['evaluated_at'],
                ]
            );

            if ($request->boolean('mark_completed', true)) {
                $intern->update(['status' => 'COMPLETED']);
            }

            AuditLog::create([
                'actor_id' => auth()->id(),
                'event' => 'evaluate_intern',
                'auditable_type' => InternshipEvaluation::class,
                'auditable_id' => $evaluation->id,
                'old_values' => null,
                'new_values' => $evaluation->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        });

        return redirect()->route('internship.show', $intern)
            ->with('success', "Penilaian akhir peserta magang '{$intern->full_name}' berhasil disimpan dengan skor akhir: {$finalScore}.");
    }

    /**
     * Helper to resolve the authenticated user's employee record.
     */
    protected function resolveCurrentEmployee(): Employee
    {
        $employee = auth()->user()->employee;

        if (! $employee) {
            $dept = Department::firstOrCreate(['code' => 'HRD'], ['name' => 'Human Resources']);
            $pos = Position::firstOrCreate(
                ['name' => 'HR Supervisor', 'department_id' => $dept->id],
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
