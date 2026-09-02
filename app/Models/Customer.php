<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    protected $fillable = [
        'code',
        'name',
        'customer_group_id',
        'phone',
        'email',
        'address',
        'city',
        'tax_id',
        'credit_limit',
        'loyalty_points',
        'is_active',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'loyalty_points' => 'integer',
        'is_active' => 'boolean',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class, 'customer_group_id');
    }
}
