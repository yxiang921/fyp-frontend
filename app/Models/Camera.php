<?php

namespace App\Models;

use App\Enums\CameraStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Camera extends Model
{
    protected $fillable = [
        'camera_id',
        'name',
        'location',
        'ip_address',
        'port',
        'stream_url',
        'stream_type',
        'username',
        'password',
        'latitude',
        'longitude',
        'status',
        'description',
        'registered_by',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'port' => 'integer',
            'status' => CameraStatus::class,
        ];
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
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
    public function scopeOnline($query)
    {
        return $query->where('status', CameraStatus::Online);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'ilike', "%{$search}%")
              ->orWhere('camera_id', 'ilike', "%{$search}%")
              ->orWhere('location', 'ilike', "%{$search}%")
              ->orWhere('ip_address', 'ilike', "%{$search}%");
        });
    }

    // IP Camera helpers
    public function hasIpAddress(): bool
    {
        return !empty($this->ip_address) || $this->stream_type === 'webcam';
    }

    /**
     * Get the Python server video feed URL for this camera.
     */
    public function getPythonVideoFeedUrl(): string
    {
        $baseUrl = config('services.python_api.base_url', 'http://localhost:5000');
        return $baseUrl . '/video_feed/' . $this->camera_id;
    }

    public function getStreamUrl(): ?string
    {
        // Webcam: stream_url is the device index (0 = default webcam)
        if ($this->stream_type === 'webcam') {
            return $this->stream_url ?: '0';
        }

        // Custom type: use stream_url as the full URL directly
        if ($this->stream_type === 'custom' && $this->stream_url) {
            return $this->stream_url;
        }

        if (!$this->hasIpAddress()) {
            return null;
        }

        if ($this->stream_url && $this->stream_type !== 'custom') {
            // Use stream URL path with IP/port substitution
            $url = $this->stream_url;
            $url = str_replace('{ip}', $this->ip_address, $url);
            $url = str_replace('{port}', (string) $this->port, $url);
            return $url;
        }

        // Build default URL based on stream type
        $base = "http://{$this->ip_address}:{$this->port}";

        return match ($this->stream_type) {
            'mjpeg' => $base . '/mjpeg',
            'snapshot' => $base . '/snapshot.jpg',
            'rtsp' => "rtsp://{$this->ip_address}:{$this->port}/live",
            'hls' => $base . '/live/stream.m3u8',
            default => $base . '/mjpeg',
        };
    }
}
