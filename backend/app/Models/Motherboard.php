<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Motherboard extends Model
{
    use HasFactory;

    protected $table = 'motherboard';

    protected $fillable = [
        'asset_tag',
        'brand',
        'model',
        'chipset',
        'socket_type',
        'form_factor',
        'quantity',
        'unit',
    ];

    protected $casts = [
        'form_factor' => 'string',
        'quantity' => 'integer',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Assets::class, 'asset_tag', 'asset_tag');
    }
}
