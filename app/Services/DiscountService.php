<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Discount;
use App\Models\Product;

class DiscountService
{
    /**
     * Resolve all active discounts applicable for given cart items and customer.
     *
     * @param array $cartItems Array of items: [['product_id' => 1, 'unit_id' => 1, 'quantity' => 2, 'price' => 3500], ...]
     * @param Customer|null $customer
     * @param string|null $promoCode Optional coupon/promo code
     * @return array
     */
    public function calculateCartDiscounts(array $cartItems, ?Customer $customer = null, ?string $promoCode = null): array
    {
        $subtotal = 0;
        $itemsByProduct = [];

        foreach ($cartItems as $index => $item) {
            $lineTotal = (float) $item['price'] * (float) $item['quantity'];
            $subtotal += $lineTotal;

            $pId = $item['product_id'];
            if (!isset($itemsByProduct[$pId])) {
                $itemsByProduct[$pId] = [
                    'quantity' => 0,
                    'total_amount' => 0,
                    'indices' => [],
                ];
            }
            $itemsByProduct[$pId]['quantity'] += (float) $item['quantity'];
            $itemsByProduct[$pId]['total_amount'] += $lineTotal;
            $itemsByProduct[$pId]['indices'][] = $index;
        }

        $today = now()->toDateString();
        $currentTime = now()->format('H:i:s');
        $customerGroupId = $customer?->customer_group_id;

        // Query active discounts
        $query = Discount::with(['items', 'rewardProduct'])
            ->where('is_active', true)
            ->where(function ($q) use ($today) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $today);
            })
            ->where(function ($q) use ($customerGroupId) {
                $q->whereNull('customer_group_id')->orWhere('customer_group_id', $customerGroupId);
            });

        if ($promoCode) {
            $query->where(function ($q) use ($promoCode) {
                $q->whereNull('code')->orWhere('code', $promoCode);
            });
        }

        $discounts = $query->get();

        $itemDiscounts = [];
        $invoiceDiscounts = [];
        $freeRewards = [];
        $totalDiscountAmount = 0;

        foreach ($discounts as $discount) {
            // Check minimum order amount if set
            if ($discount->min_order_amount && $subtotal < (float) $discount->min_order_amount) {
                continue;
            }

            // Check time restriction
            if ($discount->start_time && $currentTime < $discount->start_time) {
                continue;
            }
            if ($discount->end_time && $currentTime > $discount->end_time) {
                continue;
            }

            // Process based on type
            switch ($discount->type) {
                case 'percentage_item':
                case 'fixed_item':
                    $eligibleProductIds = $discount->items->pluck('product_id')->toArray();
                    $applyToAll = empty($eligibleProductIds);

                    foreach ($cartItems as $idx => $cartItem) {
                        if ($applyToAll || in_array($cartItem['product_id'], $eligibleProductIds)) {
                            $itemPrice = (float) $cartItem['price'];
                            $itemQty = (float) $cartItem['quantity'];
                            $lineSubtotal = $itemPrice * $itemQty;

                            if ($discount->type === 'percentage_item') {
                                $disc = ($lineSubtotal * (float) $discount->value) / 100;
                            } else {
                                $disc = min((float) $discount->value * $itemQty, $lineSubtotal);
                            }

                            if ($discount->max_discount_amount) {
                                $disc = min($disc, (float) $discount->max_discount_amount);
                            }

                            $itemDiscounts[] = [
                                'cart_index' => $idx,
                                'discount_id' => $discount->id,
                                'discount_name' => $discount->name,
                                'amount' => $disc,
                            ];
                            $totalDiscountAmount += $disc;
                        }
                    }
                    break;

                case 'percentage_invoice':
                    $disc = ($subtotal * (float) $discount->value) / 100;
                    if ($discount->max_discount_amount) {
                        $disc = min($disc, (float) $discount->max_discount_amount);
                    }
                    $invoiceDiscounts[] = [
                        'discount_id' => $discount->id,
                        'discount_name' => $discount->name,
                        'type' => 'percentage',
                        'value' => (float) $discount->value,
                        'amount' => $disc,
                    ];
                    $totalDiscountAmount += $disc;
                    break;

                case 'fixed_invoice':
                    $disc = min((float) $discount->value, $subtotal);
                    $invoiceDiscounts[] = [
                        'discount_id' => $discount->id,
                        'discount_name' => $discount->name,
                        'type' => 'fixed',
                        'value' => (float) $discount->value,
                        'amount' => $disc,
                    ];
                    $totalDiscountAmount += $disc;
                    break;

                case 'buy_x_get_y':
                    $eligibleProductIds = $discount->items->pluck('product_id')->toArray();
                    $buyQty = (float) $discount->buy_qty ?: 1;
                    $getQty = (float) $discount->get_qty ?: 1;

                    foreach ($eligibleProductIds as $pId) {
                        if (isset($itemsByProduct[$pId])) {
                            $currentQty = $itemsByProduct[$pId]['quantity'];
                            if ($currentQty >= $buyQty) {
                                $multiples = floor($currentQty / $buyQty);
                                $totalRewardQty = $multiples * $getQty;

                                $rewardProduct = $discount->rewardProduct ?: Product::find($pId);
                                if ($rewardProduct) {
                                    $freeRewards[] = [
                                        'discount_id' => $discount->id,
                                        'discount_name' => $discount->name,
                                        'product_id' => $rewardProduct->id,
                                        'product_name' => $rewardProduct->name,
                                        'quantity' => $totalRewardQty,
                                    ];
                                }
                            }
                        }
                    }
                    break;
            }
        }

        $finalGrandTotal = max(0, $subtotal - $totalDiscountAmount);

        return [
            'subtotal' => $subtotal,
            'total_discount' => $totalDiscountAmount,
            'grand_total' => $finalGrandTotal,
            'item_discounts' => $itemDiscounts,
            'invoice_discounts' => $invoiceDiscounts,
            'free_rewards' => $freeRewards,
        ];
    }
}
