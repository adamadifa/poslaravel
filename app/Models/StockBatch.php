<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockBatch extends Model
{
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'purchase_receipt_item_id',
        'batch_number',
        'expiry_date',
        'qty_in',
        'qty_remaining',
        'unit_cost',
        'entry_date',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'entry_date' => 'date',
        'qty_in' => 'decimal:4',
        'qty_remaining' => 'decimal:4',
        'unit_cost' => 'decimal:4',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function receiptItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseReceiptItem::class, 'purchase_receipt_item_id');
    }
}
