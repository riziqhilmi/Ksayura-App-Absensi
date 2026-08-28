<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'break_start',
        'break_end',
        'grace_period',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'break_start' => 'datetime',
        'break_end' => 'datetime',
    ];

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function isActive()
    {
        return $this->status === 'active';
    }
}