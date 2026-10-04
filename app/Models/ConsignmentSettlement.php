<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConsignmentSettlement extends Model
{
    protected $fillable = [
        'settlement_number',
        'supplier_id',
        'warehouse_id',
        'user_id',
        'start_date',
        'end_date',
        'total_sold_quantity',
        'total_gross_sales',
        'total_supplier_amount',
        'total_store_commission',
        'paid_amount',
        'payment_status',
        'payment_method',
        'payment_account_id',
        'payment_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'payment_date' => 'date',
        'total_sold_quantity' => 'decimal:4',
        'total_gross_sales' => 'decimal:2',
        'total_supplier_amount' => 'decimal:2',
        'total_store_commission' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'payment_account_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ConsignmentSettlementItem::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class, 'consignment_settlement_id');
    }
}
