<?php

namespace App\Models;

use App\Enums\SightingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Sighting extends Model
{
    protected $fillable = [
        'missing_person_id',
        'reporter_name',
        'reporter_phone',
        'location',
        'latitude',
        'longitude',
        'sighted_at',
        'notes',
        'status',
        'consented_at', 'reviewed_at', 'privacy_purged_at',
    ];

    protected function casts(): array
    {
        return [
            'sighted_at' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'status' => SightingStatus::class,
            'reporter_name' => 'encrypted',
            'reporter_phone' => 'encrypted',
            'notes' => 'encrypted',
            'consented_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'privacy_purged_at' => 'datetime',
        ];
    }

    public function evidence() { return $this->hasMany(SightingEvidence::class); }

    public function alert(): HasOne { return $this->hasOne(Alert::class); }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(SightingStatusHistory::class)->latest();
    }

    protected static function booted(): void
    {
        static::updating(function (Sighting $sighting) {
            if ($sighting->isDirty('status') && $sighting->status !== SightingStatus::New && !$sighting->reviewed_at) {
                $sighting->reviewed_at = now();
            }
        });
    }

    public function missingPerson(): BelongsTo
    {
        return $this->belongsTo(MissingPerson::class);
    }
}
