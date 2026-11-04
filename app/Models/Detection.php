<?php

namespace App\Models;

use App\Enums\DetectionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Detection extends Model
{
    protected $fillable = [
        'detection_id',
        'camera_id',
        'captured_image',
        'missing_person_id',
        'similarity_score',
        'status',
        'detected_at',
        'reviewed_by',
        'review_notes',
    ];

    protected function casts(): array
    {
        return [
            'similarity_score' => 'decimal:2',
            'status' => DetectionStatus::class,
            'detected_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Detection $detection) {
            if ($detection->isDirty('status') && $detection->status !== DetectionStatus::New) {
                $detection->reviewed_at ??= now();
                if ($detection->status === DetectionStatus::Confirmed) $detection->confirmed_at ??= now();
            }
        });
    }

    public function camera(): BelongsTo
    {
        return $this->belongsTo(Camera::class);
    }

    public function missingPerson(): BelongsTo
    {
        return $this->belongsTo(MissingPerson::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function verificationRequests(): HasMany
    {
        return $this->hasMany(VerificationRequest::class);
    }

    // Scopes
    public function scopeNew($query)
    {
        return $query->where('status', DetectionStatus::New);
    }

    public function scopeByStatus($query, DetectionStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByCamera($query, int $cameraId)
    {
        return $query->where('camera_id', $cameraId);
    }

    public function scopeHighSimilarity($query, float $threshold = 80.0)
    {
        return $query->where('similarity_score', '>=', $threshold);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('detection_id', 'ilike', "%{$search}%");
        });
    }

    // Helpers
    public function referenceName(): string
    {
        return $this->missingPerson?->full_name ?? 'Unknown';
    }

    public function isHighConfidence(): bool
    {
        return $this->similarity_score >= 80.0;
    }
}
