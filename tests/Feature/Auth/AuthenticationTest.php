<?php

namespace Tests\Feature\Auth;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_owner_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create(['role' => 'owner']);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('owner.dashboard'));
    }

    public function test_employee_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        Employee::create([
            'user_id' => $user->id,
            'employee_code' => 'EMP-TEST',
            'position' => 'Staff',
            'base_salary' => 0,
            'salary_type' => 'monthly',
            'status' => 'active',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('employee.dashboard'));
    }

    public function test_authenticated_employee_visiting_login_redirects_to_employee_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'employee']);

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect(route('employee.dashboard'));
    }

    public function test_authenticated_owner_visiting_login_redirects_to_owner_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'owner']);

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect(route('owner.dashboard'));
    }

    public function test_employee_dashboard_does_not_redirect_to_login_when_employee_profile_exists(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        Employee::create([
            'user_id' => $user->id,
            'employee_code' => 'EMP-DASH',
            'position' => 'Staff',
            'base_salary' => 0,
            'salary_type' => 'monthly',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get('/employee/dashboard');

        $response->assertOk();
    }

    public function test_employee_dashboard_renders_instead_of_redirecting_back_when_profile_is_missing(): void
    {
        $user = User::factory()->create(['role' => 'employee']);

        $response = $this->withHeader('referer', route('login'))
            ->actingAs($user)
            ->get('/employee/dashboard');

        $response->assertOk();
    }

    public function test_employee_cannot_access_owner_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'employee']);

        $response = $this->actingAs($user)->get('/owner/dashboard');

        $response->assertForbidden();
    }

    public function test_owner_cannot_access_employee_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'owner']);

        $response = $this->actingAs($user)->get('/employee/dashboard');

        $response->assertForbidden();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }
}
