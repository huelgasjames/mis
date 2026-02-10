<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DvdRom extends Model
{
    use HasFactory;

    protected $table = 'dvd_rom';

    protected $fillable = [
        'asset_tag',
        'brand',
        'model',
        'type',
        'speed',
        'has_writer',
        'quantity',
        'unit',
    ];

    protected $casts = [
        'has_writer' => 'boolean',
        'quantity' => 'integer',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Assets::class, 'asset_tag', 'asset_tag');
    }
}
