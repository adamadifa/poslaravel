<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConsignmentReceipt extends Model
{
    protected $fillable = [
        'receipt_number',
        'supplier_id',
        'warehouse_id',
        'user_id',
        'receipt_date',
        'status',
        'total_items',
        'total_quantity',
        'total_estimated_value',
        'notes',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'total_items' => 'integer',
        'total_quantity' => 'decimal:4',
        'total_estimated_value' => 'decimal:2',
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

    public function items(): HasMany
    {
        return $this->hasMany(ConsignmentReceiptItem::class);
    }
}
