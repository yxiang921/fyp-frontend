<?php

namespace App\Models;

use App\Enums\MissingPersonStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class MissingPerson extends Model
{
    protected $table = 'missing_persons';

    protected $fillable = [
        'case_number',
        'full_name',
        'gender',
        'date_of_birth',
        'national_id',
        'last_seen_location',
        'last_seen_date',
        'status',
        'description',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'last_seen_date' => 'date',
            'status' => MissingPersonStatus::class,
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(MissingPersonPhoto::class);
    }

    public function primaryPhoto()
    {
        return $this->hasOne(MissingPersonPhoto::class)->where('is_primary', true);
    }

    public function timeline(): HasMany
    {
        return $this->hasMany(MissingPersonTimeline::class);
    }

    public function detections(): MorphMany
    {
        return $this->morphMany(Detection::class, 'reference');
    }

    public function alerts(): MorphMany
    {
        return $this->morphMany(Alert::class, 'reference');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', MissingPersonStatus::Active);
    }

    public function scopeByStatus($query, MissingPersonStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('full_name', 'ilike', "%{$search}%")
              ->orWhere('case_number', 'ilike', "%{$search}%")
              ->orWhere('national_id', 'ilike', "%{$search}%")
              ->orWhere('last_seen_location', 'ilike', "%{$search}%");
        });
    }
}
