<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Builder;

class Department extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'office_location',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'asset_count',
        'inventory_count',
    ];

    // Relationships
    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'department_id', 'uuid');
    }

    public function departmentInventory(): HasMany
    {
        return $this->hasMany(DepartmentInventory::class, 'department_id', 'uuid');
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    // Scopes
    public function scopeByLocation(Builder $query, string $location): Builder
    {
        return $query->where('office_location', 'like', "%{$location}%");
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('office_location', 'like', "%{$search}%");
        });
    }

    // Accessors
    public function getAssetCountAttribute(): int
    {
        return $this->assets()->count();
    }

    public function getInventoryCountAttribute(): int
    {
        return $this->departmentInventory()->count();
    }

    // Mutators
    public function setNameAttribute(string $value): void
    {
        $this->attributes['name'] = ucwords(strtolower(trim($value)));
    }

    public function setOfficeLocationAttribute(string $value): void
    {
        $this->attributes['office_location'] = ucwords(strtolower(trim($value)));
    }

    // Methods
    public function getInventoryByCategory(): array
    {
        return $this->departmentInventory()
            ->with('category')
            ->get()
            ->groupBy('category.name')
            ->map(function ($items) {
                return [
                    'count' => $items->count(),
                    'working' => $items->where('status', 'Working')->count(),
                    'defective' => $items->where('status', 'Defective')->count(),
                    'deployed' => $items->where('status', 'Deployed')->count(),
                    'in_storage' => $items->where('status', 'In Storage')->count(),
                ];
            })
            ->toArray();
    }

    public function getInventoryStats(): array
    {
        $inventory = $this->departmentInventory();
        
        return [
            'total' => $inventory->count(),
            'by_status' => $inventory->get()->groupBy('status')->map->count(),
            'by_location' => $inventory->get()->groupBy('location')->map->count(),
            'recently_deployed' => $inventory->where('deployment_date', '>=', now()->subMonths(3))->count(),
        ];
    }

    // Events
    protected static function booted(): void
    {
        static::creating(function ($department) {
            if (!$department->uuid) {
                $department->uuid = \Illuminate\Support\Str::uuid();
            }
        });

        static::updated(function ($department) {
            // Log important changes
            if ($department->isDirty(['name', 'office_location'])) {
                AuditLog::logChange($department, 'department_details_changed',
                    $department->getOriginal(),
                    $department->only(['name', 'office_location']),
                    ['notes' => 'Department details modified']
                );
            }
        });

        static::deleting(function ($department) {
            // Prevent deletion if department has inventory
            if ($department->departmentInventory()->count() > 0) {
                return false;
            }
        });
    }
}
