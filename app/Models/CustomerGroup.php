<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerGroup extends Model
{
    protected $fillable = [
        'name',
        'discount_percent',
        'description',
    ];

    protected $casts = [
        'discount_percent' => 'decimal:2',
    ];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function tieredPrices(): HasMany
    {
        return $this->hasMany(TieredPrice::class);
    }
}
