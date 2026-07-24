<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissingPersonTimeline extends Model
{
    protected $table = 'missing_person_timeline';

    protected $fillable = [
        'missing_person_id',
        'event_type',
        'description',
        'recorded_by',
    ];

    public function missingPerson(): BelongsTo
    {
        return $this->belongsTo(MissingPerson::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
