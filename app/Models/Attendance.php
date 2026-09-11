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
        'gps_accuracy_in',
        'gps_accuracy_out',
        'location_recorded_at_in',
        'location_recorded_at_out',
        'device_fingerprint_in',
        'device_fingerprint_out',
        'timezone_in',
        'timezone_out',
        'client_time_offset_in',
        'client_time_offset_out',
        'fraud_flags',
        'status',
        'notes',
        'is_auto_checkout',
        'auto_checkout_at',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'location_recorded_at_in' => 'datetime',
        'location_recorded_at_out' => 'datetime',
        'fraud_flags' => 'array',
        'is_auto_checkout' => 'boolean',
        'auto_checkout_at' => 'datetime',
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
