<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Department;

class Assets extends Model
{
    protected $fillable = [
        'asset_tag',
        'computer_name',
        'category',
        'processor',
        'ram',
        'storage',
        'serial_number',
        'status',
        'department_id',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
