<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Floor extends Model
{
    use HasFactory;

    protected $fillable = [
        'house_id',
        'floor_number',
        'name',
        'description',
        'total_flats',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'floor_number' => 'integer',
        'total_flats' => 'integer',
    ];

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    public function flats(): HasMany
    {
        return $this->hasMany(Flat::class);
    }
}