<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsignmentReceiptItem extends Model
{
    protected $fillable = [
        'consignment_receipt_id',
        'product_id',
        'unit_id',
        'quantity',
        'conversion_ratio',
        'quantity_base',
        'consignment_cost',
        'subtotal',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'conversion_ratio' => 'decimal:4',
        'quantity_base' => 'decimal:4',
        'consignment_cost' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(ConsignmentReceipt::class, 'consignment_receipt_id');
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
