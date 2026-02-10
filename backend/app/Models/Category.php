<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'code',
        'name',
        'description',
        'is_editable',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_editable' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    protected $appends = [
        'item_count',
        'is_system_category',
    ];

    // Relationships
    public function laboratoryInventory(): HasMany
    {
        return $this->hasMany(LaboratoryInventory::class, 'category_id')->withTrashed();
    }

    public function departmentInventory(): HasMany
    {
        return $this->hasMany(DepartmentInventory::class, 'category_id')->withTrashed();
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'category_id')->withTrashed();
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    public function scopeEditable(Builder $query): Builder
    {
        return $query->where('is_editable', true);
    }

    public function scopeSystem(Builder $query): Builder
    {
        return $query->where('is_editable', false);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeByCode(Builder $query, string $code): Builder
    {
        return $query->where('code', $code);
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    // Accessors
    public function getItemCountAttribute(): int
    {
        return $this->laboratoryInventory()->count() + 
               $this->departmentInventory()->count() + 
               $this->assets()->count();
    }

    public function getIsSystemCategoryAttribute(): bool
    {
        return !$this->is_editable;
    }

    public function getFormattedCodeAttribute(): string
    {
        return strtoupper($this->code);
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

    // Methods
    public function canBeEdited(): bool
    {
        return $this->is_editable;
    }

    public function canBeDeleted(): bool
    {
        if (!$this->is_editable) {
            return false;
        }

        return $this->item_count === 0;
    }

    public function canBeDeactivated(): bool
    {
        if (!$this->is_editable) {
            return false;
        }

        return $this->item_count === 0;
    }

    public function activate(): bool
    {
        if ($this->is_system_category) {
            return false;
        }

        return $this->update(['is_active' => true]);
    }

    public function deactivate(): bool
    {
        if (!$this->canBeDeactivated()) {
            return false;
        }

        return $this->update(['is_active' => false]);
    }

    public function getInventoryCount(): array
    {
        return [
            'laboratory' => $this->laboratoryInventory()->count(),
            'department' => $this->departmentInventory()->count(),
            'assets' => $this->assets()->count(),
            'total' => $this->item_count,
        ];
    }

    public function hasInventory(): bool
    {
        return $this->item_count > 0;
    }

    // Events
    protected static function booted(): void
    {
        static::creating(function ($category) {
            if (!$category->id) {
                $category->id = \Illuminate\Support\Str::uuid();
            }
            
            // Auto-set sort order if not provided
            if (!$category->sort_order) {
                $category->sort_order = static::max('sort_order') + 1;
            }
        });

        static::updating(function ($category) {
            // Log changes to system categories
            if (!$category->is_editable && $category->isDirty()) {
                \Log::warning('System category modified', [
                    'category_id' => $category->id,
                    'changes' => $category->getDirty(),
                    'user_id' => auth()->id(),
                ]);
            }
        });

        static::deleting(function ($category) {
            // Prevent deletion of system categories
            if (!$category->is_editable) {
                return false;
            }

            // Prevent deletion if category has inventory
            if ($category->hasInventory()) {
                return false;
            }
        });

        static::updated(function ($category) {
            // Log important changes
            if ($category->isDirty(['is_active', 'is_editable'])) {
                AuditLog::logChange($category, 'category_settings_changed', 
                    $category->getOriginal(), 
                    $category->only(['is_active', 'is_editable']),
                    ['notes' => 'Category settings modified']
                );
            }
        });
    }
}
