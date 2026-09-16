<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransferItem extends Model
{
    protected $fillable = [
        'stock_transfer_id',
        'product_id',
        'unit_id',
        'quantity_sent',
        'quantity_received',
        'base_quantity_sent',
        'base_quantity_received',
        'unit_cost',
        'batch_number',
    ];

    protected $casts = [
        'quantity_sent' => 'float',
        'quantity_received' => 'float',
        'base_quantity_sent' => 'float',
        'base_quantity_received' => 'float',
        'unit_cost' => 'decimal:2',
    ];

    public function stockTransfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class);
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
