<?php

namespace Tests\Feature;

use App\Models\Core\Department;
use App\Models\Core\Position;
use App\Models\Core\Role;
use App\Models\Core\User;
use App\Models\HRIS\Employee;
use App\Models\Internship\Intern;
use App\Models\Internship\InternshipLogbook;
use App\Models\Operations\Task;
use App\Models\Operations\TaskAttachment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OperationsAndInternshipTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;

    protected Employee $supervisorEmployee;

    protected Employee $staffEmployee;

    protected Department $department;

    protected Position $position;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        // Roles
        $superAdminRole = Role::firstOrCreate(['code' => 'super_admin'], ['name' => 'Super Admin']);
        Role::firstOrCreate(['code' => 'supervisor'], ['name' => 'Supervisor']);
        Role::firstOrCreate(['code' => 'employee'], ['name' => 'Karyawan']);
        Role::firstOrCreate(['code' => 'intern'], ['name' => 'Anak Magang']);

        // Admin User
        $this->adminUser = User::create([
            'name' => 'Admin Ops '.uniqid(),
            'email' => 'admin_ops_'.uniqid().'@example.test',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $this->adminUser->roles()->sync([$superAdminRole->id]);

        // Department & Position
        $this->department = Department::firstOrCreate(
            ['code' => 'OPS-TEST'],
            ['name' => 'Operasional Test', 'cost_center' => 'CC-OPS-TEST', 'is_active' => true]
        );

        $this->position = Position::firstOrCreate(
            ['name' => 'Staff Operasional Test', 'department_id' => $this->department->id],
            ['grade_level' => 'Staff', 'is_active' => true]
        );

        // Supervisor Employee
        $supUser = User::create([
            'name' => 'Supervisor User '.uniqid(),
            'email' => 'sup_'.uniqid().'@example.test',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $this->supervisorEmployee = Employee::create([
            'user_id' => $supUser->id,
            'nik' => 'SUP-'.rand(10000, 99999),
            'full_name' => 'Supervisor Budi',
            'gender' => 'MALE',
            'birth_date' => '1990-01-01',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_status' => 'PERMANENT',
            'join_date' => '2024-01-01',
            'is_active' => true,
        ]);

        // Staff Employee
        $staffUser = User::create([
            'name' => 'Staff User '.uniqid(),
            'email' => 'staff_'.uniqid().'@example.test',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $this->staffEmployee = Employee::create([
            'user_id' => $staffUser->id,
            'nik' => 'STF-'.rand(10000, 99999),
            'full_name' => 'Staff Joko',
            'gender' => 'MALE',
            'birth_date' => '1992-05-10',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_status' => 'PERMANENT',
            'join_date' => '2024-02-01',
            'is_active' => true,
        ]);
    }

    public function test_can_list_tasks_and_render_index(): void
    {
        Task::create([
            'assigned_by' => $this->supervisorEmployee->id,
            'assigned_to' => $this->staffEmployee->id,
            'title' => 'Tugas Uji Coba Index '.uniqid(),
            'priority' => 'HIGH',
            'status' => 'TODO',
            'due_date' => now()->addDays(3),
        ]);

        $response = $this->actingAs($this->adminUser)->get('/tasks');
        $response->assertStatus(200);
        $response->assertSee('Tugas Operasional');
        $response->assertSee('Total Tugas');
    }

    public function test_can_create_new_task(): void
    {
        $this->actingAs($this->adminUser)->get('/tasks/create')->assertStatus(200);

        $taskTitle = 'Implementasi Modul Q2 '.uniqid();

        $response = $this->actingAs($this->adminUser)->post('/tasks', [
            'title' => $taskTitle,
            'description' => 'Rincian tugas penting untuk tim operasional.',
            'assigned_to' => $this->staffEmployee->id,
            'priority' => 'URGENT',
            'due_date' => now()->addDays(5)->format('Y-m-d H:i'),
        ]);

        $this->assertDatabaseHas('tasks', [
            'title' => $taskTitle,
            'assigned_to' => $this->staffEmployee->id,
            'priority' => 'URGENT',
            'status' => 'TODO',
        ]);

        $task = Task::where('title', $taskTitle)->first();
        $response->assertRedirect("/tasks/{$task->id}");
    }

    public function test_assignee_can_view_task_and_update_status(): void
    {
        $task = Task::create([
            'assigned_by' => $this->supervisorEmployee->id,
            'assigned_to' => $this->staffEmployee->id,
            'title' => 'Tugas Pengerjaan Mandiri '.uniqid(),
            'priority' => 'MEDIUM',
            'status' => 'TODO',
            'due_date' => now()->addDays(2),
        ]);

        $showResponse = $this->actingAs($this->staffEmployee->user)->get("/tasks/{$task->id}");
        $showResponse->assertStatus(200);
        $showResponse->assertSee($task->title);

        $statusResponse = $this->actingAs($this->staffEmployee->user)->patch("/tasks/{$task->id}/status", [
            'status' => 'IN_PROGRESS',
        ]);

        $statusResponse->assertRedirect("/tasks/{$task->id}");
        $this->assertEquals('IN_PROGRESS', $task->fresh()->status);
    }

    public function test_assignee_can_submit_evidence_and_status_changes_to_waiting_verification(): void
    {
        $task = Task::create([
            'assigned_by' => $this->supervisorEmployee->id,
            'assigned_to' => $this->staffEmployee->id,
            'title' => 'Tugas Pengunggahan Bukti '.uniqid(),
            'priority' => 'HIGH',
            'status' => 'IN_PROGRESS',
            'due_date' => now()->addDays(1),
        ]);

        $fakeFile = UploadedFile::fake()->create('laporan_pengerjaan.pdf', 300, 'application/pdf');

        $response = $this->actingAs($this->staffEmployee->user)->post("/tasks/{$task->id}/evidence", [
            'file' => $fakeFile,
            'notes' => 'Berikut adalah file laporan pengerjaan final.',
        ]);

        $response->assertRedirect("/tasks/{$task->id}");

        $this->assertEquals('WAITING_VERIFICATION', $task->fresh()->status);
        $this->assertDatabaseHas('task_attachments', [
            'task_id' => $task->id,
            'file_name' => 'laporan_pengerjaan.pdf',
        ]);
    }

    public function test_supervisor_can_review_task_with_revision_and_approval(): void
    {
        $task = Task::create([
            'assigned_by' => $this->supervisorEmployee->id,
            'assigned_to' => $this->staffEmployee->id,
            'title' => 'Tugas Review Supervisor '.uniqid(),
            'priority' => 'HIGH',
            'status' => 'WAITING_VERIFICATION',
        ]);

        // 1. Request Revision
        $revResponse = $this->actingAs($this->supervisorEmployee->user)->post("/tasks/{$task->id}/review", [
            'decision' => 'REVISION',
            'score' => 65,
            'feedback' => 'Format tabel belum rapi.',
            'revision_notes' => 'Tolong perbaiki tabel di halaman 3 dan cantumkan stempel.',
        ]);

        $revResponse->assertRedirect("/tasks/{$task->id}");
        $this->assertEquals('REVISION', $task->fresh()->status);
        $this->assertDatabaseHas('task_reviews', [
            'task_id' => $task->id,
            'decision' => 'REVISION',
        ]);

        // 2. Final Approval
        $appResponse = $this->actingAs($this->supervisorEmployee->user)->post("/tasks/{$task->id}/review", [
            'decision' => 'APPROVED',
            'score' => 95,
            'feedback' => 'Luar biasa, revisi sudah sangat baik.',
        ]);

        $appResponse->assertRedirect("/tasks/{$task->id}");
        $this->assertEquals('APPROVED', $task->fresh()->status);
        $this->assertNotNull($task->fresh()->completed_at);
    }

    public function test_can_download_task_attachment(): void
    {
        $task = Task::create([
            'assigned_by' => $this->supervisorEmployee->id,
            'assigned_to' => $this->staffEmployee->id,
            'title' => 'Tugas Download '.uniqid(),
            'status' => 'WAITING_VERIFICATION',
        ]);

        $fakeFile = UploadedFile::fake()->create('dokumen_rahasia.pdf', 200);
        $path = $fakeFile->store('tasks/evidences', 'local');

        $attachment = TaskAttachment::create([
            'task_id' => $task->id,
            'file_name' => 'dokumen_rahasia.pdf',
            'file_path' => $path,
            'file_size' => 200,
            'file_type' => 'application/pdf',
            'uploaded_by' => $this->staffEmployee->user->id,
        ]);

        $downloadResponse = $this->actingAs($this->adminUser)->get("/tasks/{$task->id}/attachments/{$attachment->id}/download");
        $downloadResponse->assertStatus(200);
    }

    public function test_can_list_and_register_intern(): void
    {
        $this->actingAs($this->adminUser)->get('/internship')->assertStatus(200);
        $this->actingAs($this->adminUser)->get('/internship/create')->assertStatus(200);

        $internName = 'Mahasiswa Magang '.uniqid();
        $internEmail = 'intern_'.uniqid().'@example.test';

        $response = $this->actingAs($this->adminUser)->post('/internship', [
            'full_name' => $internName,
            'institution' => 'Institut Teknologi Bandung',
            'major' => 'Sistem Informasi',
            'phone' => '081234567890',
            'department_id' => $this->department->id,
            'mentor_id' => $this->supervisorEmployee->id,
            'start_date' => '2026-01-01',
            'end_date' => '2026-06-30',
            'create_user_account' => true,
            'email' => $internEmail,
            'password' => 'InternSecret123!',
        ]);

        $this->assertDatabaseHas('interns', [
            'full_name' => $internName,
            'institution' => 'Institut Teknologi Bandung',
            'status' => 'ACTIVE',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => $internEmail,
        ]);

        $intern = Intern::where('full_name', $internName)->first();
        $this->assertNotNull($intern);
        $response->assertRedirect("/internship/{$intern->id}");
    }

    public function test_can_view_intern_dossier(): void
    {
        $intern = Intern::create([
            'mentor_id' => $this->supervisorEmployee->id,
            'department_id' => $this->department->id,
            'intern_code' => 'INT-'.date('Ym').'-'.rand(1000, 9999),
            'full_name' => 'Peserta Dossier Test '.uniqid(),
            'institution' => 'Universitas Indonesia',
            'start_date' => '2026-02-01',
            'end_date' => '2026-05-31',
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($this->adminUser)->get("/internship/{$intern->id}");
        $response->assertStatus(200);
        $response->assertSee($intern->full_name);
        $response->assertSee('Universitas Indonesia');
    }

    public function test_intern_can_submit_logbook(): void
    {
        $internUser = User::create([
            'name' => 'Intern Logbook User '.uniqid(),
            'email' => 'intern_log_'.uniqid().'@example.test',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $intern = Intern::create([
            'user_id' => $internUser->id,
            'mentor_id' => $this->supervisorEmployee->id,
            'department_id' => $this->department->id,
            'intern_code' => 'INT-'.date('Ym').'-'.rand(1000, 9999),
            'full_name' => 'Intern Tester',
            'institution' => 'Politeknik Negeri Jakarta',
            'start_date' => '2026-01-01',
            'end_date' => '2026-06-30',
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($internUser)->get('/internship/logbooks')->assertStatus(200);

        $response = $this->actingAs($internUser)->post('/internship/logbooks', [
            'date' => '2026-03-15',
            'activity_description' => 'Mempelajari arsitektur database modular dan membuat relasi migration.',
            'output_url' => 'https://github.com/company/repo/commit/12345',
        ]);

        $response->assertRedirect('/internship/logbooks');

        $this->assertDatabaseHas('internship_logbooks', [
            'intern_id' => $intern->id,
            'date' => '2026-03-15',
            'status' => 'SUBMITTED',
        ]);
    }

    public function test_mentor_can_verify_logbook(): void
    {
        $intern = Intern::create([
            'mentor_id' => $this->supervisorEmployee->id,
            'department_id' => $this->department->id,
            'intern_code' => 'INT-'.date('Ym').'-'.rand(1000, 9999),
            'full_name' => 'Intern Verif Test',
            'institution' => 'Universitas Gadjah Mada',
            'start_date' => '2026-01-01',
            'end_date' => '2026-06-30',
            'status' => 'ACTIVE',
        ]);

        $logbook = InternshipLogbook::create([
            'intern_id' => $intern->id,
            'date' => '2026-03-16',
            'activity_description' => 'Menyelesaikan modul pengujian feature test.',
            'status' => 'SUBMITTED',
        ]);

        $response = $this->actingAs($this->supervisorEmployee->user)->patch("/internship/logbooks/{$logbook->id}/verify", [
            'status' => 'VERIFIED',
            'verification_notes' => 'Kerja bagus, logbook disetujui.',
        ]);

        $response->assertRedirect();
        $this->assertEquals('VERIFIED', $logbook->fresh()->status);
        $this->assertEquals($this->supervisorEmployee->id, $logbook->fresh()->verified_by);
    }

    public function test_mentor_can_evaluate_intern(): void
    {
        $intern = Intern::create([
            'mentor_id' => $this->supervisorEmployee->id,
            'department_id' => $this->department->id,
            'intern_code' => 'INT-'.date('Ym').'-'.rand(1000, 9999),
            'full_name' => 'Intern Eval Test',
            'institution' => 'Universitas Diponegoro',
            'start_date' => '2026-01-01',
            'end_date' => '2026-06-30',
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($this->supervisorEmployee->user)->post("/internship/{$intern->id}/evaluate", [
            'discipline_score' => 90,
            'technical_score' => 88,
            'teamwork_score' => 92,
            'communication_score' => 86,
            'recommendation' => 'Sangat direkomendasikan untuk direkrut menjadi Junior Staff.',
            'certificate_number' => 'CERT-INT-001/2026',
            'evaluated_at' => '2026-06-30',
            'mark_completed' => true,
        ]);

        $response->assertRedirect("/internship/{$intern->id}");

        $this->assertDatabaseHas('internship_evaluations', [
            'intern_id' => $intern->id,
            'final_score' => 89.00, // (90 + 88 + 92 + 86) / 4 = 89
            'certificate_number' => 'CERT-INT-001/2026',
        ]);

        $this->assertEquals('COMPLETED', $intern->fresh()->status);
    }
}
