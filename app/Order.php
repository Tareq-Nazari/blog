<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'restaurant_id',
        'customer_id',
        'table_id',
        'staff_id',
        'type',
        'status',
        'payment_status',
        'payment_method',
        'subtotal',
        'tax_amount',
        'service_charge',
        'discount_amount',
        'total_amount',
        'discount_code',
        'special_instructions',
        'customer_notes',
        'kitchen_notes',
        'estimated_prep_time',
        'confirmed_at',
        'prepared_at',
        'served_at',
        'completed_at',
        'delivery_address',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'estimated_prep_time' => 'integer',
        'confirmed_at' => 'datetime',
        'prepared_at' => 'datetime',
        'served_at' => 'datetime',
        'completed_at' => 'datetime',
        'delivery_address' => 'array',
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

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Business Logic
    public function generateOrderNumber()
    {
        $prefix = 'ORD';
        $date = now()->format('Ymd');
        $sequence = str_pad($this->id, 4, '0', STR_PAD_LEFT);
        return "{$prefix}-{$date}-{$sequence}";
    }

    public function calculateTotals()
    {
        $this->subtotal = $this->orderItems->sum('total_price');
        $this->tax_amount = $this->subtotal * ($this->restaurant->tax_rate / 100);
        $this->service_charge = $this->subtotal * ($this->restaurant->service_charge / 100);
        $this->total_amount = $this->subtotal + $this->tax_amount + $this->service_charge - $this->discount_amount;
    }

    public function updateStatus($status)
    {
        $this->status = $status;
        
        switch ($status) {
            case 'confirmed':
                $this->confirmed_at = now();
                break;
            case 'prepared':
                $this->prepared_at = now();
                break;
            case 'served':
                $this->served_at = now();
                break;
            case 'completed':
                $this->completed_at = now();
                if ($this->customer) {
                    $this->customer->updateOrderStats($this->total_amount);
                }
                break;
        }
        
        $this->save();
    }

    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    public function getEstimatedCompletionTime()
    {
        if (!$this->estimated_prep_time) {
            return null;
        }
        
        $baseTime = $this->confirmed_at ?: $this->created_at;
        return $baseTime->addMinutes($this->estimated_prep_time);
    }
}
