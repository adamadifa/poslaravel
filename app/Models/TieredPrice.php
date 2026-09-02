<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TieredPrice extends Model
{
    protected $fillable = [
        'product_id',
        'unit_id',
        'customer_group_id',
        'min_qty',
        'max_qty',
        'price',
        'is_active',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'min_qty' => 'decimal:4',
        'max_qty' => 'decimal:4',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function customerGroup(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class);
    }
}
