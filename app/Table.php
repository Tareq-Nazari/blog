<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Table extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'number',
        'capacity',
        'type',
        'description',
        'x_position',
        'y_position',
        'status',
        'is_active',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'x_position' => 'decimal:2',
        'y_position' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
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

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeOccupied($query)
    {
        return $query->where('status', 'occupied');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByCapacity($query, $minCapacity)
    {
        return $query->where('capacity', '>=', $minCapacity);
    }

    // Business Logic
    public function isAvailable()
    {
        return $this->status === 'available' && $this->is_active;
    }

    public function isOccupied()
    {
        return $this->status === 'occupied';
    }

    public function isReserved()
    {
        return $this->status === 'reserved';
    }

    public function occupy()
    {
        $this->status = 'occupied';
        $this->save();
    }

    public function reserve()
    {
        $this->status = 'reserved';
        $this->save();
    }

    public function makeAvailable()
    {
        $this->status = 'available';
        $this->save();
    }

    public function getCurrentOrder()
    {
        return $this->orders()
            ->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready', 'served'])
            ->latest()
            ->first();
    }

    public function getCurrentReservation()
    {
        return $this->reservations()
            ->where('status', 'confirmed')
            ->where('reservation_date', '<=', now())
            ->where('reservation_date', '>=', now()->subHours(2))
            ->first();
    }

    public function isAvailableForReservation($dateTime, $duration = 120)
    {
        $endTime = $dateTime->copy()->addMinutes($duration);
        
        $conflictingReservations = $this->reservations()
            ->where('status', 'confirmed')
            ->where(function($query) use ($dateTime, $endTime) {
                $query->whereBetween('reservation_date', [$dateTime, $endTime])
                      ->orWhere(function($q) use ($dateTime, $endTime) {
                          $q->where('reservation_date', '<=', $dateTime)
                            ->whereRaw('DATE_ADD(reservation_date, INTERVAL duration_minutes MINUTE) >= ?', [$dateTime]);
                      });
            })
            ->exists();
            
        return !$conflictingReservations;
    }
}
