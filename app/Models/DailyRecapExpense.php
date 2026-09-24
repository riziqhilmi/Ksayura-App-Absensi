<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyRecapExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_recap_id',
        'daily_recap_expense_session_id',
        'created_by_employee_id',
        'name',
        'expense_time',
        'amount',
        'sort_order',
    ];

    protected $casts = [
        'amount' => 'integer',
        'sort_order' => 'integer',
    ];

    public function dailyRecap()
    {
        return $this->belongsTo(DailyRecap::class);
    }

    public function expenseSession()
    {
        return $this->belongsTo(DailyRecapExpenseSession::class, 'daily_recap_expense_session_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'created_by_employee_id');
    }
}
