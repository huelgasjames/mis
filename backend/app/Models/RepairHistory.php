<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepairHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_uuid',
        'repair_type',
        'issue_description',
        'resolution_description',
        'technician',
        'cost',
        'start_date',
        'end_date',
        'parts_used',
        'warranty_info',
        'status',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'cost' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'duration',
        'duration_hours',
    ];

    // Relationships
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(LaboratoryInventory::class, 'inventory_uuid', 'uuid');
    }

    // Scopes
    public function scopeByType($query, string $type)
    {
        return $query->where('repair_type', $type);
    }

    public function scopeByTechnician($query, string $technician)
    {
        return $query->where('technician', 'like', "%{$technician}%");
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('end_date');
    }

    public function scopeInProgress($query)
    {
        return $query->whereNull('end_date');
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_date', [$startDate, $endDate]);
    }

    // Accessors
    public function getDurationAttribute(): ?string
    {
        if (!$this->start_date || !$this->end_date) {
            return null;
        }

        return $this->start_date->diffForHumans($this->end_date, true);
    }

    public function getDurationHoursAttribute(): ?float
    {
        if (!$this->start_date || !$this->end_date) {
            return null;
        }

        return $this->start_date->diffInHours($this->end_date);
    }

    public function getIsCompletedAttribute(): bool
    {
        return !is_null($this->end_date);
    }

    public function getIsInProgressAttribute(): bool
    {
        return is_null($this->end_date) && !is_null($this->start_date);
    }

    // Methods
    public function completeRepair(array $completionData): bool
    {
        return $this->update([
            'end_date' => now(),
            'resolution_description' => $completionData['resolution'] ?? $this->resolution_description,
            'cost' => $completionData['cost'] ?? $this->cost,
            'parts_used' => $completionData['parts_used'] ?? $this->parts_used,
            'status' => 'completed',
        ]);
    }

    public function isInProgress(): bool
    {
        return $this->is_in_progress;
    }

    public function isCompleted(): bool
    {
        return $this->is_completed;
    }
}
