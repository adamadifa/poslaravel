<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashierShift extends Model
{
    protected $fillable = [
        'user_id',
        'warehouse_id',
        'opened_at',
        'closed_at',
        'starting_cash',
        'expected_cash',
        'closing_cash',
        'cash_difference',
        'total_sales',
        'total_transactions',
        'total_expenses',
        'total_agent_cash_in',
        'total_agent_cash_out',
        'total_agent_profit',
        'status',
        'notes',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'starting_cash' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'closing_cash' => 'decimal:2',
        'cash_difference' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'total_transactions' => 'integer',
        'total_expenses' => 'decimal:2',
        'total_agent_cash_in' => 'decimal:2',
        'total_agent_cash_out' => 'decimal:2',
        'total_agent_profit' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(CashierShiftExpense::class);
    }

    public function agentTransactions(): HasMany
    {
        return $this->hasMany(AgentTransaction::class);
    }
}
