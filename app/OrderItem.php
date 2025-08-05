<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'menu_item_id',
        'quantity',
        'unit_price',
        'total_price',
        'customizations',
        'notes',
        'status',
        'prepared_at',
        'served_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'customizations' => 'array',
        'prepared_at' => 'datetime',
        'served_at' => 'datetime',
    ];

    // Relationships
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
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

    public function scopePreparing($query)
    {
        return $query->where('status', 'preparing');
    }

    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    // Business Logic
    public function calculateTotal()
    {
        $this->total_price = $this->unit_price * $this->quantity;
    }

    public function updateStatus($status)
    {
        $this->status = $status;
        
        switch ($status) {
            case 'prepared':
                $this->prepared_at = now();
                break;
            case 'served':
                $this->served_at = now();
                break;
        }
        
        $this->save();
    }

    public function getCustomizationsDisplayAttribute()
    {
        if (!$this->customizations) {
            return '';
        }
        
        return collect($this->customizations)
            ->map(function ($value, $key) {
                return ucfirst($key) . ': ' . $value;
            })
            ->implode(', ');
    }
}
