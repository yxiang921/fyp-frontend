<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SightingEvidence extends Model
{
    protected $table = 'sighting_evidence';
    protected $guarded = ['id'];
    protected $hidden = ['path'];
    protected function casts(): array { return ['expires_at' => 'datetime', 'purged_at' => 'datetime']; }
    public function sighting() { return $this->belongsTo(Sighting::class); }
}
