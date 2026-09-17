<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [
        'account_code',
        'name',
        'type',
        'account_number',
        'account_holder',
        'bank_name',
        'opening_balance',
        'current_balance',
        'alert_minimum_balance',
        'is_default',
        'is_active',
        'description',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'alert_minimum_balance' => 'decimal:2',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function mutations()
    {
        return $this->hasMany(AccountMutation::class);
    }

    public function agentTransactions()
    {
        return $this->hasMany(AgentTransaction::class);
    }

    public function isLowBalance(): bool
    {
        return (float) $this->alert_minimum_balance > 0 && (float) $this->current_balance <= (float) $this->alert_minimum_balance;
    }

    public function isBankAgent(): bool
    {
        return $this->type === 'bank_agent';
    }

    public function isPpobProvider(): bool
    {
        return $this->type === 'ppob_provider';
    }
}
