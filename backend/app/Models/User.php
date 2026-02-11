<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'email',
        'role',
        'password',
        'last_activity_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'deleted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    // Relationships
    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    // Session Management Methods
    
    /**
     * Get session timeout in minutes based on role
     */
    public function getSessionTimeoutMinutes(): int
    {
        return match($this->role) {
            'admin' => 60,
            'manager' => 45,
            'supervisor' => 30,
            'technician' => 20,
            'staff' => 15,
            default => 30,
        };
    }

    /**
     * Get session timeout label for display
     */
    public function getSessionTimeoutLabel(): string
    {
        return match($this->role) {
            'admin' => '1 hour',
            'manager' => '45 minutes',
            'supervisor' => '30 minutes',
            'technician' => '20 minutes',
            'staff' => '15 minutes',
            default => '30 minutes',
        };
    }

    /**
     * Check if user session is active
     */
    public function isSessionActive(): bool
    {
        if (!$this->last_activity_at) return true;
        $timeoutMinutes = $this->getSessionTimeoutMinutes();
        $elapsedMinutes = now()->diffInMinutes($this->last_activity_at);
        return $elapsedMinutes < $timeoutMinutes;
    }

    /**
     * Update last activity timestamp
     */
    public function updateLastActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }

    /**
     * Clear session (logout)
     */
    public function clearSession(): void
    {
        $this->update(['remember_token' => null, 'last_activity_at' => null]);
    }

    // Role Helper Methods
    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isManager(): bool { return $this->role === 'manager'; }
    public function isSupervisor(): bool { return $this->role === 'supervisor'; }
    public function hasElevatedPermissions(): bool 
    { 
        return in_array($this->role, ['admin', 'manager', 'supervisor']); 
    }

    // Methods
    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);
        $initials = '';
        
        foreach ($words as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
        
        return $initials;
    }

    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    // Events
    protected static function booted(): void
    {
        static::creating(function ($user) {
            if (!$user->uuid) {
                $user->uuid = \Illuminate\Support\Str::uuid();
            }
        });

        // Temporarily disabled audit logging to fix login issues
        /*
        static::updated(function ($user) {
            // Log important user changes
            if ($user->isDirty(['name', 'email'])) {
                AuditLog::logChange($user, 'user_details_changed',
                    $user->getOriginal(),
                    $user->only(['name', 'email']),
                    ['notes' => 'User details modified']
                );
            }
        });
        */
    }
}
