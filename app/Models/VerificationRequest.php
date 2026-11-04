<?php

namespace App\Models;

use App\Enums\VerificationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationRequest extends Model
{
    protected $table = 'verification_requests';

    protected $fillable = [
        'request_id',
        'detection_id',
        'alert_id',
        'requested_by',
        'reviewed_by',
        'status',
        'jpn_notes',
        'police_notes',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => VerificationStatus::class,
            'verified_at' => 'datetime',
        ];
    }

    public function detection(): BelongsTo
    {
        return $this->belongsTo(Detection::class);
    }

    public function alert(): BelongsTo
    {
        return $this->belongsTo(Alert::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', VerificationStatus::Pending);
    }

    public function scopeByStatus($query, VerificationStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('request_id', 'ilike', "%{$search}%");
        });
    }
}
