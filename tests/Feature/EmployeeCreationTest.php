<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_code_uses_largest_existing_number_instead_of_count(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $firstUser = User::factory()->create(['role' => 'employee']);
        $latestUser = User::factory()->create(['role' => 'employee']);

        Employee::create([
            'user_id' => $firstUser->id,
            'employee_code' => 'EMP0001',
            'position' => 'Staff',
            'base_salary' => 100000,
            'salary_type' => 'daily',
            'daily_rate' => 100000,
            'status' => 'active',
        ]);

        Employee::create([
            'user_id' => $latestUser->id,
            'employee_code' => 'EMP0011',
            'position' => 'Staff',
            'base_salary' => 100000,
            'salary_type' => 'daily',
            'daily_rate' => 100000,
            'status' => 'active',
        ]);

        $response = $this->actingAs($owner)->post(route('owner.employees.store'), [
            'name' => 'Karyawan Baru',
            'email' => 'karyawan-baru@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'phone' => '08123456789',
            'address' => 'Jl. Testing',
            'position' => 'Kasir',
            'daily_rate' => 125000,
            'hourly_rate' => 15000,
            'hire_date' => '2026-09-18',
        ]);

        $response->assertRedirect(route('owner.employees.index'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'karyawan-baru@example.com',
            'role' => 'employee',
        ]);

        $this->assertDatabaseHas('employees', [
            'employee_code' => 'EMP0012',
            'position' => 'Kasir',
            'status' => 'active',
        ]);

        $this->assertDatabaseMissing('employees', [
            'employee_code' => 'EMP0003',
        ]);
    }
}
