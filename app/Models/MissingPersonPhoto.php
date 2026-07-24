<?php

namespace App\Models;

use App\Enums\PhotoType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissingPersonPhoto extends Model
{
    protected $fillable = [
        'missing_person_id',
        'path',
        'type',
        'is_primary',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => PhotoType::class,
            'is_primary' => 'boolean',
        ];
    }

    public function missingPerson(): BelongsTo
    {
        return $this->belongsTo(MissingPerson::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function url(): string
    {
        return asset('storage/' . $this->path);
    }
}
