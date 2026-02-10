<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoCard extends Model
{
    use HasFactory;

    protected $table = 'video_card';

    protected $fillable = [
        'asset_tag',
        'brand',
        'model',
        'memory',
        'memory_type',
        'interface',
        'quantity',
        'unit',
    ];

    protected $casts = [
        'memory_type' => 'string',
        'interface' => 'string',
        'quantity' => 'integer',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Assets::class, 'asset_tag', 'asset_tag');
    }
}
