<?php

namespace App\Models;

use App\Enums\AlertStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alert extends Model
{
    protected $fillable = [
        'alert_id',
        'detection_id',
        'sighting_id',
        'missing_person_id',
        'similarity_score',
        'camera_id',
        'patrol_area_id',
        'status',
        'assigned_to',
        'description',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'similarity_score' => 'decimal:2',
            'status' => AlertStatus::class,
            'resolved_at' => 'datetime',
        ];
    }

    public function detection(): BelongsTo
    {
        return $this->belongsTo(Detection::class);
    }

    public function sighting(): BelongsTo
    {
        return $this->belongsTo(Sighting::class);
    }

    public function missingPerson(): BelongsTo
    {
        return $this->belongsTo(MissingPerson::class);
    }

    public function camera(): BelongsTo
    {
        return $this->belongsTo(Camera::class);
    }

    public function patrolArea(): BelongsTo
    {
        return $this->belongsTo(PatrolArea::class);
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
        return $this->missingPerson?->full_name ?? 'N/A';
    }

}
