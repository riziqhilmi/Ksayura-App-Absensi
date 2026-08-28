<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'period',
        'start_date',
        'end_date',
        'base_salary',
        'overtime_hours',
        'overtime_pay',
        'attendance_bonus',
        'performance_bonus',
        'deductions',
        'total_salary',
        'working_days',
        'present_days',
        'late_days',
        'absent_days',
        'leave_days',
        'status',
        'paid_date',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'paid_date' => 'date',
        'base_salary' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'attendance_bonus' => 'decimal:2',
        'performance_bonus' => 'decimal:2',
        'deductions' => 'decimal:2',
        'total_salary' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeCalculated($query)
    {
        return $query->where('status', 'calculated');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isCalculated()
    {
        return $this->status === 'calculated';
    }

    public function isPaid()
    {
        return $this->status === 'paid';
    }
}