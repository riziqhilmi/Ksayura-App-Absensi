<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyRecapExpenseSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_recap_id',
        'recap_date',
        'opened_by_employee_id',
        'closed_by_employee_id',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'recap_date' => 'date',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function dailyRecap()
    {
        return $this->belongsTo(DailyRecap::class);
    }

    public function openedBy()
    {
        return $this->belongsTo(Employee::class, 'opened_by_employee_id');
    }

    public function closedBy()
    {
        return $this->belongsTo(Employee::class, 'closed_by_employee_id');
    }

    public function expenses()
    {
        return $this->hasMany(DailyRecapExpense::class)->orderBy('sort_order')->orderBy('id');
    }
}
