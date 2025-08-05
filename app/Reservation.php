<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_number',
        'restaurant_id',
        'customer_id',
        'table_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'party_size',
        'reservation_date',
        'duration_minutes',
        'status',
        'special_requests',
        'notes',
        'confirmed_at',
        'seated_at',
        'completed_at',
        'cancellation_reason',
        'reminder_sent',
    ];

    protected $casts = [
        'party_size' => 'integer',
        'duration_minutes' => 'integer',
        'reservation_date' => 'datetime',
        'confirmed_at' => 'datetime',
        'seated_at' => 'datetime',
        'completed_at' => 'datetime',
        'reminder_sent' => 'boolean',
    ];

    // Relationships
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('reservation_date', today());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('reservation_date', '>=', now());
    }

    public function scopeNeedsReminder($query)
    {
        return $query->confirmed()
            ->where('reminder_sent', false)
            ->whereBetween('reservation_date', [now()->addHour(), now()->addHours(2)]);
    }

    // Business Logic
    public function generateReservationNumber()
    {
        $prefix = 'RES';
        $date = now()->format('Ymd');
        $sequence = str_pad($this->id, 4, '0', STR_PAD_LEFT);
        return "{$prefix}-{$date}-{$sequence}";
    }

    public function confirm()
    {
        $this->status = 'confirmed';
        $this->confirmed_at = now();
        $this->save();
        
        if ($this->table) {
            $this->table->reserve();
        }
    }

    public function seat()
    {
        $this->status = 'seated';
        $this->seated_at = now();
        $this->save();
        
        if ($this->table) {
            $this->table->occupy();
        }
    }

    public function complete()
    {
        $this->status = 'completed';
        $this->completed_at = now();
        $this->save();
        
        if ($this->table) {
            $this->table->makeAvailable();
        }
    }

    public function cancel($reason = null)
    {
        $this->status = 'cancelled';
        $this->cancellation_reason = $reason;
        $this->save();
        
        if ($this->table) {
            $this->table->makeAvailable();
        }
    }

    public function markAsNoShow()
    {
        $this->status = 'no_show';
        $this->save();
        
        if ($this->table) {
            $this->table->makeAvailable();
        }
    }

    public function getEndTimeAttribute()
    {
        return $this->reservation_date->addMinutes($this->duration_minutes);
    }

    public function isActive()
    {
        return in_array($this->status, ['pending', 'confirmed', 'seated']);
    }

    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'confirmed']) && 
               $this->reservation_date > now()->addHour();
    }

    public function isOverdue()
    {
        return $this->status === 'confirmed' && 
               $this->reservation_date->addMinutes(15) < now();
    }

    public function sendReminder()
    {
        // Logic to send reminder email/SMS
        $this->reminder_sent = true;
        $this->save();
    }
}
