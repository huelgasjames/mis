<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaboratoryInventory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'laboratory_inventory';

    protected $fillable = [
        'uuid',
        'asset_tag',
        'computer_name',
        'lab_pc_num',
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
        'assigned_to',
        'condition',
        'notes',
        'laboratory_id',
        'deployment_date',
        'last_maintenance',
        'repair_start_date',
        'repair_end_date',
        'repair_description',
        'repaired_by',
    ];

    protected $casts = [
        'deployment_date' => 'date',
        'last_maintenance' => 'date',
        'repair_start_date' => 'date',
        'repair_end_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    protected $appends = [
        'status_color',
        'condition_color',
        'repair_duration',
        'full_pc_number',
        'is_overdue_maintenance',
    ];

    // Relationships
    public function laboratory(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class)->withTrashed();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->withTrashed();
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
    public function scopeByLaboratory(Builder $query, $laboratoryId): Builder
    {
        return $query->where('laboratory_id', $laboratoryId);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByCondition(Builder $query, string $condition): Builder
    {
        return $query->where('condition', $condition);
    }

    public function scopeByCategory(Builder $query, $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeDeployed(Builder $query): Builder
    {
        return $query->where('status', 'Deployed');
    }

    public function scopeUnderRepair(Builder $query): Builder
    {
        return $query->where('status', 'Under Repair');
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', 'Available');
    }

    public function scopeDefective(Builder $query): Builder
    {
        return $query->where('status', 'Defective');
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
              ->orWhere('lab_pc_num', 'like', "%{$search}%")
              ->orWhere('assigned_to', 'like', "%{$search}%");
        });
    }

    // Accessors
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'Deployed' => 'primary',
            'Under Repair' => 'warning',
            'Available' => 'success',
            'Defective' => 'danger',
            'For Disposal' => 'secondary',
            default => 'light'
        };
    }

    public function getConditionColorAttribute(): string
    {
        return match($this->condition) {
            'Excellent' => 'success',
            'Good' => 'info',
            'Fair' => 'warning',
            'Poor' => 'danger',
            default => 'secondary'
        };
    }

    public function getRepairDurationAttribute(): ?int
    {
        if (!$this->repair_start_date || !$this->repair_end_date) {
            return null;
        }

        return $this->repair_start_date->diffInDays($this->repair_end_date);
    }

    public function getFullPcNumberAttribute(): string
    {
        $labCode = $this->laboratory?->code ?? 'UNKNOWN';
        return "{$labCode}-{$this->lab_pc_num}";
    }

    public function getIsOverdueMaintenanceAttribute(): bool
    {
        if (!$this->last_maintenance) {
            return true;
        }

        return $this->last_maintenance->lt(now()->subMonths(6));
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
    public function isUnderRepair(): bool
    {
        return $this->status === 'Under Repair';
    }

    public function isDeployed(): bool
    {
        return $this->status === 'Deployed';
    }

    public function isAvailable(): bool
    {
        return $this->status === 'Available';
    }

    public function isDefective(): bool
    {
        return $this->status === 'Defective';
    }

    public function canBeDeployed(): bool
    {
        return in_array($this->status, ['Available', 'Under Repair']) && 
               in_array($this->condition, ['Excellent', 'Good']);
    }

    public function canBeRepaired(): bool
    {
        return in_array($this->status, ['Deployed', 'Available', 'Defective']);
    }

    public function startRepair(array $repairData): bool
    {
        if (!$this->canBeRepaired()) {
            return false;
        }

        return $this->update([
            'status' => 'Under Repair',
            'repair_start_date' => now(),
            'repair_end_date' => null,
            'repair_description' => $repairData['description'] ?? null,
            'repaired_by' => $repairData['repaired_by'] ?? null,
        ]);
    }

    public function completeRepair(array $completionData): bool
    {
        if (!$this->isUnderRepair()) {
            return false;
        }

        return $this->update([
            'status' => $completionData['next_status'] ?? 'Available',
            'condition' => $completionData['condition'] ?? $this->condition,
            'repair_end_date' => now(),
            'notes' => $completionData['notes'] ?? $this->notes,
        ]);
    }

    public function deploy(string $assignedTo = null): bool
    {
        if (!$this->canBeDeployed()) {
            return false;
        }

        return $this->update([
            'status' => 'Deployed',
            'deployment_date' => now(),
            'assigned_to' => $assignedTo,
        ]);
    }

    public function recall(): bool
    {
        if (!$this->isDeployed()) {
            return false;
        }

        return $this->update([
            'status' => 'Available',
            'assigned_to' => null,
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
                $inventory->auditLogs()->create([
                    'action' => 'status_change',
                    'old_value' => $inventory->getOriginal('status'),
                    'new_value' => $inventory->status,
                    'user_id' => auth()->id(),
                    'notes' => 'Status changed from ' . $inventory->getOriginal('status') . ' to ' . $inventory->status,
                ]);
            }
        });
    }
}
