<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Flat extends Model
{
    use HasFactory;

    protected $fillable = [
        'house_id',
        'floor_id',
        'flat_number',
        'name',
        'type',
        'bedrooms',
        'bathrooms',
        'size_sqft',
        'rent_amount',
        'security_deposit',
        'description',
        'amenities',
        'status',
        'is_furnished',
        'available_from',
    ];

    protected $casts = [
        'amenities' => 'array',
        'is_furnished' => 'boolean',
        'available_from' => 'date',
        'rent_amount' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'size_sqft' => 'integer',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
    ];

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(TenantUser::class);
    }
}