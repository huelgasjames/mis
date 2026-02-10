<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class DepartmentInventory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'department_inventory';

    protected $fillable = [
        'uuid',
        'asset_tag',
        'computer_name',
        'category',
        'category_id',
        'processor',
        'motherboard',
        'video_card',
        'dvd_rom',
        'psu',
        'ram',
        'storage',
        'serial_number',
        'status',
        'description',
        'location',
        'department_id',
        'deployment_date',
        'last_maintenance',
    ];

    protected $casts = [
        'deployment_date' => 'date',
        'last_maintenance' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    protected $appends = [
        'status_color',
        'is_overdue_maintenance',
        'full_asset_identifier',
    ];

    // Relationships
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'uuid')->withTrashed();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id')->withTrashed();
    }

    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(MaintenanceRecord::class, 'inventory_uuid', 'uuid');
    }

    public function repairHistory(): HasMany
    {
        return $this->hasMany(RepairHistory::class, 'inventory_uuid', 'uuid');
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    // Scopes
    public function scopeByDepartment(Builder $query, $departmentId): Builder
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByCategory(Builder $query, $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByLocation(Builder $query, string $location): Builder
    {
        return $query->where('location', 'like', "%{$location}%");
    }

    public function scopeWorking(Builder $query): Builder
    {
        return $query->where('status', 'Working');
    }

    public function scopeDeployed(Builder $query): Builder
    {
        return $query->where('status', 'Deployed');
    }

    public function scopeDefective(Builder $query): Builder
    {
        return $query->where('status', 'Defective');
    }

    public function scopeInStorage(Builder $query): Builder
    {
        return $query->where('status', 'In Storage');
    }

    public function scopeNeedsMaintenance(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('last_maintenance')
              ->orWhere('last_maintenance', '<', now()->subMonths(6));
        });
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('asset_tag', 'like', "%{$search}%")
              ->orWhere('computer_name', 'like', "%{$search}%")
              ->orWhere('serial_number', 'like', "%{$search}%")
              ->orWhere('pc_num', 'like', "%{$search}%");
        });
    }

    // Accessors
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'Working' => 'success',
            'Deployed' => 'primary',
            'Defective' => 'danger',
            'For Disposal' => 'warning',
            'In Storage' => 'info',
            default => 'secondary',
        };
    }

    public function getIsOverdueMaintenanceAttribute(): bool
    {
        if (!$this->last_maintenance) {
            return true;
        }

        return $this->last_maintenance->lt(now()->subMonths(6));
    }

    public function getFullAssetIdentifierAttribute(): string
    {
        $deptCode = $this->department?->name ?? 'Unknown';
        return "{$deptCode}-{$this->pc_num}";
    }

    // Mutators
    public function setAssetTagAttribute(string $value): void
    {
        $this->attributes['asset_tag'] = strtoupper(trim($value));
    }

    public function setComputerNameAttribute(string $value): void
    {
        $this->attributes['computer_name'] = strtoupper(trim($value));
    }

    public function setSerialNumberAttribute(?string $value): void
    {
        $this->attributes['serial_number'] = $value ? strtoupper(trim($value)) : null;
    }

    // Methods
    public function isWorking(): bool
    {
        return $this->status === 'Working';
    }

    public function isDeployed(): bool
    {
        return $this->status === 'Deployed';
    }

    public function isDefective(): bool
    {
        return $this->status === 'Defective';
    }

    public function isInStorage(): bool
    {
        return $this->status === 'In Storage';
    }

    public function canBeDeployed(): bool
    {
        return in_array($this->status, ['Working', 'In Storage']);
    }

    public function deploy(): bool
    {
        if (!$this->canBeDeployed()) {
            return false;
        }

        return $this->update([
            'status' => 'Deployed',
            'deployment_date' => now(),
        ]);
    }

    public function recall(): bool
    {
        if (!$this->isDeployed()) {
            return false;
        }

        return $this->update([
            'status' => 'Working',
            'deployment_date' => null,
        ]);
    }

    // Events
    protected static function booted(): void
    {
        static::creating(function ($inventory) {
            if (!$inventory->uuid) {
                $inventory->uuid = \Illuminate\Support\Str::uuid();
            }
            
            // Auto-set deployment date
            if ($inventory->status === 'Deployed' && !$inventory->deployment_date) {
                $inventory->deployment_date = now();
            }
        });

        static::updating(function ($inventory) {
            // Auto-set deployment date when changing to Deployed status
            if ($inventory->status === 'Deployed' && 
                !$inventory->getOriginal('deployment_date') && 
                !$inventory->deployment_date) {
                $inventory->deployment_date = now();
            }
        });

        static::updated(function ($inventory) {
            // Log status changes
            if ($inventory->isDirty('status')) {
                AuditLog::logChange($inventory, 'status_change',
                    $inventory->getOriginal('status'),
                    $inventory->status,
                    ['notes' => 'Status changed from ' . $inventory->getOriginal('status') . ' to ' . $inventory->status]
                );
            }
        });
    }
}
