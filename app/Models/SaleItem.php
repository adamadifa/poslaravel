<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'unit_id',
        'conversion_ratio',
        'quantity',
        'unit_price',
        'unit_cost',
        'discount_amount',
        'subtotal',
        'notes',
        'item_status',
        'prepared_at',
        'served_at',
    ];

    protected $casts = [
        'conversion_ratio' => 'decimal:4',
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'prepared_at' => 'datetime',
        'served_at' => 'datetime',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function modifiers(): HasMany
    {
        return $this->hasMany(SaleItemModifier::class);
    }

    public function assignment(): HasOne
    {
        return $this->hasOne(SaleItemAssignment::class);
    }
}
