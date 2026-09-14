<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Modifier extends Model
{
    use HasFactory;

    protected $fillable = [
        'modifier_group_id',
        'name',
        'price_adjustment',
        'is_default',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_adjustment' => 'decimal:2',
            'is_default' => 'boolean',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(ModifierGroup::class, 'modifier_group_id');
    }

    public function recipes(): HasMany
    {
        return $this->hasMany(ModifierRecipe::class, 'modifier_id')->with(['ingredient.baseUnit', 'unit']);
    }
}
