<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Psu extends Model
{
    use HasFactory;

    protected $table = 'psu';

    protected $fillable = [
        'asset_tag',
        'brand',
        'model',
        'wattage',
        'efficiency_rating',
        'form_factor',
        'has_modular_cabling',
        'quantity',
        'unit',
    ];

    protected $casts = [
        'has_modular_cabling' => 'boolean',
        'efficiency_rating' => 'string',
        'form_factor' => 'string',
        'quantity' => 'integer',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Assets::class, 'asset_tag', 'asset_tag');
    }
}
