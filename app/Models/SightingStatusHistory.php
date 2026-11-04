<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SightingStatusHistory extends Model
{
    protected $table = 'sighting_status_history';

    protected $fillable = ['sighting_id', 'from_status', 'to_status', 'notes', 'performed_by'];

    public function sighting(): BelongsTo { return $this->belongsTo(Sighting::class); }
    public function performedBy(): BelongsTo { return $this->belongsTo(User::class, 'performed_by'); }
}
