<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_code',
        'position',
        'base_salary',
        'salary_type',
        'daily_rate',
        'hourly_rate',
        'status',
        'notes',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function salaryHistory()
    {
        return $this->hasMany(SalaryHistory::class);
    }

    public function getFullNameAttribute()
    {
        return $this->user->name;
    }

    public function getEmailAttribute()
    {
        return $this->user->email;
    }

    public function getPhoneAttribute()
    {
        return $this->user->phone;
    }

    public function getAddressAttribute()
    {
        return $this->user->address;
    }

    // Helper untuk format gaji harian
    public function getDailyRateFormattedAttribute()
    {
        return 'Rp ' . number_format($this->daily_rate, 0, ',', '.');
    }

    // Helper untuk mendapatkan tipe gaji
    public function getSalaryTypeLabelAttribute()
    {
        $types = [
            'daily' => 'Harian',
            'weekly' => 'Mingguan',
            'monthly' => 'Bulanan',
        ];
        return $types[$this->salary_type] ?? $this->salary_type;
    }
}