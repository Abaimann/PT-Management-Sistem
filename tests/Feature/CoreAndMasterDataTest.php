<?php

namespace Tests\Feature;

use App\Models\Core\Department;
use App\Models\Core\Position;
use App\Models\Core\Role;
use App\Models\Core\User;
use App\Models\HRIS\Employee;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CoreAndMasterDataTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;

    protected Department $department;

    protected Position $position;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['code' => 'super_admin'], ['name' => 'Super Admin']);

        $this->adminUser = User::create([
            'name' => 'Admin Test '.uniqid(),
            'email' => 'adm_'.uniqid().'@example.test',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $this->adminUser->roles()->sync([$role->id]);

        $this->department = Department::create([
            'code' => 'TEST-'.strtoupper(substr(uniqid(), 0, 4)),
            'name' => 'Departemen Testing',
            'cost_center' => 'CC-TEST',
            'is_active' => true,
        ]);

        $this->position = Position::create([
            'department_id' => $this->department->id,
            'name' => 'Software Engineer Test',
            'grade_level' => 'Senior',
            'is_active' => true,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/departments')->assertRedirect('/login');
        $this->get('/positions')->assertRedirect('/login');
        $this->get('/employees')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $response->assertSee('Tingkat Kehadiran');
        $response->assertSee('Total Karyawan');
    }

    public function test_can_list_and_create_department(): void
    {
        // 1. Index
        $response = $this->actingAs($this->adminUser)->get('/departments');
        $response->assertStatus(200);
        $response->assertSee('Master Departemen');
        $response->assertSee($this->department->name);

        // 2. Create View
        $this->actingAs($this->adminUser)->get('/departments/create')->assertStatus(200);

        // 3. Store
        $newDeptCode = 'DPT-'.strtoupper(substr(uniqid(), 0, 3));
        $storeResponse = $this->actingAs($this->adminUser)->post('/departments', [
            'code' => $newDeptCode,
            'name' => 'Departemen Operasional Baru',
            'cost_center' => 'CC-OPS-99',
            'is_active' => true,
        ]);

        $storeResponse->assertRedirect('/departments');
        $this->assertDatabaseHas('departments', [
            'code' => $newDeptCode,
            'name' => 'Departemen Operasional Baru',
        ]);
    }

    public function test_can_update_department(): void
    {
        $dept = Department::create([
            'code' => 'UPD-'.strtoupper(substr(uniqid(), 0, 3)),
            'name' => 'Departemen Before Update',
            'cost_center' => 'CC-B4',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->put("/departments/{$dept->id}", [
            'code' => $dept->code,
            'name' => 'Departemen After Update',
            'cost_center' => 'CC-AFTER',
            'is_active' => true,
        ]);

        $response->assertRedirect('/departments');
        $this->assertDatabaseHas('departments', [
            'id' => $dept->id,
            'name' => 'Departemen After Update',
            'cost_center' => 'CC-AFTER',
        ]);
    }

    public function test_can_list_and_create_position(): void
    {
        // 1. Index with search filter to ensure finding the test position
        $response = $this->actingAs($this->adminUser)->get('/positions?search=Software+Engineer+Test');
        $response->assertStatus(200);
        $response->assertSee('Master Jabatan');
        $response->assertSee($this->position->name);

        // 2. Create View
        $this->actingAs($this->adminUser)->get('/positions/create')->assertStatus(200);

        // 3. Store
        $storeResponse = $this->actingAs($this->adminUser)->post('/positions', [
            'department_id' => $this->department->id,
            'name' => 'DevOps Specialist '.uniqid(),
            'grade_level' => 'Senior Staff',
            'is_active' => true,
        ]);

        $storeResponse->assertRedirect('/positions');
    }

    public function test_can_create_view_and_update_employee(): void
    {
        // 1. Index
        $this->actingAs($this->adminUser)->get('/employees')->assertStatus(200);

        // 2. Create View
        $this->actingAs($this->adminUser)->get('/employees/create')->assertStatus(200);

        // 3. Store Employee
        $nik = 'EMP-'.rand(10000, 99999);
        $userEmail = 'emp_'.uniqid().'@example.test';

        $storeResponse = $this->actingAs($this->adminUser)->post('/employees', [
            'nik' => $nik,
            'full_name' => 'Budi Pratama Testing',
            'gender' => 'MALE',
            'birth_place' => 'Jakarta',
            'birth_date' => '1995-05-15',
            'phone' => '+62 811-2222-3333',
            'address' => 'Jl. Merdeka No. 45, Jakarta',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_status' => 'PERMANENT',
            'join_date' => '2025-01-10',
            'is_active' => true,
            'create_user_account' => true,
            'email' => $userEmail,
            'password' => 'SecurePass123!',
        ]);

        $employee = Employee::where('nik', $nik)->first();
        $this->assertNotNull($employee);
        $storeResponse->assertRedirect("/employees/{$employee->id}");

        // Assert user was also created
        $this->assertDatabaseHas('users', ['email' => $userEmail]);

        // 4. Show Employee Dossier
        $showResponse = $this->actingAs($this->adminUser)->get("/employees/{$employee->id}");
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Budi Pratama Testing');
        $showResponse->assertSee($nik);

        // 5. Update Employee
        $updateResponse = $this->actingAs($this->adminUser)->put("/employees/{$employee->id}", [
            'nik' => $nik,
            'full_name' => 'Budi Pratama S.Kom',
            'gender' => 'MALE',
            'birth_place' => 'Jakarta',
            'birth_date' => '1995-05-15',
            'phone' => '+62 811-9999-8888',
            'address' => 'Jl. Thamrin No. 10',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employment_status' => 'PERMANENT',
            'join_date' => '2025-01-10',
            'is_active' => true,
        ]);

        $updateResponse->assertRedirect("/employees/{$employee->id}");
        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'full_name' => 'Budi Pratama S.Kom',
            'phone' => '+62 811-9999-8888',
        ]);
    }
}
