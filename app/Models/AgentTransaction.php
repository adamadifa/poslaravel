<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentTransaction extends Model
{
    protected $fillable = [
        'transaction_number',
        'cashier_shift_id',
        'user_id',
        'account_id',
        'category',
        'service_type',
        'destination_target',
        'destination_holder',
        'principal_amount',
        'cost_price',
        'admin_fee',
        'total_customer_paid',
        'net_profit',
        'payment_method',
        'reference_number',
        'status',
        'notes',
    ];

    protected $casts = [
        'principal_amount' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'admin_fee' => 'decimal:2',
        'total_customer_paid' => 'decimal:2',
        'net_profit' => 'decimal:2',
    ];

    public function cashierShift(): BelongsTo
    {
        return $this->belongsTo(CashierShift::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
