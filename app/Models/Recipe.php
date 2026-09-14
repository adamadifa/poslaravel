<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'ingredient_product_id',
        'quantity',
        'unit_id',
        'waste_percent',
        'cost_estimate',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'waste_percent' => 'decimal:2',
            'cost_estimate' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'ingredient_product_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * Calculate estimated cost for this recipe ingredient.
     */
    public function calculateCost(): float
    {
        $ingredient = $this->ingredient ?? Product::find($this->ingredient_product_id);
        if (! $ingredient) {
            return 0;
        }

        $conversionRatio = 1.0;
        if ($this->unit_id !== $ingredient->base_unit_id) {
            $conv = UnitConversion::where('product_id', $ingredient->id)
                ->where('from_unit_id', $this->unit_id)
                ->where('to_unit_id', $ingredient->base_unit_id)
                ->first();

            if ($conv && (float) $conv->conversion_value > 0) {
                $conversionRatio = (float) $conv->conversion_value;
            } else {
                // Check reverse conversion
                $reverseConv = UnitConversion::where('product_id', $ingredient->id)
                    ->where('from_unit_id', $ingredient->base_unit_id)
                    ->where('to_unit_id', $this->unit_id)
                    ->first();

                if ($reverseConv && (float) $reverseConv->conversion_value > 0) {
                    $conversionRatio = 1 / (float) $reverseConv->conversion_value;
                }
            }
        }

        $qtyInBaseUnit = (float) $this->quantity * $conversionRatio;
        $wasteMultiplier = 1 + ((float) $this->waste_percent / 100);
        $totalBaseQty = $qtyInBaseUnit * $wasteMultiplier;

        return $totalBaseQty * (float) $ingredient->purchase_price;
    }
}
