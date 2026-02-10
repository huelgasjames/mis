<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Processor extends Model
{
    use HasFactory;

    protected $table = 'processor';

    protected $fillable = [
        'asset_tag',
        'brand',
        'model',
        'speed',
        'cores',
        'threads',
        'quantity',
        'unit',
    ];

    protected $casts = [
        'cores' => 'integer',
        'threads' => 'integer',
        'quantity' => 'integer',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Assets::class, 'asset_tag', 'asset_tag');
    }
}
