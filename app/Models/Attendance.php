<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'shift_id',
        'date',
        'check_in_time',
        'check_out_time',
        'check_in_location',
        'check_out_location',
        'latitude_in',
        'longitude_in',
        'latitude_out',
        'longitude_out',
        'status',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    // Scope untuk hari ini
    public function scopeToday($query)
    {
        return $query->whereDate('date', today());
    }

    // Scope untuk bulan ini
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('date', now()->month)
                     ->whereYear('date', now()->year);
    }

    // Cek apakah sudah check in
    public function isCheckedIn()
    {
        return !is_null($this->check_in_time) && is_null($this->check_out_time);
    }

    // Cek apakah sudah check out
    public function isCheckedOut()
    {
        return !is_null($this->check_out_time);
    }
}