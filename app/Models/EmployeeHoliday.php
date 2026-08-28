<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeHoliday extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'reason',
        'type',
        'is_paid',
        'status',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'is_paid' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function getTypeLabel()
    {
        $types = [
            'annual' => 'Cuti Tahunan',
            'sick' => 'Cuti Sakit',
            'personal' => 'Cuti Pribadi',
            'company' => 'Libur Perusahaan',
            'other' => 'Lainnya',
        ];
        return $types[$this->type] ?? $this->type;
    }

    public function getStatusLabel()
    {
        $statuses = [
            'scheduled' => 'Terjadwal',
            'taken' => 'Diambil',
            'cancelled' => 'Dibatalkan',
        ];
        return $statuses[$this->status] ?? $this->status;
    }
}