<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use Auditable;

    protected $fillable = [
        'invoice_number',
        'cashier_shift_id',
        'warehouse_id',
        'user_id',
        'waiter_id',
        'customer_id',
        'sale_date',
        'service_type',
        'dining_table_id',
        'queue_number',
        'order_status',
        'guest_count',
        'service_booking_id',
        'assigned_staff_id',
        'service_status',
        'service_started_at',
        'service_completed_at',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'service_charge',
        'grand_total',
        'paid_amount',
        'change_amount',
        'payment_method',
        'payment_status',
        'status',
        'reference_number',
        'notes',
        'void_by',
        'void_at',
        'void_reason',
    ];

    protected $casts = [
        'sale_date' => 'datetime',
        'service_started_at' => 'datetime',
        'service_completed_at' => 'datetime',
        'guest_count' => 'integer',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'void_at' => 'datetime',
    ];

    public function cashierShift(): BelongsTo
    {
        return $this->belongsTo(CashierShift::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function waiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'waiter_id');
    }

    public function diningTable(): BelongsTo
    {
        return $this->belongsTo(DiningTable::class);
    }

    public function serviceBooking(): BelongsTo
    {
        return $this->belongsTo(ServiceBooking::class);
    }

    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function voidUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'void_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
