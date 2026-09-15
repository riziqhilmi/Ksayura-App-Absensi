<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyRecapShiftSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'recap_date',
        'capital_amount',
        'remaining_cash_amount',
        'updated_by_employee_id',
    ];

    protected $casts = [
        'recap_date' => 'date',
        'capital_amount' => 'integer',
        'remaining_cash_amount' => 'integer',
    ];

    public function updatedBy()
    {
        return $this->belongsTo(Employee::class, 'updated_by_employee_id');
    }
}
