<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'agency',
        'phone',
        'avatar',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function missingPersons(): HasMany
    {
        return $this->hasMany(MissingPerson::class, 'created_by');
    }

    public function assignedAlerts(): HasMany
    {
        return $this->hasMany(Alert::class, 'assigned_to');
    }

    public function reviewedDetections(): HasMany
    {
        return $this->hasMany(Detection::class, 'reviewed_by');
    }

    public function verificationRequests(): HasMany
    {
        return $this->hasMany(VerificationRequest::class, 'requested_by');
    }

    public function reviewedVerifications(): HasMany
    {
        return $this->hasMany(VerificationRequest::class, 'reviewed_by');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function pushTokens(): HasMany
    {
        return $this->hasMany(PushToken::class);
    }

    public function patrolAreas(): BelongsToMany
    {
        return $this->belongsToMany(PatrolArea::class);
    }

    public function unreadNotifications(): HasMany
    {
        return $this->notifications()->whereNull('read_at');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, UserRole $role)
    {
        return $query->where('role', $role);
    }

    // Helpers
    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    public function isPdrmOfficer(): bool
    {
        return $this->role === UserRole::PdrmOfficer;
    }

    public function isPatrolUnit(): bool
    {
        return $this->role === UserRole::PatrolUnit;
    }

    public function isPublicUser(): bool
    {
        return $this->role === UserRole::PublicUser;
    }

    public function avatarUrl(): string
    {
        if ($this->avatar) {
            // Cloudinary URL (starts with https://)
            if (str_starts_with($this->avatar, 'https://')) {
                return $this->avatar;
            }
            // Local storage path
            return asset('storage/' . $this->avatar);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=1B3A5C&color=fff&size=128';
    }
}
