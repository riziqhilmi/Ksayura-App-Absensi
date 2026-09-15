<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyRecap extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'recap_date',
        'total_expense_amount',
        'remaining_cash_amount',
        'total_qris_amount',
        'capital_amount',
        'expense_opened_at',
        'expense_closed_at',
    ];

    protected $casts = [
        'recap_date' => 'date',
        'total_expense_amount' => 'integer',
        'remaining_cash_amount' => 'integer',
        'total_qris_amount' => 'integer',
        'capital_amount' => 'integer',
        'expense_opened_at' => 'datetime',
        'expense_closed_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function expenses()
    {
        return $this->hasMany(DailyRecapExpense::class)->orderBy('sort_order')->orderBy('id');
    }

    public function expenseSessions()
    {
        return $this->hasMany(DailyRecapExpenseSession::class);
    }

    public function qrisTransactions()
    {
        return $this->hasMany(DailyRecapQrisTransaction::class)->orderBy('sort_order')->orderBy('id');
    }

    public function refreshQrisTotals(): void
    {
        $this->total_qris_amount = (int) $this->qrisTransactions()->sum('amount');
        $this->save();
    }

    public function refreshExpenseTotals(): void
    {
        $this->total_expense_amount = (int) $this->expenses()->sum('amount');
        $this->save();
    }
}
