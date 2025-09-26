<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantUser extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'flat_id',
        'lease_start',
        'lease_end',
        'monthly_rent',
        'security_deposit_paid',
        'emergency_contact',
        'documents',
        'status',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'lease_start' => 'date',
        'lease_end' => 'date',
        'monthly_rent' => 'decimal:2',
        'security_deposit_paid' => 'decimal:2',
        'documents' => 'array',
        'emergency_contact' => 'array',
        'is_active' => 'boolean',
    ];

    public function flat(): BelongsTo
    {
        return $this->belongsTo(Flat::class);
    }
}