<?php

namespace App\Models;

use App\Enums\AlertPriority;
use App\Enums\AlertStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Alert extends Model
{
    protected $fillable = [
        'alert_id',
        'detection_id',
        'alert_type',
        'reference_type',
        'reference_id',
        'similarity_score',
        'camera_id',
        'status',
        'priority',
        'assigned_to',
        'description',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'similarity_score' => 'decimal:2',
            'status' => AlertStatus::class,
            'priority' => AlertPriority::class,
            'resolved_at' => 'datetime',
        ];
    }

    public function detection(): BelongsTo
    {
        return $this->belongsTo(Detection::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function camera(): BelongsTo
    {
        return $this->belongsTo(Camera::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function timeline(): HasMany
    {
        return $this->hasMany(AlertTimeline::class);
    }

    public function verificationRequests(): HasMany
    {
        return $this->hasMany(VerificationRequest::class);
    }

    // Scopes
    public function scopeNew($query)
    {
        return $query->where('status', AlertStatus::New);
    }

    public function scopeByStatus($query, AlertStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, AlertPriority $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('alert_type', $type);
    }

    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('alert_id', 'ilike', "%{$search}%")
              ->orWhere('description', 'ilike', "%{$search}%");
        });
    }

    // Helpers
    public function referenceName(): string
    {
        return $this->reference?->full_name ?? 'N/A';
    }

    public function isHighPriority(): bool
    {
        return in_array($this->priority, [AlertPriority::High, AlertPriority::Critical]);
    }
}
