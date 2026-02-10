<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_uuid',
        'maintenance_type',
        'description',
        'performed_by',
        'cost',
        'maintenance_date',
        'next_maintenance_date',
        'notes',
    ];

    protected $casts = [
        'maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'cost' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(LaboratoryInventory::class, 'inventory_uuid', 'uuid');
    }

    // Scopes
    public function scopeByType($query, string $type)
    {
        return $query->where('maintenance_type', $type);
    }

    public function scopeScheduled($query)
    {
        return $query->whereNotNull('next_maintenance_date')
                    ->where('next_maintenance_date', '>=', now());
    }

    public function scopeOverdue($query)
    {
        return $query->whereNotNull('next_maintenance_date')
                    ->where('next_maintenance_date', '<', now());
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('maintenance_date', [$startDate, $endDate]);
    }

    // Accessors
    public function getIsOverdueAttribute(): bool
    {
        if (!$this->next_maintenance_date) {
            return false;
        }

        return $this->next_maintenance_date->isPast();
    }

    public function getDaysUntilNextMaintenanceAttribute(): ?int
    {
        if (!$this->next_maintenance_date) {
            return null;
        }

        return now()->diffInDays($this->next_maintenance_date, false);
    }

    // Methods
    public function isScheduled(): bool
    {
        return !is_null($this->next_maintenance_date);
    }

    public function scheduleNextMaintenance(\Carbon\Carbon $date): bool
    {
        return $this->update(['next_maintenance_date' => $date]);
    }
}
