<?php

namespace Tests\Feature;

use App\Models\Core\Role;
use App\Models\Core\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AuthAndRbacTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['code' => 'super_admin'], ['name' => 'Super Admin']);
        Role::firstOrCreate(['code' => 'hr_admin'], ['name' => 'HR Admin']);
        Role::firstOrCreate(['code' => 'employee'], ['name' => 'Karyawan']);
    }

    protected function createUser(array $attributes = []): User
    {
        return User::create(array_merge([
            'name' => 'Test User '.uniqid(),
            'email' => 'user_'.uniqid().'@example.test',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ], $attributes));
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('PT MANAGEMENT SISTEM');
        $response->assertSee('Selamat Datang');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = $this->createUser([
            'email' => 'auth_test_'.uniqid().'@example.test',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/dashboard');
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $user = $this->createUser([
            'email' => 'wrong_pass_'.uniqid().'@example.test',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_inactive_users_cannot_authenticate(): void
    {
        $user = $this->createUser([
            'email' => 'inactive_'.uniqid().'@example.test',
            'password' => Hash::make('password123'),
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_authenticated_users_can_logout(): void
    {
        $user = $this->createUser(['is_active' => true]);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }

    public function test_user_can_view_profile_and_update_details(): void
    {
        $user = $this->createUser([
            'name' => 'Initial Name',
            'email' => 'initial_'.uniqid().'@example.test',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/profile');
        $response->assertStatus(200);
        $response->assertSee('Initial Name');

        $updatedEmail = 'updated_'.uniqid().'@example.test';
        $updateResponse = $this->actingAs($user)->put('/profile', [
            'name' => 'Updated Name',
            'email' => $updatedEmail,
        ]);

        $updateResponse->assertRedirect('/profile');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => $updatedEmail,
        ]);
    }

    public function test_user_can_update_password(): void
    {
        $user = $this->createUser([
            'password' => Hash::make('old-password123'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->put('/profile/password', [
            'current_password' => 'old-password123',
            'password' => 'new-password456',
            'password_confirmation' => 'new-password456',
        ]);

        $response->assertRedirect('/profile');
        $this->assertTrue(Hash::check('new-password456', $user->fresh()->password));
    }

    public function test_bootstrap_admin_artisan_command_creates_super_admin(): void
    {
        $testEmail = 'cli_superadmin_'.time().'@example.test';

        $this->artisan('erp:bootstrap-admin', [
            '--name' => 'CLI Admin',
            '--email' => $testEmail,
            '--password' => 'supersecret123',
        ])->assertSuccessful();

        $user = User::where('email', $testEmail)->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('super_admin'));
    }

    public function test_check_role_middleware_allows_authorized_role(): void
    {
        Route::get('/test-role-hr', function () {
            return 'OK';
        })->middleware(['web', 'auth', 'role:hr_admin']);

        $hrRole = Role::where('code', 'hr_admin')->first();
        $hrUser = $this->createUser(['is_active' => true]);
        $hrUser->roles()->attach($hrRole->id);

        $response = $this->actingAs($hrUser)->get('/test-role-hr');
        $response->assertStatus(200);
        $response->assertSee('OK');
    }

    public function test_check_role_middleware_blocks_unauthorized_role(): void
    {
        Route::get('/test-role-restricted', function () {
            return 'OK';
        })->middleware(['web', 'auth', 'role:hr_admin']);

        $empRole = Role::where('code', 'employee')->first();
        $empUser = $this->createUser(['is_active' => true]);
        $empUser->roles()->attach($empRole->id);

        $response = $this->actingAs($empUser)->get('/test-role-restricted');
        $response->assertStatus(403);
    }

    public function test_check_role_middleware_super_admin_bypasses_restriction(): void
    {
        Route::get('/test-role-bypass', function () {
            return 'OK';
        })->middleware(['web', 'auth', 'role:finance_officer']);

        $superRole = Role::where('code', 'super_admin')->first();
        $admin = $this->createUser(['is_active' => true]);
        $admin->roles()->attach($superRole->id);

        $response = $this->actingAs($admin)->get('/test-role-bypass');
        $response->assertStatus(200);
        $response->assertSee('OK');
    }

    public function test_ensure_user_is_active_middleware_logs_out_deactivated_user(): void
    {
        Route::get('/test-active-guard', function () {
            return 'OK';
        })->middleware(['web', 'auth', 'active']);

        $user = $this->createUser(['is_active' => false]);

        $response = $this->actingAs($user)->get('/test-active-guard');
        $this->assertGuest();
        $response->assertRedirect('/login');
    }
}
