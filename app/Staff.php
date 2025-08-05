<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Staff extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $table = 'staff';

    protected $fillable = [
        'restaurant_id',
        'employee_id',
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'date_of_birth',
        'gender',
        'address',
        'city',
        'state',
        'postal_code',
        'position',
        'hourly_rate',
        'commission_rate',
        'hire_date',
        'termination_date',
        'employment_status',
        'work_schedule',
        'permissions',
        'profile_photo',
        'notes',
        'can_login',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'hourly_rate' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'hire_date' => 'date',
        'termination_date' => 'date',
        'work_schedule' => 'array',
        'permissions' => 'array',
        'can_login' => 'boolean',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
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

    // Accessors
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getIsActiveAttribute()
    {
        return $this->employment_status === 'active';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('employment_status', 'active');
    }

    public function scopeByPosition($query, $position)
    {
        return $query->where('position', $position);
    }

    public function scopeCanLogin($query)
    {
        return $query->where('can_login', true);
    }

    // Business Logic
    public function isWorkingToday()
    {
        $today = now()->format('l'); // Monday, Tuesday, etc.
        
        if (!isset($this->work_schedule[$today])) {
            return false;
        }
        
        return !$this->work_schedule[$today]['off'];
    }

    public function getWorkingHoursToday()
    {
        $today = now()->format('l');
        
        if (!$this->isWorkingToday()) {
            return null;
        }
        
        return $this->work_schedule[$today];
    }

    public function calculateMonthlyEarnings($month = null, $year = null)
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;
        
        // This would calculate based on orders served, hours worked, etc.
        // Simplified implementation
        $ordersServed = $this->orders()
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->count();
            
        $commission = $this->orders()
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->sum('total_amount') * ($this->commission_rate / 100);
            
        return $commission;
    }

    public function hasPermission($permission)
    {
        if (!$this->permissions) {
            return false;
        }
        
        return in_array($permission, $this->permissions);
    }
}
