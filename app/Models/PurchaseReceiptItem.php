<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseReceiptItem extends Model
{
    protected $fillable = [
        'purchase_receipt_id',
        'purchase_order_item_id',
        'product_id',
        'unit_id',
        'quantity_received',
        'base_quantity',
        'unit_cost',
        'base_unit_cost',
        'subtotal',
        'batch_number',
        'expiry_date',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'quantity_received' => 'decimal:4',
        'base_quantity' => 'decimal:4',
        'unit_cost' => 'decimal:2',
        'base_unit_cost' => 'decimal:4',
        'subtotal' => 'decimal:2',
    ];

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(PurchaseReceipt::class, 'purchase_receipt_id');
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
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
