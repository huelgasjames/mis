<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ram extends Model
{
    use HasFactory;

    protected $table = 'ram';

    protected $fillable = [
        'asset_tag',
        'brand',
        'model',
        'capacity',
        'type',
        'speed',
        'modules_count',
        'quantity',
        'unit',
    ];

    protected $casts = [
        'modules_count' => 'integer',
        'type' => 'string',
        'speed' => 'string',
        'quantity' => 'integer',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Assets::class, 'asset_tag', 'asset_tag');
    }
}
