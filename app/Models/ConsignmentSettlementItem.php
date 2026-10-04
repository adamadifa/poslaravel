<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsignmentSettlementItem extends Model
{
    protected $fillable = [
        'consignment_settlement_id',
        'product_id',
        'unit_id',
        'quantity_sold',
        'selling_price',
        'consignment_cost',
        'gross_sales',
        'supplier_payable',
        'store_commission',
    ];

    protected $casts = [
        'quantity_sold' => 'decimal:4',
        'selling_price' => 'decimal:2',
        'consignment_cost' => 'decimal:2',
        'gross_sales' => 'decimal:2',
        'supplier_payable' => 'decimal:2',
        'store_commission' => 'decimal:2',
    ];

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(ConsignmentSettlement::class, 'consignment_settlement_id');
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
