<?php

namespace App\Models;

use App\Enums\MissingPersonStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MissingPerson extends Model
{
    protected $table = 'missing_persons';

    protected $fillable = [
        'case_number',
        'full_name',
        'gender',
        'date_of_birth',
        'national_id',
        'ethnicity',
        'nationality',
        'height_cm',
        'weight_kg',
        'hair_type',
        'hair_color',
        'eye_color',
        'clothing_description',
        'identifying_features',
        'last_seen_location',
        'last_seen_latitude',
        'last_seen_longitude',
        'last_seen_date',
        'status',
        'description',
        'created_by',
        'consented_at',
        'duplicate_review',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'last_seen_date' => 'date',
            'last_seen_latitude' => 'decimal:7',
            'last_seen_longitude' => 'decimal:7',
            'height_cm' => 'integer',
            'weight_kg' => 'decimal:2',
            'status' => MissingPersonStatus::class,
            'duplicate_review' => 'array',
            'consented_at' => 'datetime',
        ];
    }

    protected $hidden = ['national_id', 'national_id_encrypted', 'national_id_hash', 'duplicate_review'];

    public static function nationalIdHash(string $value): string
    {
        return hash_hmac('sha256', preg_replace('/[^A-Z0-9]/', '', strtoupper($value)), config('app.key'));
    }

    public function getNationalIdAttribute($value): ?string
    {
        return isset($this->attributes['national_id_encrypted'])
            ? \Illuminate\Support\Facades\Crypt::decryptString($this->attributes['national_id_encrypted']) : $value;
    }

    public function setNationalIdAttribute($value): void
    {
        $this->attributes['national_id'] = null;
        $this->attributes['national_id_encrypted'] = $value ? \Illuminate\Support\Facades\Crypt::encryptString($value) : null;
        $this->attributes['national_id_hash'] = $value ? self::nationalIdHash($value) : null;
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

    public function sightings(): HasMany
    {
        return $this->hasMany(Sighting::class);
    }

    public function detections(): HasMany
    {
        return $this->hasMany(Detection::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
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
                ->orWhere('national_id_hash', self::nationalIdHash($search))
                ->orWhere('last_seen_location', 'ilike', "%{$search}%");
        });
    }
}
