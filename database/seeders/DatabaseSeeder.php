<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Owner
        User::updateOrCreate(['email' => 'owner@kantorsayur.com'], [
            'name' => 'Owner Kantor Sayur',
            'password' => Hash::make('123'),
            'role' => 'owner',
            'phone' => '081234567890',
            'address' => 'Jl. Kebon Sayur No. 123, Jakarta',
            'hire_date' => now(),
            'email_verified_at' => now(),
        ]);

        // Create Employee
        User::updateOrCreate(['email' => 'ahmad@kantorsayur.com'], [
            'name' => 'Ahmad Karyawan',
            'password' => Hash::make('123'),
            'role' => 'employee',
            'phone' => '081298765432',
            'address' => 'Jl. Karyawan No. 45, Jakarta',
            'hire_date' => now(),
            'email_verified_at' => now(),
        ]);

        User::updateOrCreate(['email' => 'siti@kantorsayur.com'], [
            'name' => 'Siti Karyawati',
            'password' => Hash::make('password123'),
            'role' => 'employee',
            'phone' => '081287654321',
            'address' => 'Jl. Melati No. 67, Jakarta',
            'hire_date' => now(),
            'email_verified_at' => now(),
        ]);

        User::updateOrCreate(['email' => 'budi@kantorsayur.com'], [
            'name' => 'Budi Santoso',
            'password' => Hash::make('123'),
            'role' => 'employee',
            'phone' => '081276543210',
            'address' => 'Jl. Mawar No. 89, Jakarta',
            'hire_date' => now(),
            'email_verified_at' => now(),
        ]);

        $this->call(EmployeeSeeder::class);
        $this->call(ShiftAndRiziqAttendanceSeeder::class);
    }
}
