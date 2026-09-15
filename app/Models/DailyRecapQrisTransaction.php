<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyRecapQrisTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_recap_id',
        'amount',
        'evidence_path',
        'evidence_original_name',
        'evidence_mime_type',
        'evidence_size',
        'evidence_uploaded_at',
        'evidence_expires_at',
        'evidence_deleted_at',
        'sort_order',
    ];

    protected $casts = [
        'amount' => 'integer',
        'evidence_uploaded_at' => 'datetime',
        'evidence_expires_at' => 'datetime',
        'evidence_deleted_at' => 'datetime',
        'sort_order' => 'integer',
    ];

    public function dailyRecap()
    {
        return $this->belongsTo(DailyRecap::class);
    }

    public function hasEvidence(): bool
    {
        return filled($this->evidence_path)
            && is_null($this->evidence_deleted_at)
            && (
                is_null($this->evidence_expires_at)
                || $this->evidence_expires_at->isFuture()
            );
    }
}
