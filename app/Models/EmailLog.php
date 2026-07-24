<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailLog extends Model
{
    protected $table = 'email_logs';

    protected $fillable = [
        'email_log_id',
        'recipient_email',
        'recipient_name',
        'subject',
        'mailable_class',
        'alert_id',
        'status',
        'error_message',
        'sent_at',
        'trigger_type',
        'triggered_by',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function alert(): BelongsTo
    {
        return $this->belongsTo(Alert::class);
    }

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByTriggerType($query, string $type)
    {
        return $query->where('trigger_type', $type);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('recipient_email', 'ilike', "%{$search}%")
              ->orWhere('recipient_name', 'ilike', "%{$search}%")
              ->orWhere('subject', 'ilike', "%{$search}%")
              ->orWhere('email_log_id', 'ilike', "%{$search}%");
        });
    }

    // Static helper to create a log entry with auto-generated ID
    public static function log(array $data): self
    {
        $year = date('Y');
        $latest = self::where('email_log_id', 'like', "EML-{$year}-%")
            ->orderBy('email_log_id', 'desc')
            ->first();
        if ($latest) {
            $lastNum = (int) substr($latest->email_log_id, -4);
            $data['email_log_id'] = "EML-{$year}-" . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $data['email_log_id'] = "EML-{$year}-0001";
        }

        return self::create($data);
    }

    // Helpers
    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isQueued(): bool
    {
        return $this->status === 'queued';
    }

    public function statusBadge(): string
    {
        return match ($this->status) {
            'sent' => 'badge-green',
            'queued' => 'badge-blue',
            'failed' => 'badge-red',
            default => 'badge-gray',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'sent' => 'Sent',
            'queued' => 'Queued',
            'failed' => 'Failed',
            default => ucfirst($this->status),
        };
    }

    public function triggerTypeLabel(): string
    {
        return match ($this->trigger_type) {
            'auto' => 'Auto',
            'manual' => 'Manual',
            default => ucfirst($this->trigger_type),
        };
    }

    public function mailableLabel(): string
    {
        return match ($this->mailable_class) {
            'App\\Mail\\NewAlertNotification' => 'New Alert',
            'App\\Mail\\PatrolAlertNotification' => 'Patrol Dispatch',
            default => class_basename($this->mailable_class),
        };
    }
}
