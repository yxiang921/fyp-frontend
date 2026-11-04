<?php

namespace App\Models;

use App\Enums\NotificationType;
use App\Jobs\SendExpoNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => NotificationType::class,
            'data' => 'array',
            'read_at' => 'datetime',
        ];
    }

    /**
     * Fire a mobile push whenever a notification is created.
     */
    protected static function booted(): void
    {
        static::created(function (Notification $notification) {
            try {
                SendExpoNotification::dispatch(
                    $notification->user_id,
                    $notification->title,
                    $notification->message,
                    is_array($notification->data) ? $notification->data : [],
                );
            } catch (\Throwable $e) {
                Log::warning('Notification push hook failed: ' . $e->getMessage());
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeByType($query, NotificationType $type)
    {
        return $query->where('type', $type);
    }

    // Helpers
    public function markAsRead(): void
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }

    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }
}
