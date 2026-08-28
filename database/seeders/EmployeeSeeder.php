<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('role', 'employee')->get();
        
        foreach ($users as $user) {
            Employee::updateOrCreate(['user_id' => $user->id], [
                'employee_code' => 'EMP' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
                'position' => 'Staff',
                'base_salary' => rand(4000000, 8000000),
                'salary_type' => 'monthly',
                'daily_rate' => rand(150000, 300000),
                'hourly_rate' => rand(20000, 40000),
                'status' => 'active',
            ]);
        }
    }
}
