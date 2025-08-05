<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'preferences',
        'loyalty_points',
        'loyalty_tier',
        'total_spent',
        'total_orders',
        'last_order_at',
        'marketing_consent',
        'is_active',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'preferences' => 'array',
        'loyalty_points' => 'integer',
        'total_spent' => 'decimal:2',
        'total_orders' => 'integer',
        'last_order_at' => 'datetime',
        'marketing_consent' => 'boolean',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    // Relationships
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getAgeAttribute()
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByLoyaltyTier($query, $tier)
    {
        return $query->where('loyalty_tier', $tier);
    }

    // Business Logic
    public function addLoyaltyPoints($points)
    {
        $this->loyalty_points += $points;
        $this->updateLoyaltyTier();
        $this->save();
    }

    public function redeemLoyaltyPoints($points)
    {
        if ($this->loyalty_points >= $points) {
            $this->loyalty_points -= $points;
            $this->save();
            return true;
        }
        return false;
    }

    private function updateLoyaltyTier()
    {
        if ($this->total_spent >= 1000) {
            $this->loyalty_tier = 'platinum';
        } elseif ($this->total_spent >= 500) {
            $this->loyalty_tier = 'gold';
        } elseif ($this->total_spent >= 200) {
            $this->loyalty_tier = 'silver';
        } else {
            $this->loyalty_tier = 'bronze';
        }
    }

    public function updateOrderStats($orderTotal)
    {
        $this->total_spent += $orderTotal;
        $this->total_orders += 1;
        $this->last_order_at = now();
        $this->updateLoyaltyTier();
        $this->save();
    }
}
