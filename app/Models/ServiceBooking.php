<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_number',
        'warehouse_id',
        'customer_id',
        'guest_name',
        'guest_phone',
        'booking_date',
        'booking_time',
        'estimated_duration_minutes',
        'status',
        'sale_id',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'estimated_duration_minutes' => 'integer',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ServiceBookingItem::class);
    }
}
