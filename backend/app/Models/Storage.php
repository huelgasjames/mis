<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Storage extends Model
{
    use HasFactory;

    protected $table = 'storage';

    protected $fillable = [
        'asset_tag',
        'brand',
        'model',
        'type',
        'capacity',
        'interface',
        'form_factor',
        'rpm',
        'quantity',
        'unit',
    ];

    protected $casts = [
        'rpm' => 'integer',
        'type' => 'string',
        'interface' => 'string',
        'form_factor' => 'string',
        'quantity' => 'integer',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Assets::class, 'asset_tag', 'asset_tag');
    }
}
