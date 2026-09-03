<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Discount extends Model
{
    protected $fillable = [
        'name',
        'code',
        'type',
        'value',
        'min_order_amount',
        'max_discount_amount',
        'buy_qty',
        'get_qty',
        'reward_product_id',
        'customer_group_id',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'is_combinable',
        'is_active',
        'description',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'buy_qty' => 'decimal:4',
        'get_qty' => 'decimal:4',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_combinable' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function customerGroup(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class);
    }

    public function rewardProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'reward_product_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DiscountItem::class);
    }
}
