<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'category_id',
        'base_unit_id',
        'code',
        'barcode',
        'name',
        'product_type',
        'duration_minutes',
        'is_bookable',
        'require_staff_assignment',
        'max_concurrent',
        'slug',
        'brand',
        'description',
        'purchase_price',
        'selling_price',
        'min_stock',
        'max_stock',
        'tax_type',
        'tax_rate',
        'has_expiry',
        'is_active',
        'image_path',
        'default_notes',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'is_bookable' => 'boolean',
        'require_staff_assignment' => 'boolean',
        'max_concurrent' => 'integer',
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'min_stock' => 'decimal:4',
        'max_stock' => 'decimal:4',
        'tax_rate' => 'decimal:2',
        'has_expiry' => 'boolean',
        'is_active' => 'boolean',
        'default_notes' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    public function barcodes(): HasMany
    {
        return $this->hasMany(ProductBarcode::class);
    }

    public function conversions(): HasMany
    {
        return $this->hasMany(UnitConversion::class);
    }

    public function priceLists(): HasMany
    {
        return $this->hasMany(PriceList::class);
    }

    public function tieredPrices(): HasMany
    {
        return $this->hasMany(TieredPrice::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }

    public function modifierGroups(): BelongsToMany
    {
        return $this->belongsToMany(ModifierGroup::class, 'product_modifier_groups')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    public function serviceStaff(): HasMany
    {
        return $this->hasMany(ServiceStaff::class);
    }

    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class, 'product_id')->with(['ingredient.baseUnit', 'unit']);
    }

    public function usedInRecipes(): HasMany
    {
        return $this->hasMany(Recipe::class, 'ingredient_product_id');
    }

    /**
     * Calculate total recipe cost (HPP) based on all recipe ingredients.
     */
    public function calculateRecipeHpp(): float
    {
        $totalCost = 0;
        foreach ($this->recipes as $recipe) {
            $totalCost += $recipe->calculateCost();
        }

        return $totalCost;
    }

    public function scopeServices($query)
    {
        return $query->where('product_type', 'service');
    }

    public function scopeFnb($query)
    {
        return $query->whereIn('product_type', ['food', 'beverage']);
    }

    public function scopeRawMaterials($query)
    {
        return $query->where('product_type', 'raw_material');
    }

    public function scopeForSale($query)
    {
        return $query->where('product_type', '!=', 'raw_material');
    }
}
