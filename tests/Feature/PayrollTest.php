<?php

namespace Tests\Feature;

use App\Models\Attendance\AttendanceRecord;
use App\Models\Core\AuditLog;
use App\Models\Core\Department;
use App\Models\Core\Position;
use App\Models\Core\Role;
use App\Models\Core\User;
use App\Models\HRIS\Employee;
use App\Models\HRIS\Shift;
use App\Models\Payroll\EmployeeSalaryComponent;
use App\Models\Payroll\PayrollPeriod;
use App\Models\Payroll\PayrollRun;
use App\Models\Payroll\Payslip;
use App\Models\Payroll\SalaryComponent;
use App\Services\Payroll\PayrollService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PayrollTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;

    protected User $directorUser;

    protected User $employeeUserA;

    protected User $employeeUserB;

    protected Employee $employeeA;

    protected Employee $employeeB;

    protected Department $department;

    protected Position $position;

    protected Shift $shift;

    protected PayrollService $payrollService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->payrollService = app(PayrollService::class);
        $this->payrollService->ensureMasterSalaryComponents();

        // 1. Roles
        $superAdminRole = Role::firstOrCreate(['code' => 'super_admin'], ['name' => 'Super Admin']);
        $directorRole = Role::firstOrCreate(['code' => 'director'], ['name' => 'Direksi']);
        $employeeRole = Role::firstOrCreate(['code' => 'employee'], ['name' => 'Karyawan']);

        // 2. Department & Position
        $this->department = Department::firstOrCreate(
            ['code' => 'ENG-PAY'],
            ['name' => 'Engineering Payroll', 'is_active' => true]
        );

        $this->position = Position::firstOrCreate(
            ['department_id' => $this->department->id, 'name' => 'Senior Developer'],
            ['grade_level' => 'Senior Staff', 'is_active' => true]
        );

        $this->shift = Shift::firstOrCreate(
            ['code' => 'SHIFT-REG'],
            [
                'name' => 'Regular Shift',
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'late_tolerance_minutes' => 15,
                'is_active' => true,
            ]
        );

        // 3. Admin User
        $this->adminUser = User::create([
            'name' => 'Payroll Admin',
            'email' => 'payroll_admin_' . uniqid() . '@example.test',
            'password' => Hash::make('secret123'),
            'is_active' => true,
        ]);
        $this->adminUser->roles()->sync([$superAdminRole->id]);

        // 4. Director User
        $this->directorUser = User::create([
            'name' => 'Company Director',
            'email' => 'director_' . uniqid() . '@example.test',
            'password' => Hash::make('secret123'),
            'is_active' => true,
        ]);
        $this->directorUser->roles()->sync([$directorRole->id]);

        // 5. Employee A
        $this->employeeUserA = User::create([
            'name' => 'Employee Alice',
            'email' => 'alice_' . uniqid() . '@example.test',
            'password' => Hash::make('secret123'),
            'is_active' => true,
        ]);
        $this->employeeUserA->roles()->sync([$employeeRole->id]);

        $this->employeeA = Employee::create([
            'user_id' => $this->employeeUserA->id,
            'nik' => 'NIK-' . rand(10000, 99999),
            'full_name' => 'Alice Developer',
            'gender' => 'FEMALE',
            'birth_date' => '1995-03-15',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_status' => 'PERMANENT',
            'join_date' => '2024-01-01',
            'is_active' => true,
        ]);

        // 6. Employee B
        $this->employeeUserB = User::create([
            'name' => 'Employee Bob',
            'email' => 'bob_' . uniqid() . '@example.test',
            'password' => Hash::make('secret123'),
            'is_active' => true,
        ]);
        $this->employeeUserB->roles()->sync([$employeeRole->id]);

        $this->employeeB = Employee::create([
            'user_id' => $this->employeeUserB->id,
            'nik' => 'NIK-' . rand(10000, 99999),
            'full_name' => 'Bob Engineer',
            'gender' => 'MALE',
            'birth_date' => '1993-07-20',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_status' => 'PERMANENT',
            'join_date' => '2024-02-01',
            'is_active' => true,
        ]);
    }

    public function test_authorized_user_can_view_payroll_periods_index(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('payroll.periods.index'));

        $response->assertStatus(200);
        $response->assertViewIs('payroll.periods.index');
        $response->assertSee('Manajemen Penggajian (Payroll)');
    }

    public function test_can_create_new_payroll_period(): void
    {
        $code = 'PR-' . date('Ym') . rand(10, 99);
        $data = [
            'code' => $code,
            'name' => 'Payroll Periode Test ' . $code,
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-30',
            'cutoff_start_date' => '2026-11-01',
            'cutoff_end_date' => '2026-11-25',
            'cutoff_date' => '2026-11-25',
            'payment_date' => '2026-11-30',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('payroll.periods.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('payroll_periods', [
            'code' => $code,
            'status' => 'DRAFT',
        ]);
    }

    public function test_payroll_calculation_calculates_components_and_payslips(): void
    {
        $period = PayrollPeriod::create([
            'code' => 'PR-CALC-' . uniqid(),
            'name' => 'Payroll Calculation Test Period',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
            'cutoff_start_date' => '2026-10-01',
            'cutoff_end_date' => '2026-10-25',
            'cutoff_date' => '2026-10-25',
            'payment_date' => '2026-10-31',
            'status' => 'DRAFT',
        ]);

        // Custom salary component for Employee A: Basic 7,000,000 & Loan deduction 500,000
        $basicComp = SalaryComponent::where('code', 'BASIC_SALARY')->first();
        EmployeeSalaryComponent::updateOrCreate(
            ['employee_id' => $this->employeeA->id, 'salary_component_id' => $basicComp->id],
            ['amount' => 7000000.00, 'is_active' => true]
        );

        $loanComp = SalaryComponent::where('code', 'LOAN_DED')->first();
        EmployeeSalaryComponent::updateOrCreate(
            ['employee_id' => $this->employeeA->id, 'salary_component_id' => $loanComp->id],
            ['amount' => 500000.00, 'is_active' => true]
        );

        // Create attendance for Employee A in cutoff period:
        // 5 days ON_TIME, 1 day LATE (30 mins)
        for ($i = 1; $i <= 5; $i++) {
            AttendanceRecord::updateOrCreate(
                ['employee_id' => $this->employeeA->id, 'date' => "2026-10-0{$i}"],
                [
                    'shift_id' => $this->shift->id,
                    'clock_in' => '08:00:00',
                    'clock_out' => '17:00:00',
                    'status' => 'ON_TIME',
                    'late_minutes' => 0,
                    'is_locked' => false,
                ]
            );
        }

        AttendanceRecord::updateOrCreate(
            ['employee_id' => $this->employeeA->id, 'date' => '2026-10-06'],
            [
                'shift_id' => $this->shift->id,
                'clock_in' => '08:30:00',
                'clock_out' => '17:00:00',
                'status' => 'LATE',
                'late_minutes' => 30,
                'is_locked' => false,
            ]
        );

        // Execute Calculation
        $response = $this->actingAs($this->adminUser)->post(route('payroll.periods.calculate', $period));

        $response->assertRedirect(route('payroll.periods.show', $period));
        $response->assertSessionHas('success');

        $period->refresh();
        $this->assertEquals('CALCULATED', $period->status);

        $this->assertDatabaseHas('payroll_runs', [
            'payroll_period_id' => $period->id,
            'status' => 'COMPLETED',
        ]);

        $payslipA = Payslip::where('employee_id', $this->employeeA->id)->first();
        $this->assertNotNull($payslipA);
        $this->assertGreaterThan(0, $payslipA->gross_salary);
        $this->assertGreaterThan(0, $payslipA->net_salary);

        // Check Payslip Items
        $this->assertDatabaseHas('payslip_items', [
            'payslip_id' => $payslipA->id,
            'name' => 'Gaji Pokok',
            'amount' => 7000000.00,
            'type' => 'EARNING',
        ]);

        $this->assertDatabaseHas('payslip_items', [
            'payslip_id' => $payslipA->id,
            'name' => 'Potongan Pinjaman / Kasbon Karyawan',
            'amount' => 500000.00,
            'type' => 'DEDUCTION',
        ]);

        // Late deduction item: 30 minutes * 1000 = 30,000
        $this->assertDatabaseHas('payslip_items', [
            'payslip_id' => $payslipA->id,
            'type' => 'DEDUCTION',
            'amount' => 30000.00,
        ]);
    }

    public function test_director_or_authorized_user_can_approve_payroll_period(): void
    {
        $period = PayrollPeriod::create([
            'code' => 'PR-APPR-' . uniqid(),
            'name' => 'Payroll Approval Test Period',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
            'cutoff_start_date' => '2026-10-01',
            'cutoff_end_date' => '2026-10-25',
            'cutoff_date' => '2026-10-25',
            'payment_date' => '2026-10-31',
            'status' => 'CALCULATED',
        ]);

        $response = $this->actingAs($this->directorUser)->post(route('payroll.periods.approve', $period));

        $response->assertRedirect(route('payroll.periods.show', $period));
        $response->assertSessionHas('success');

        $period->refresh();
        $this->assertEquals('APPROVED', $period->status);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $this->directorUser->id,
            'event' => 'PAYROLL_APPROVED',
            'auditable_type' => PayrollPeriod::class,
            'auditable_id' => $period->id,
        ]);
    }

    public function test_payroll_closing_guard_cannot_close_if_not_approved(): void
    {
        $period = PayrollPeriod::create([
            'code' => 'PR-GUARD-' . uniqid(),
            'name' => 'Payroll Guard Test Period',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
            'cutoff_start_date' => '2026-10-01',
            'cutoff_end_date' => '2026-10-25',
            'cutoff_date' => '2026-10-25',
            'payment_date' => '2026-10-31',
            'status' => 'CALCULATED', // Not yet APPROVED
        ]);

        $response = $this->actingAs($this->directorUser)->post(route('payroll.periods.close', $period));

        $response->assertRedirect(route('payroll.periods.show', $period));
        $response->assertSessionHas('error');

        $period->refresh();
        $this->assertNotEquals('CLOSED', $period->status);
    }

    public function test_closing_payroll_period_locks_attendance_records_and_updates_status(): void
    {
        $period = PayrollPeriod::create([
            'code' => 'PR-CLOSE-' . uniqid(),
            'name' => 'Payroll Close Test Period',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
            'cutoff_start_date' => '2026-10-01',
            'cutoff_end_date' => '2026-10-25',
            'cutoff_date' => '2026-10-25',
            'payment_date' => '2026-10-31',
            'status' => 'APPROVED',
        ]);

        // Attendance record within cutoff period
        $attRecord = AttendanceRecord::updateOrCreate(
            ['employee_id' => $this->employeeA->id, 'date' => '2026-10-10'],
            [
                'shift_id' => $this->shift->id,
                'clock_in' => '08:00:00',
                'clock_out' => '17:00:00',
                'status' => 'ON_TIME',
                'is_locked' => false,
            ]
        );

        $this->assertFalse((bool) $attRecord->is_locked);

        // Run Close
        $response = $this->actingAs($this->directorUser)->post(route('payroll.periods.close', $period));

        $response->assertRedirect(route('payroll.periods.show', $period));
        $response->assertSessionHas('success');

        $period->refresh();
        $this->assertEquals('CLOSED', $period->status);
        $this->assertNotNull($period->closed_at);
        $this->assertEquals($this->directorUser->id, $period->closed_by_id);

        // Assert attendance record is now locked
        $attRecord->refresh();
        $this->assertTrue((bool) $attRecord->is_locked);

        // Assert AuditLog was recorded
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $this->directorUser->id,
            'event' => 'PAYROLL_CLOSED',
            'auditable_type' => PayrollPeriod::class,
            'auditable_id' => $period->id,
        ]);
    }

    public function test_employee_can_only_view_own_payslip_while_officer_can_view_all(): void
    {
        $period = PayrollPeriod::create([
            'code' => 'PR-AUTH-' . uniqid(),
            'name' => 'Payroll Auth Test Period',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
            'cutoff_start_date' => '2026-10-01',
            'cutoff_end_date' => '2026-10-25',
            'cutoff_date' => '2026-10-25',
            'payment_date' => '2026-10-31',
            'status' => 'CLOSED',
        ]);

        $run = PayrollRun::create([
            'payroll_period_id' => $period->id,
            'run_number' => 'RUN-TEST-' . uniqid(),
            'run_date' => '2026-10-31',
            'total_employees' => 2,
            'total_gross_pay' => 10000000,
            'total_deductions' => 500000,
            'total_net_pay' => 9500000,
            'status' => 'COMPLETED',
        ]);

        $payslipA = Payslip::create([
            'payroll_run_id' => $run->id,
            'employee_id' => $this->employeeA->id,
            'payslip_number' => 'SLIP/A/' . uniqid(),
            'gross_salary' => 5000000,
            'total_deductions' => 250000,
            'net_salary' => 4750000,
            'status' => 'PUBLISHED',
        ]);

        $payslipB = Payslip::create([
            'payroll_run_id' => $run->id,
            'employee_id' => $this->employeeB->id,
            'payslip_number' => 'SLIP/B/' . uniqid(),
            'gross_salary' => 5000000,
            'total_deductions' => 250000,
            'net_salary' => 4750000,
            'status' => 'PUBLISHED',
        ]);

        // 1. Employee A views own payslip -> 200 OK
        $resA = $this->actingAs($this->employeeUserA)->get(route('payroll.payslips.show', $payslipA));
        $resA->assertStatus(200);
        $resA->assertSee($payslipA->payslip_number);

        // 2. Employee A tries to view Employee B's payslip -> 403 Forbidden
        $resForbidden = $this->actingAs($this->employeeUserA)->get(route('payroll.payslips.show', $payslipB));
        $resForbidden->assertStatus(403);

        // 3. Admin/Payroll Officer views Employee B's payslip -> 200 OK
        $resAdmin = $this->actingAs($this->adminUser)->get(route('payroll.payslips.show', $payslipB));
        $resAdmin->assertStatus(200);
        $resAdmin->assertSee($payslipB->payslip_number);
    }
}
