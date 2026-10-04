<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsignmentReturnItem extends Model
{
    protected $fillable = [
        'consignment_return_id',
        'product_id',
        'unit_id',
        'quantity',
        'conversion_ratio',
        'quantity_base',
        'reason',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'conversion_ratio' => 'decimal:4',
        'quantity_base' => 'decimal:4',
    ];

    public function return(): BelongsTo
    {
        return $this->belongsTo(ConsignmentReturn::class, 'consignment_return_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
