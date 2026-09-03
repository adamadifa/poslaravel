<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\PriceList;
use App\Models\Product;
use App\Models\TieredPrice;
use App\Models\UnitConversion;

class PricingService
{
    /**
     * Resolve final unit selling price and HPP based on Product, Unit, Quantity, and Customer.
     *
     * @param Product $product
     * @param int $unitId
     * @param float $quantity
     * @param Customer|null $customer
     * @return array
     */
    public function resolvePrice(Product $product, int $unitId, float $quantity = 1, ?Customer $customer = null): array
    {
        $baseUnitId = $product->base_unit_id;
        $customerGroupId = $customer?->customer_group_id;
        $groupDiscountPercent = $customer?->group?->discount_percent ?? 0;

        // 1. Calculate Conversion Ratio to Base Unit
        $conversionRatio = 1.0;
        if ($unitId !== $baseUnitId) {
            $conversion = UnitConversion::where('product_id', $product->id)
                ->where('from_unit_id', $unitId)
                ->where('to_unit_id', $baseUnitId)
                ->first();

            if ($conversion && $conversion->conversion_value > 0) {
                $conversionRatio = (float) $conversion->conversion_value;
            }
        }

        // 2. Determine Base Selling Price for this specific Unit
        // Priority A: Explicit PriceList row for this product & unit
        $priceListItem = PriceList::where('product_id', $product->id)
            ->where('unit_id', $unitId)
            ->first();

        if ($priceListItem && $priceListItem->selling_price > 0) {
            $unitSellingPrice = (float) $priceListItem->selling_price;
            $unitPurchasePrice = (float) $priceListItem->purchase_price;
        } else {
            // Priority B: Calculate from base product price multiplied by conversion ratio
            $unitSellingPrice = (float) $product->selling_price * $conversionRatio;
            $unitPurchasePrice = (float) $product->purchase_price * $conversionRatio;
        }

        // 3. Check for Tiered Pricing (Harga Berjenjang)
        $today = now()->toDateString();
        $tieredPrice = TieredPrice::where('product_id', $product->id)
            ->where('unit_id', $unitId)
            ->where('is_active', true)
            ->where('min_qty', '<=', $quantity)
            ->where(function ($q) use ($quantity) {
                $q->whereNull('max_qty')->orWhere('max_qty', '>=', $quantity);
            })
            ->where(function ($q) use ($customerGroupId) {
                $q->whereNull('customer_group_id')->orWhere('customer_group_id', $customerGroupId);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $today);
            })
            // Prioritize customer-group-specific rule over generic rule
            ->orderByRaw('customer_group_id IS NULL ASC')
            ->orderByDesc('min_qty')
            ->first();

        $isTieredApplied = false;
        if ($tieredPrice && $tieredPrice->price > 0) {
            $unitSellingPrice = (float) $tieredPrice->price;
            $isTieredApplied = true;
        }

        // 4. Apply Customer Group Member Discount (if not overridden by strict tiered pricing)
        $discountAmount = 0;
        if (!$isTieredApplied && $groupDiscountPercent > 0) {
            $discountAmount = ($unitSellingPrice * $groupDiscountPercent) / 100;
        }

        $finalUnitPrice = max(0, $unitSellingPrice - $discountAmount);
        $totalPrice = $finalUnitPrice * $quantity;

        return [
            'product_id' => $product->id,
            'unit_id' => $unitId,
            'quantity' => $quantity,
            'conversion_ratio' => $conversionRatio,
            'unit_purchase_price' => $unitPurchasePrice,
            'regular_unit_price' => $unitSellingPrice,
            'discount_percent' => $groupDiscountPercent,
            'discount_amount' => $discountAmount,
            'final_unit_price' => $finalUnitPrice,
            'total_price' => $totalPrice,
            'is_tiered_applied' => $isTieredApplied,
        ];
    }

    /**
     * Auto-sync/generate PriceList rows based on Product conversions.
     */
    public function syncPriceListsFromConversions(Product $product): void
    {
        $basePrice = (float) $product->selling_price;
        $baseHpp = (float) $product->purchase_price;

        // Ensure base unit is in price lists
        PriceList::updateOrCreate(
            ['product_id' => $product->id, 'unit_id' => $product->base_unit_id],
            ['purchase_price' => $baseHpp, 'selling_price' => $basePrice]
        );

        // Loop through each conversion
        foreach ($product->conversions as $conv) {
            $ratio = (float) $conv->conversion_value;
            if ($ratio > 0) {
                // If price list row does not exist, auto create proportionally
                PriceList::firstOrCreate(
                    ['product_id' => $product->id, 'unit_id' => $conv->from_unit_id],
                    [
                        'purchase_price' => $baseHpp * $ratio,
                        'selling_price' => $basePrice * $ratio,
                    ]
                );
            }
        }
    }
}
