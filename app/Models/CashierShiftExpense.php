<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashierShiftExpense extends Model
{
    protected $fillable = [
        'cashier_shift_id',
        'user_id',
        'warehouse_id',
        'amount',
        'category',
        'notes',
        'expense_date',
        'cash_flow_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'datetime',
    ];

    public function cashierShift(): BelongsTo
    {
        return $this->belongsTo(CashierShift::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function cashFlow(): BelongsTo
    {
        return $this->belongsTo(CashFlow::class);
    }
}
