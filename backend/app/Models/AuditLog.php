<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'action',
        'old_value',
        'new_value',
        'user_id',
        'ip_address',
        'user_agent',
        'notes',
    ];

    protected $casts = [
        'old_value' => 'json',
        'new_value' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    // Scopes
    public function scopeForModel($query, string $modelType)
    {
        return $query->where('auditable_type', $modelType);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Accessors
    public function getFormattedOldValueAttribute(): string
    {
        if (is_array($this->old_value)) {
            return json_encode($this->old_value, JSON_PRETTY_PRINT);
        }

        return $this->old_value ?? '';
    }

    public function getFormattedNewValueAttribute(): string
    {
        if (is_array($this->new_value)) {
            return json_encode($this->new_value, JSON_PRETTY_PRINT);
        }

        return $this->new_value ?? '';
    }

    public function getHasChangesAttribute(): bool
    {
        return $this->old_value !== $this->new_value;
    }

    // Methods
    public static function logChange(
        Model $model,
        string $action,
        $oldValue = null,
        $newValue = null,
        array $metadata = []
    ): self {
        return static::create([
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'action' => $action,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'notes' => $metadata['notes'] ?? null,
        ]);
    }

    public function isStatusChange(): bool
    {
        return $this->action === 'status_change';
    }

    public function isCreation(): bool
    {
        return $this->action === 'created';
    }

    public function isUpdate(): bool
    {
        return $this->action === 'updated';
    }

    public function isDeletion(): bool
    {
        return $this->action === 'deleted';
    }
}
