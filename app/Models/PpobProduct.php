<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpobProduct extends Model
{
    protected $fillable = [
        'category',
        'provider',
        'code',
        'name',
        'cost_price',
        'selling_price',
        'default_account_id',
        'is_active',
        'description',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function defaultAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'default_account_id');
    }

    /**
     * Calculate net margin amount.
     */
    public function getMarginAttribute(): float
    {
        return (float) ($this->selling_price - $this->cost_price);
    }
}
