<?php

namespace Tests\Feature;

use App\Models\Attendance\AttendanceCorrection;
use App\Models\Attendance\AttendanceRecord;
use App\Models\Core\CompanySetting;
use App\Models\Core\Department;
use App\Models\Core\Position;
use App\Models\Core\Role;
use App\Models\Core\User;
use App\Models\Finance\Budget;
use App\Models\Finance\ChartOfAccount;
use App\Models\Finance\Reimbursement;
use App\Models\HRIS\Employee;
use App\Models\HRIS\LeaveRequest;
use App\Models\HRIS\LeaveType;
use App\Models\HRIS\Shift;
use App\Models\Inventory\Asset;
use App\Models\Inventory\Item;
use App\Models\Inventory\StockBalance;
use App\Models\Inventory\Warehouse;
use App\Models\Operations\Task;
use App\Models\Payroll\PayrollPeriod;
use App\Models\Payroll\PayrollRun;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Procurement\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ExecutiveAndIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    protected User $directorUser;

    protected User $superAdminUser;

    protected User $employeeUser;

    protected Employee $employee;

    protected Department $department;

    protected Position $position;

    protected function setUp(): void
    {
        parent::setUp();

        $directorRole = Role::firstOrCreate(['code' => 'director'], ['name' => 'Direksi']);
        $superAdminRole = Role::firstOrCreate(['code' => 'super_admin'], ['name' => 'Super Admin']);
        $employeeRole = Role::firstOrCreate(['code' => 'employee'], ['name' => 'Karyawan']);

        // Director
        $this->directorUser = User::create([
            'name' => 'Executive Director',
            'email' => 'director_' . uniqid() . '@example.test',
            'password' => Hash::make('secret123'),
            'is_active' => true,
        ]);
        $this->directorUser->roles()->sync([$directorRole->id]);

        // Super Admin
        $this->superAdminUser = User::create([
            'name' => 'Executive Super Admin',
            'email' => 'superadmin_' . uniqid() . '@example.test',
            'password' => Hash::make('secret123'),
            'is_active' => true,
        ]);
        $this->superAdminUser->roles()->sync([$superAdminRole->id]);

        // Regular Employee User
        $this->employeeUser = User::create([
            'name' => 'Regular Staff',
            'email' => 'staff_' . uniqid() . '@example.test',
            'password' => Hash::make('secret123'),
            'is_active' => true,
        ]);
        $this->employeeUser->roles()->sync([$employeeRole->id]);

        // Department & Position
        $this->department = Department::firstOrCreate(
            ['code' => 'EXEC-TEST'],
            ['name' => 'Executive Office', 'is_active' => true]
        );

        $this->position = Position::firstOrCreate(
            ['department_id' => $this->department->id, 'name' => 'Executive Staff'],
            ['grade_level' => 'Staff', 'is_active' => true]
        );

        $this->employee = Employee::create([
            'user_id' => $this->employeeUser->id,
            'nik' => 'NIK-' . rand(10000, 99999),
            'full_name' => 'Staff Regular Person',
            'gender' => 'MALE',
            'birth_date' => '1990-05-10',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_status' => 'PERMANENT',
            'join_date' => '2024-01-01',
            'is_active' => true,
        ]);

        CompanySetting::firstOrCreate(
            ['id' => 1],
            [
                'company_name' => 'PT Management Sistem',
                'legal_name' => 'PT MANAGEMENT SISTEM INDONESIA',
                'npwp' => '01.234.567.8-012.000',
                'address' => 'Gedung Cyber 2 Lantai 18',
                'city' => 'Jakarta Selatan',
                'postal_code' => '12950',
            ]
        );
    }

    public function test_director_can_access_executive_dashboard_and_see_kpi_summary(): void
    {
        $response = $this->actingAs($this->directorUser)->get(route('executive.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('executive.index');
        $response->assertSee('Executive Command Center');
        $response->assertSee('SDM &amp; Kehadiran', false);
    }

    public function test_super_admin_can_access_executive_dashboard(): void
    {
        $response = $this->actingAs($this->superAdminUser)->get(route('executive.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('executive.index');
    }

    public function test_regular_employee_is_forbidden_from_executive_dashboard(): void
    {
        $response = $this->actingAs($this->employeeUser)->get(route('executive.dashboard'));

        $response->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('executive.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_cross_module_data_integrity_in_executive_dashboard(): void
    {
        $today = now()->toDateString();

        // 1. Attendance Record
        $shift = Shift::firstOrCreate(
            ['code' => 'SHIFT-EXEC'],
            ['name' => 'Shift Exec', 'start_time' => '08:00', 'end_time' => '17:00', 'late_tolerance_minutes' => 15, 'is_active' => true]
        );

        $attRecord = AttendanceRecord::updateOrCreate(
            ['employee_id' => $this->employee->id, 'date' => $today],
            [
                'shift_id' => $shift->id,
                'clock_in' => '07:55:00',
                'status' => 'ON_TIME',
                'late_minutes' => 0,
                'is_locked' => false,
            ]
        );

        // 2. Pending Leave Request
        $leaveType = LeaveType::firstOrCreate(
            ['code' => 'ANNUAL-EXEC'],
            ['name' => 'Annual Leave', 'default_quota' => 12, 'is_paid' => true, 'is_active' => true]
        );

        LeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(6)->toDateString(),
            'total_days' => 2,
            'reason' => 'Keperluan keluarga',
            'status' => 'PENDING',
        ]);

        // 3. Pending Attendance Correction
        AttendanceCorrection::create([
            'attendance_record_id' => $attRecord->id,
            'employee_id' => $this->employee->id,
            'proposed_clock_in' => '07:50:00',
            'reason' => 'Koreksi jam masuk fingerprint',
            'status' => 'PENDING',
        ]);

        // 4. Pending Task Review
        Task::create([
            'assigned_by' => $this->employee->id,
            'assigned_to' => $this->employee->id,
            'title' => 'Laporan Audit Kuartal',
            'description' => 'Menyusun laporan audit integrasi sistem',
            'priority' => 'HIGH',
            'status' => 'REVIEW',
            'due_date' => now()->addDays(2),
        ]);

        // 5. Pending Purchase Request
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR-INT-' . uniqid(),
            'department_id' => $this->department->id,
            'requester_id' => $this->employee->id,
            'request_date' => now()->toDateString(),
            'required_date' => now()->addDays(10)->toDateString(),
            'total_amount' => 15000000.00,
            'reason' => 'Pengadaan server database',
            'status' => 'PENDING',
        ]);

        // 6. Pending Purchase Order
        $vendor = Vendor::firstOrCreate(
            ['code' => 'VEND-INT-1'],
            ['name' => 'PT Teknologi Server', 'is_active' => true]
        );

        PurchaseOrder::create([
            'po_number' => 'PO-INT-' . uniqid(),
            'purchase_request_id' => $pr->id,
            'vendor_id' => $vendor->id,
            'po_date' => now()->toDateString(),
            'delivery_date' => now()->addDays(7)->toDateString(),
            'subtotal' => 15000000.00,
            'tax_amount' => 1650000.00,
            'total_amount' => 16650000.00,
            'status' => 'PENDING',
        ]);

        // 7. Pending Reimbursement
        Reimbursement::create([
            'employee_id' => $this->employee->id,
            'claim_number' => 'CLM-INT-' . uniqid(),
            'title' => 'Biaya Transportasi Dinas',
            'description' => 'Kunjungan kerja ke kantor cabang',
            'amount' => 750000.00,
            'status' => 'PENDING',
        ]);

        // 8. Calculated Payroll Period
        $period = PayrollPeriod::create([
            'code' => 'PR-INT-' . uniqid(),
            'name' => 'Payroll Integrasi Test',
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
            'cutoff_date' => now()->toDateString(),
            'payment_date' => now()->endOfMonth()->toDateString(),
            'status' => 'CALCULATED',
        ]);

        PayrollRun::create([
            'payroll_period_id' => $period->id,
            'run_number' => 'RUN-INT-' . uniqid(),
            'run_date' => now()->toDateString(),
            'total_employees' => 1,
            'total_gross_pay' => 8000000.00,
            'total_deductions' => 500000.00,
            'total_net_pay' => 7500000.00,
            'status' => 'COMPLETED',
        ]);

        // 9. Active Fixed Asset
        Asset::create([
            'asset_code' => 'AST-INT-' . uniqid(),
            'name' => 'Server Dell PowerEdge R750',
            'category' => 'IT Infrastructure',
            'acquisition_date' => '2025-01-15',
            'acquisition_cost' => 85000000.00,
            'book_value' => 70000000.00,
            'condition' => 'GOOD',
            'status' => 'ACTIVE',
        ]);

        // 10. Item with Low Stock
        $warehouse = Warehouse::firstOrCreate(
            ['code' => 'WH-MAIN'],
            ['name' => 'Main Warehouse', 'is_active' => true]
        );

        $item = Item::create([
            'sku' => 'SKU-INT-' . uniqid(),
            'name' => 'Kertas A4 Sinar Dunia 80gsm',
            'category' => 'ATK',
            'unit' => 'Rim',
            'minimum_stock' => 10.00,
            'is_active' => true,
        ]);

        StockBalance::create([
            'warehouse_id' => $warehouse->id,
            'item_id' => $item->id,
            'quantity' => 2.00, // Below minimum 10
        ]);

        // 11. Budget
        $coa = ChartOfAccount::firstOrCreate(
            ['account_code' => '5101-INT'],
            ['account_name' => 'Beban Operasional IT', 'classification' => 'EXPENSE', 'normal_balance' => 'DEBIT', 'is_active' => true]
        );

        Budget::create([
            'department_id' => $this->department->id,
            'chart_of_account_id' => $coa->id,
            'fiscal_year' => now()->year,
            'allocated_amount' => 100000000.00,
            'spent_amount' => 25000000.00,
            'remaining_amount' => 75000000.00,
            'status' => 'APPROVED',
        ]);

        // Execute request to Executive Dashboard
        $response = $this->actingAs($this->directorUser)->get(route('executive.dashboard'));

        $response->assertStatus(200);

        // Verify aggregated cross-module metrics in view
        $viewData = $response->viewData('totalPendingApprovals');
        $this->assertGreaterThanOrEqual(7, $viewData);

        $presentCount = $response->viewData('presentToday');
        $this->assertGreaterThanOrEqual(1, $presentCount);

        $assetValue = $response->viewData('totalAssetBookValue');
        $this->assertGreaterThanOrEqual(70000000.00, $assetValue);

        $lowStock = $response->viewData('lowStockCount');
        $this->assertGreaterThanOrEqual(1, $lowStock);

        $highValueList = $response->viewData('highValueApprovals');
        $this->assertNotEmpty($highValueList);
    }

    /**
     * Helper to create a user assigned to a specific canonical role.
     */
    protected function getOrCreateRoleUser(string $roleCode): User
    {
        $role = Role::firstOrCreate(
            ['code' => $roleCode],
            ['name' => ucwords(str_replace('_', ' ', $roleCode))]
        );

        $user = User::create([
            'name' => 'User ' . $role->name . ' ' . uniqid(),
            'email' => $roleCode . '_' . uniqid() . '@example.test',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $user->roles()->sync([$role->id]);

        return $user;
    }

    /**
     * Verify all 10 canonical roles can authenticate and access their authorized resources.
     */
    public function test_all_10_canonical_roles_can_login_and_access_authorized_resources(): void
    {
        $authorizedRoutesByRole = [
            'super_admin' => ['/dashboard', '/executive', '/finance'],
            'director' => ['/executive', '/dashboard', '/finance'],
            'hr_admin' => ['/employees', '/attendance', '/leaves'],
            'supervisor' => ['/dashboard', '/tasks'],
            'employee' => ['/dashboard', '/profile', '/tasks', '/ga/requests'],
            'intern' => ['/dashboard', '/profile', '/internship/logbooks'],
            'payroll_officer' => ['/payroll', '/payroll/periods', '/payroll/periods/create'],
            'finance_officer' => ['/finance', '/finance/accounts', '/reimbursements'],
            'procurement_officer' => ['/procurement/vendors', '/procurement/requests', '/procurement/orders'],
            'inventory_ga' => ['/inventory/items', '/inventory/warehouses', '/inventory/assets'],
        ];

        $this->assertCount(10, $authorizedRoutesByRole, 'All 10 canonical roles must be verified.');

        foreach ($authorizedRoutesByRole as $roleCode => $routes) {
            auth()->logout();

            $user = $this->getOrCreateRoleUser($roleCode);

            // 1. Verify Authentication via Login Form
            $loginResponse = $this->post('/login', [
                'email' => $user->email,
                'password' => 'password123',
            ]);
            $this->assertAuthenticatedAs($user);
            $loginResponse->assertRedirect('/dashboard');

            // 2. Verify Authorized Resource Access
            foreach ($routes as $route) {
                $response = $this->actingAs($user)->get($route);
                $response->assertStatus(200, "Role [{$roleCode}] was unable to access authorized route [{$route}].");
            }

            auth()->logout();
        }
    }

    /**
     * Verify all 8 non-executive roles are strictly rejected (403) from the Executive Dashboard.
     */
    public function test_all_non_executive_roles_are_rejected_with_403_from_executive_dashboard(): void
    {
        $nonExecutiveRoles = [
            'hr_admin',
            'supervisor',
            'employee',
            'intern',
            'payroll_officer',
            'finance_officer',
            'procurement_officer',
            'inventory_ga',
        ];

        $this->assertCount(8, $nonExecutiveRoles);

        foreach ($nonExecutiveRoles as $roleCode) {
            $user = $this->getOrCreateRoleUser($roleCode);

            $response = $this->actingAs($user)->get('/executive');
            $response->assertStatus(403, "Role [{$roleCode}] was not rejected with 403 from Executive Dashboard.");
        }
    }

    /**
     * Verify unauthorized roles are properly rejected with 403 from role-restricted business operations.
     */
    public function test_unauthorized_roles_are_rejected_with_403_from_restricted_operations(): void
    {
        // 1. Payroll Period Creation (forbidden for employee, intern, procurement_officer, inventory_ga)
        $restrictedPayrollRoles = ['employee', 'intern', 'procurement_officer', 'inventory_ga'];
        foreach ($restrictedPayrollRoles as $roleCode) {
            $user = $this->getOrCreateRoleUser($roleCode);
            $response = $this->actingAs($user)->get('/payroll/periods/create');
            $response->assertStatus(403, "Role [{$roleCode}] should receive 403 when creating payroll periods.");
        }

        // 2. Reimbursement Approval (forbidden for employee, intern, payroll_officer, procurement_officer)
        $reimbursement = Reimbursement::create([
            'employee_id' => $this->employee->id,
            'claim_number' => 'CLM-AUTH-' . uniqid(),
            'title' => 'Biaya Operasional Lapangan',
            'description' => 'Bensin dinas luar kota',
            'amount' => 350000.00,
            'status' => 'PENDING',
        ]);

        $restrictedReimbRoles = ['employee', 'intern', 'payroll_officer', 'procurement_officer'];
        foreach ($restrictedReimbRoles as $roleCode) {
            $user = $this->getOrCreateRoleUser($roleCode);
            $response = $this->actingAs($user)->post("/reimbursements/{$reimbursement->id}/approve");
            $response->assertStatus(403, "Role [{$roleCode}] should receive 403 when approving reimbursements.");
        }

        // 3. Attendance Correction Approval (forbidden for employee, intern, finance_officer, procurement_officer)
        $shift = Shift::firstOrCreate(
            ['code' => 'SHIFT-RBAC'],
            ['name' => 'Shift RBAC', 'start_time' => '08:00', 'end_time' => '17:00', 'late_tolerance_minutes' => 15, 'is_active' => true]
        );
        $record = AttendanceRecord::create([
            'employee_id' => $this->employee->id,
            'shift_id' => $shift->id,
            'date' => now()->subDay()->toDateString(),
            'clock_in' => '08:30:00',
            'status' => 'LATE',
            'late_minutes' => 30,
        ]);
        $correction = AttendanceCorrection::create([
            'attendance_record_id' => $record->id,
            'employee_id' => $this->employee->id,
            'proposed_clock_in' => '08:00:00',
            'reason' => 'Fingerprint error pada pintu barat',
            'status' => 'PENDING',
        ]);

        $restrictedCorrectionRoles = ['employee', 'intern', 'finance_officer', 'procurement_officer'];
        foreach ($restrictedCorrectionRoles as $roleCode) {
            $user = $this->getOrCreateRoleUser($roleCode);
            $response = $this->actingAs($user)->post("/attendance/corrections/{$correction->id}/approve");
            $response->assertStatus(403, "Role [{$roleCode}] should receive 403 when approving attendance corrections.");
        }
    }
}
