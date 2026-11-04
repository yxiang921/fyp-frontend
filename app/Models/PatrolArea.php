<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PatrolArea extends Model
{
    protected $fillable = ['name', 'code', 'description', 'center_latitude', 'center_longitude', 'is_active'];

    protected function casts(): array
    {
        return ['center_latitude' => 'decimal:7', 'center_longitude' => 'decimal:7', 'is_active' => 'boolean'];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function cameras(): HasMany
    {
        return $this->hasMany(Camera::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }
}
