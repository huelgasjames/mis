<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Laboratory extends Model
{
    use HasFactory, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'uuid',
        'name',
        'code',
        'description',
        'location',
        'capacity',
        'supervisor',
        'contact_number',
        'status',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    protected $appends = [
        'occupancy_percentage',
        'status_color',
        'inventory_stats',
        'is_full',
        'is_over_capacity',
        'available_slots',
    ];

    // Relationships
    public function inventory(): HasMany
    {
        return $this->hasMany(LaboratoryInventory::class, 'laboratory_id', 'uuid')->withTrashed();
    }

    public function activeInventory(): HasMany
    {
        return $this->hasMany(LaboratoryInventory::class, 'laboratory_id', 'uuid');
    }

    public function deployedPcs(): HasMany
    {
        return $this->hasMany(LaboratoryInventory::class, 'laboratory_id', 'uuid')->deployed();
    }

    public function underRepairPcs(): HasMany
    {
        return $this->hasMany(LaboratoryInventory::class, 'laboratory_id', 'uuid')->underRepair();
    }

    public function availablePcs(): HasMany
    {
        return $this->hasMany(LaboratoryInventory::class, 'laboratory_id', 'uuid')->available();
    }

    public function defectivePcs(): HasMany
    {
        return $this->hasMany(LaboratoryInventory::class, 'laboratory_id', 'uuid')->defective();
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'Active');
    }

    public function scopeMaintenance(Builder $query): Builder
    {
        return $query->where('status', 'Maintenance');
    }

    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('status', 'Closed');
    }

    public function scopeByLocation(Builder $query, string $location): Builder
    {
        return $query->where('location', 'like', "%{$location}%");
    }

    public function scopeBySupervisor(Builder $query, string $supervisor): Builder
    {
        return $query->where('supervisor', 'like', "%{$supervisor}%");
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%")
              ->orWhere('location', 'like', "%{$search}%")
              ->orWhere('supervisor', 'like', "%{$search}%");
        });
    }

    public function scopeHasCapacity(Builder $query, int $minCapacity = 1): Builder
    {
        return $query->where('capacity', '>=', $minCapacity);
    }

    public function scopeWithInventoryStats(Builder $query): Builder
    {
        return $query->withCount([
            'inventory',
            'deployedPcs',
            'underRepairPcs',
            'availablePcs',
            'defectivePcs'
        ]);
    }

    // Accessors
    public function getOccupancyPercentageAttribute(): float
    {
        $total = $this->activeInventory()->count();
        $capacity = $this->capacity;
        
        if ($capacity == 0) return 0;
        
        return min(100, round(($total / $capacity) * 100, 2));
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'Active' => 'success',
            'Maintenance' => 'warning',
            'Closed' => 'danger',
            default => 'secondary'
        };
    }

    public function getInventoryStatsAttribute(): array
    {
        return [
            'total' => $this->activeInventory()->count(),
            'deployed' => $this->deployedPcs()->count(),
            'under_repair' => $this->underRepairPcs()->count(),
            'available' => $this->availablePcs()->count(),
            'defective' => $this->defectivePcs()->count(),
            'occupancy_percentage' => $this->occupancy_percentage,
            'capacity' => $this->capacity,
        ];
    }

    public function getIsFullAttribute(): bool
    {
        return $this->activeInventory()->count() >= $this->capacity;
    }

    public function getIsOverCapacityAttribute(): bool
    {
        return $this->activeInventory()->count() > $this->capacity;
    }

    public function getAvailableSlotsAttribute(): int
    {
        return max(0, $this->capacity - $this->activeInventory()->count());
    }

    // Mutators
    public function setCodeAttribute(string $value): void
    {
        $this->attributes['code'] = strtoupper(trim($value));
    }

    public function setNameAttribute(string $value): void
    {
        $this->attributes['name'] = ucwords(strtolower(trim($value)));
    }

    public function setSupervisorAttribute(string $value): void
    {
        $this->attributes['supervisor'] = ucwords(strtolower(trim($value)));
    }

    // Methods
    public function isActive(): bool
    {
        return $this->status === 'Active';
    }

    public function isMaintenance(): bool
    {
        return $this->status === 'Maintenance';
    }

    public function isClosed(): bool
    {
        return $this->status === 'Closed';
    }

    public function canAcceptInventory(): bool
    {
        return $this->isActive() && !$this->is_full;
    }

    public function activate(): bool
    {
        return $this->update(['status' => 'Active']);
    }

    public function setMaintenance(): bool
    {
        return $this->update(['status' => 'Maintenance']);
    }

    public function close(): bool
    {
        return $this->update(['status' => 'Closed']);
    }

    public function getInventoryByCategory(): array
    {
        return $this->activeInventory()
            ->with('category')
            ->get()
            ->groupBy('category.name')
            ->map(function ($items) {
                return [
                    'count' => $items->count(),
                    'deployed' => $items->where('status', 'Deployed')->count(),
                    'available' => $items->where('status', 'Available')->count(),
                    'under_repair' => $items->where('status', 'Under Repair')->count(),
                    'defective' => $items->where('status', 'Defective')->count(),
                ];
            })
            ->toArray();
    }

    public function getMaintenanceSchedule(): array
    {
        return $this->activeInventory()
            ->needsMaintenance()
            ->with('category')
            ->get()
            ->map(function ($item) {
                return [
                    'uuid' => $item->uuid,
                    'asset_tag' => $item->asset_tag,
                    'computer_name' => $item->computer_name,
                    'category' => $item->category->name,
                    'last_maintenance' => $item->last_maintenance,
                    'days_since_maintenance' => $item->last_maintenance 
                        ? $item->last_maintenance->diffInDays(now()) 
                        : null,
                    'is_overdue' => $item->is_overdue_maintenance,
                ];
            })
            ->toArray();
    }

    public function generateNextPcNumber(): string
    {
        $maxPcNum = $this->activeInventory()
            ->where('lab_pc_num', 'regexp', '^[0-9]+$')
            ->max('lab_pc_num');

        return (string) (($maxPcNum ?? 0) + 1);
    }

    // Events
    protected static function booted(): void
    {
        static::creating(function ($laboratory) {
            if (!$laboratory->uuid) {
                $laboratory->uuid = \Illuminate\Support\Str::uuid();
            }
            
            // Auto-generate code if not provided
            if (!$laboratory->code) {
                $laboratory->code = 'LAB' . str_pad(static::max('id') + 1, 3, '0', STR_PAD_LEFT);
            }
        });

        static::updating(function ($laboratory) {
            // Log status changes
            if ($laboratory->isDirty('status')) {
                AuditLog::logChange($laboratory, 'laboratory_status_changed',
                    $laboratory->getOriginal('status'),
                    $laboratory->status,
                    ['notes' => 'Laboratory status changed from ' . $laboratory->getOriginal('status') . ' to ' . $laboratory->status]
                );
            }
        });

        static::deleting(function ($laboratory) {
            // Prevent deletion if laboratory has inventory
            if ($laboratory->activeInventory()->count() > 0) {
                return false;
            }
        });
    }
}
