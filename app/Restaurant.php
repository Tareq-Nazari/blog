<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Restaurant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'website',
        'operating_hours',
        'logo',
        'tax_rate',
        'service_charge',
        'currency',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'operating_hours' => 'array',
        'settings' => 'array',
        'tax_rate' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }

    public function tables(): HasMany
    {
        return $this->hasMany(Table::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Accessors
    public function getFullAddressAttribute()
    {
        return "{$this->address}, {$this->city}, {$this->state} {$this->postal_code}, {$this->country}";
    }

    // Business Logic Methods
    public function isOpen($day = null, $time = null)
    {
        $day = $day ?? now()->format('l'); // Monday, Tuesday, etc.
        $time = $time ?? now()->format('H:i');
        
        if (!isset($this->operating_hours[$day])) {
            return false;
        }
        
        $hours = $this->operating_hours[$day];
        if ($hours['closed']) {
            return false;
        }
        
        return $time >= $hours['open'] && $time <= $hours['close'];
    }

    public function calculateTotalWithTaxAndService($subtotal)
    {
        $tax = $subtotal * ($this->tax_rate / 100);
        $service = $subtotal * ($this->service_charge / 100);
        return $subtotal + $tax + $service;
    }
}
