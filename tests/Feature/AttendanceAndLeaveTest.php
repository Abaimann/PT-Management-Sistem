<?php

namespace Tests\Feature;

use App\Models\Attendance\AttendanceCorrection;
use App\Models\Attendance\AttendanceRecord;
use App\Models\Core\CompanySetting;
use App\Models\Core\Department;
use App\Models\Core\Position;
use App\Models\Core\Role;
use App\Models\Core\User;
use App\Models\HRIS\Employee;
use App\Models\HRIS\LeaveBalance;
use App\Models\HRIS\LeaveRequest;
use App\Models\HRIS\LeaveType;
use App\Models\HRIS\Shift;
use App\Services\Attendance\AttendanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttendanceAndLeaveTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected Employee $employee;

    protected Shift $shift;

    protected CompanySetting $company;

    protected AttendanceService $attendanceService;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $role = Role::firstOrCreate(['code' => 'super_admin'], ['name' => 'Super Admin']);

        $this->user = User::create([
            'name' => 'Attendance Test User',
            'email' => 'attendance_'.uniqid().'@example.test',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $this->user->roles()->sync([$role->id]);

        $department = Department::firstOrCreate(
            ['code' => 'IT-TEST'],
            ['name' => 'IT Testing', 'is_active' => true]
        );

        $position = Position::firstOrCreate(
            ['department_id' => $department->id, 'name' => 'QA Engineer'],
            ['grade_level' => 'Staff', 'is_active' => true]
        );

        $this->employee = Employee::create([
            'user_id' => $this->user->id,
            'nik' => 'EMP-'.rand(10000, 99999),
            'full_name' => 'Test Employee Smart Attendance',
            'gender' => 'MALE',
            'birth_date' => '1992-08-20',
            'phone' => '+62 812-9988-7766',
            'department_id' => $department->id,
            'position_id' => $position->id,
            'employment_status' => 'PERMANENT',
            'join_date' => '2024-01-01',
            'is_active' => true,
        ]);

        $this->shift = Shift::firstOrCreate(
            ['code' => 'SHIFT-NORMAL'],
            [
                'name' => 'Shift Normal',
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'late_tolerance_minutes' => 15,
                'is_active' => true,
            ]
        );

        $this->company = CompanySetting::firstOrCreate(
            ['id' => 1],
            [
                'company_name' => 'PT Management Sistem',
                'latitude' => -6.225014,
                'longitude' => 106.809657,
                'geofence_radius_meters' => 100,
            ]
        );

        $this->attendanceService = app(AttendanceService::class);
    }

    public function test_haversine_distance_calculation(): void
    {
        // Distance from office to exact same point must be 0
        $distanceSame = $this->attendanceService->calculateDistance(
            -6.225014, 106.809657,
            -6.225014, 106.809657
        );
        $this->assertEquals(0, $distanceSame);

        // A small offset of ~50 meters
        $distanceNear = $this->attendanceService->calculateDistance(
            -6.225014, 106.809657,
            -6.225300, 106.809657
        );
        $this->assertGreaterThan(0, $distanceNear);
        $this->assertLessThan(100, $distanceNear);
    }

    public function test_employee_can_check_in_with_selfie_and_geofence(): void
    {
        $fakeSelfie = 'data:image/jpeg;base64,'.base64_encode('fake-image-binary-data');

        $response = $this->actingAs($this->user)->postJson('/attendance/check-in', [
            'latitude' => -6.225014,
            'longitude' => 106.809657,
            'selfie' => $fakeSelfie,
            'notes' => 'Hadir tepat waktu di kantor',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('attendance_records', [
            'employee_id' => $this->employee->id,
            'date' => Carbon::today('Asia/Jakarta')->toDateString(),
        ]);

        $record = AttendanceRecord::where('employee_id', $this->employee->id)->first();
        $this->assertNotNull($record->clock_in);
        $this->assertNotNull($record->clock_in_selfie);
        Storage::disk('local')->assertExists($record->clock_in_selfie);
    }

    public function test_duplicate_check_in_on_same_day_is_prevented(): void
    {
        $fakeSelfie = 'data:image/jpeg;base64,'.base64_encode('fake-image-binary-data');

        // First check-in
        $this->actingAs($this->user)->postJson('/attendance/check-in', [
            'latitude' => -6.225014,
            'longitude' => 106.809657,
            'selfie' => $fakeSelfie,
        ])->assertStatus(200);

        // Second check-in must fail with 422
        $responseSecond = $this->actingAs($this->user)->postJson('/attendance/check-in', [
            'latitude' => -6.225014,
            'longitude' => 106.809657,
            'selfie' => $fakeSelfie,
        ]);

        $responseSecond->assertStatus(422);
        $responseSecond->assertJson(['success' => false]);
    }

    public function test_check_out_requires_prior_check_in(): void
    {
        // Attempt check-out without prior check-in
        $response = $this->actingAs($this->user)->postJson('/attendance/check-out', [
            'latitude' => -6.225014,
            'longitude' => 106.809657,
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    public function test_successful_check_in_and_check_out_cycle(): void
    {
        $fakeSelfie = 'data:image/jpeg;base64,'.base64_encode('fake-image-data');

        // 1. Check-in
        $this->actingAs($this->user)->postJson('/attendance/check-in', [
            'latitude' => -6.225014,
            'longitude' => 106.809657,
            'selfie' => $fakeSelfie,
        ])->assertStatus(200);

        // 2. Check-out
        $responseOut = $this->actingAs($this->user)->postJson('/attendance/check-out', [
            'latitude' => -6.225014,
            'longitude' => 106.809657,
            'selfie' => $fakeSelfie,
            'notes' => 'Pulang kantor selesai shift',
        ]);

        $responseOut->assertStatus(200);
        $responseOut->assertJson(['success' => true]);

        $record = AttendanceRecord::where('employee_id', $this->employee->id)->first();
        $this->assertNotNull($record->clock_out);
    }

    public function test_attendance_correction_workflow(): void
    {
        $record = AttendanceRecord::create([
            'employee_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'date' => Carbon::today()->subDay()->toDateString(),
            'clock_in' => '09:30:00',
            'clock_out' => '17:00:00',
            'status' => 'LATE',
            'late_minutes' => 75,
        ]);

        // Submit correction
        $storeResponse = $this->actingAs($this->user)->post('/attendance/corrections', [
            'attendance_record_id' => $record->id,
            'proposed_clock_in' => '08:00',
            'proposed_clock_out' => '17:00',
            'reason' => 'Perjalanan dinas luar kota bertemu klien sehingga tidak sempat presensi dari kantor',
        ]);

        $storeResponse->assertRedirect('/attendance/corrections');
        $correction = AttendanceCorrection::where('attendance_record_id', $record->id)->first();
        $this->assertNotNull($correction);
        $this->assertEquals('PENDING', $correction->status);

        // HR approves correction
        $approveResponse = $this->actingAs($this->user)->post("/attendance/corrections/{$correction->id}/approve", [
            'action' => 'approve',
        ]);

        $approveResponse->assertRedirect('/attendance/corrections');
        $correction->refresh();
        $record->refresh();

        $this->assertEquals('APPROVED', $correction->status);
        $this->assertEquals('08:00', substr($record->clock_in, 0, 5));
        $this->assertEquals('ON_TIME', $record->status);
    }

    public function test_leave_request_and_quota_deduction_workflow(): void
    {
        $leaveType = LeaveType::firstOrCreate(
            ['code' => 'ANNUAL-TEST'],
            ['name' => 'Cuti Tahunan Test', 'default_quota' => 12, 'is_paid' => true, 'is_active' => true]
        );

        $year = Carbon::now()->year;
        $balance = LeaveBalance::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $leaveType->id,
            'year' => $year,
            'total_quota' => 12,
            'used_quota' => 0,
            'remaining_quota' => 12,
        ]);

        // 1. Submit Leave Request (3 days)
        $startDate = Carbon::today()->addDays(5)->toDateString();
        $endDate = Carbon::today()->addDays(7)->toDateString();

        $response = $this->actingAs($this->user)->post('/leaves', [
            'leave_type_id' => $leaveType->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => 'Keperluan keluarga dan liburan tahunan',
        ]);

        $response->assertRedirect('/leaves');
        $leaveRequest = LeaveRequest::where('employee_id', $this->employee->id)->latest()->first();
        $this->assertNotNull($leaveRequest);
        $this->assertEquals(3, $leaveRequest->total_days);
        $this->assertEquals('PENDING', $leaveRequest->status);

        // 2. Approve Leave Request
        $approveResponse = $this->actingAs($this->user)->post("/leaves/{$leaveRequest->id}/approve");
        $approveResponse->assertRedirect('/leaves');

        $leaveRequest->refresh();
        $balance->refresh();

        $this->assertEquals('APPROVED', $leaveRequest->status);
        $this->assertEquals(9, $balance->remaining_quota);
        $this->assertEquals(3, $balance->used_quota);

        // 3. Attendance records should be automatically marked as LEAVE
        $this->assertDatabaseHas('attendance_records', [
            'employee_id' => $this->employee->id,
            'date' => $startDate,
            'status' => 'LEAVE',
        ]);
    }
}
