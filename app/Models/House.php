<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class House extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'description',
        'total_floors',
        'amenities',
        'rules',
        'is_active',
    ];

    protected $casts = [
        'amenities' => 'array',
        'rules' => 'array',
        'is_active' => 'boolean',
    ];

    public function floors(): HasMany
    {
        return $this->hasMany(Floor::class);
    }

    public function flats(): HasMany
    {
        return $this->hasMany(Flat::class);
    }
}