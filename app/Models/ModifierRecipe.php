<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModifierRecipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'modifier_id',
        'ingredient_product_id',
        'quantity',
        'unit_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
        ];
    }

    public function modifier(): BelongsTo
    {
        return $this->belongsTo(Modifier::class, 'modifier_id');
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'ingredient_product_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}
