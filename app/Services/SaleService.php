<?php

namespace App\Services;

use App\Models\CashierShift;
use App\Models\Customer;
use App\Models\DiningTable;
use App\Models\Modifier;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleItemAssignment;
use App\Models\SaleItemModifier;
use App\Models\UnitConversion;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class SaleService
{
    protected StockService $stockService;

    protected PricingService $pricingService;

    protected DiscountService $discountService;

    public function __construct(
        StockService $stockService,
        PricingService $pricingService,
        DiscountService $discountService
    ) {
        $this->stockService = $stockService;
        $this->pricingService = $pricingService;
        $this->discountService = $discountService;
    }

    /**
     * Generate next sequential invoice number (e.g., INV-2026-09-0001)
     */
    public function generateInvoiceNumber(): string
    {
        $yearMonth = now()->format('Y-m');
        $prefix = "INV-{$yearMonth}-";

        $lastSale = Sale::where('invoice_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->first();

        if ($lastSale) {
            $lastNumber = (int) substr($lastSale->invoice_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Complete a POS Sale transaction in a DB Transaction.
     */
    public function processSale(array $payload): Sale
    {
        return DB::transaction(function () use ($payload) {
            $userId = auth()->id();
            $warehouseId = $payload['warehouse_id'] ?? Warehouse::where('is_default', true)->value('id') ?? 1;

            // Get active shift
            $shift = CashierShift::where('user_id', $userId)
                ->where('warehouse_id', $warehouseId)
                ->where('status', 'open')
                ->first();

            $customer = ! empty($payload['customer_id']) ? Customer::with('group')->find($payload['customer_id']) : null;
            $items = $payload['items'] ?? [];
            $promoCode = $payload['promo_code'] ?? null;

            // 1. Calculate discount through DiscountService
            $discResult = $this->discountService->calculateCartDiscounts($items, $customer, $promoCode);

            $subtotal = $discResult['subtotal'];
            $discountAmount = $discResult['total_discount'] + (float) ($payload['manual_discount'] ?? 0);
            $taxAmount = (float) ($payload['tax_amount'] ?? 0);
            $serviceCharge = (float) ($payload['service_charge'] ?? 0);
            $grandTotal = max(0, $subtotal - $discountAmount + $taxAmount + $serviceCharge);

            $paidAmount = (float) ($payload['paid_amount'] ?? $grandTotal);
            $changeAmount = max(0, $paidAmount - $grandTotal);
            $paymentMethod = $payload['payment_method'] ?? 'cash';

            $paymentStatus = 'paid';
            if ($paymentMethod === 'credit' || $paidAmount < $grandTotal) {
                $paymentStatus = $paidAmount == 0 ? 'unpaid' : 'partial';
            }

            // 2. Create Sale Record
            $sale = Sale::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'cashier_shift_id' => $shift?->id,
                'warehouse_id' => $warehouseId,
                'user_id' => $userId,
                'waiter_id' => $payload['waiter_id'] ?? null,
                'customer_id' => $customer?->id,
                'sale_date' => now(),
                'service_type' => $payload['service_type'] ?? null,
                'dining_table_id' => $payload['dining_table_id'] ?? null,
                'queue_number' => $payload['queue_number'] ?? null,
                'order_status' => $payload['order_status'] ?? 'completed',
                'guest_count' => $payload['guest_count'] ?? null,
                'service_booking_id' => $payload['service_booking_id'] ?? null,
                'assigned_staff_id' => $payload['assigned_staff_id'] ?? null,
                'service_status' => $payload['service_status'] ?? null,
                'service_started_at' => ! empty($payload['service_status']) && $payload['service_status'] === 'in_progress' ? now() : null,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'service_charge' => $serviceCharge,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'status' => 'completed',
                'reference_number' => $payload['reference_number'] ?? null,
                'notes' => $payload['notes'] ?? null,
            ]);

            // If Dine-In table is attached, update table status
            if (! empty($payload['dining_table_id'])) {
                $table = DiningTable::find($payload['dining_table_id']);
                if ($table) {
                    $table->update([
                        'status' => ($payload['order_status'] ?? 'completed') === 'completed' ? 'available' : 'occupied',
                        'current_sale_id' => ($payload['order_status'] ?? 'completed') === 'completed' ? null : $sale->id,
                    ]);
                }
            }

            // 3. Process Sale Items and Realtime Stock Deduction
            foreach ($items as $idx => $item) {
                $product = Product::findOrFail($item['product_id']);
                $unitId = (int) ($item['unit_id'] ?? $product->base_unit_id);
                $qty = (float) $item['quantity'];
                $unitPrice = (float) $item['price'];

                // Calculate ratio to base unit
                $conversionRatio = 1.0;
                if ($unitId !== $product->base_unit_id) {
                    $conv = UnitConversion::where('product_id', $product->id)
                        ->where('from_unit_id', $unitId)
                        ->where('to_unit_id', $product->base_unit_id)
                        ->first();
                    if ($conv && $conv->conversion_value > 0) {
                        $conversionRatio = (float) $conv->conversion_value;
                    }
                }

                $qtyInBaseUnit = $qty * $conversionRatio;
                $unitCost = (float) $product->purchase_price * $conversionRatio;

                // Item specific discount
                $itemDiscount = 0;
                foreach ($discResult['item_discounts'] as $idisc) {
                    if ($idisc['cart_index'] === $idx) {
                        $itemDiscount += (float) $idisc['amount'];
                    }
                }

                $lineSubtotal = ($unitPrice * $qty) - $itemDiscount;

                $saleItem = SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'unit_id' => $unitId,
                    'conversion_ratio' => $conversionRatio,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'unit_cost' => $unitCost,
                    'discount_amount' => $itemDiscount,
                    'subtotal' => $lineSubtotal,
                    'notes' => $item['notes'] ?? null,
                    'item_status' => ($payload['order_status'] ?? 'completed') === 'completed' ? 'served' : 'pending',
                ]);

                // Modifiers attachments
                if (! empty($item['modifiers']) && is_array($item['modifiers'])) {
                    foreach ($item['modifiers'] as $mod) {
                        SaleItemModifier::create([
                            'sale_item_id' => $saleItem->id,
                            'modifier_id' => $mod['id'] ?? null,
                            'modifier_name' => $mod['name'] ?? 'Modifier',
                            'price_adjustment' => (float) ($mod['price_adjustment'] ?? 0),
                        ]);
                    }
                }

                // Service technician assignment
                if (! empty($item['staff_user_id']) || ! empty($payload['assigned_staff_id'])) {
                    SaleItemAssignment::create([
                        'sale_item_id' => $saleItem->id,
                        'staff_user_id' => $item['staff_user_id'] ?? $payload['assigned_staff_id'],
                        'status' => 'assigned',
                    ]);
                }

                // Deduct stock: If product has recipes, deduct raw materials; otherwise deduct product itself (skip for services)
                if ($product->product_type !== 'service') {
                    $product->loadMissing(['recipes.ingredient', 'recipes.unit']);

                    if ($product->recipes->isNotEmpty()) {
                        $totalRecipeCogs = 0;

                        foreach ($product->recipes as $recipe) {
                            $ing = $recipe->ingredient;
                            if (! $ing) {
                                continue;
                            }

                            $ingRatio = 1.0;
                            if ($recipe->unit_id !== $ing->base_unit_id) {
                                $conv = UnitConversion::where('product_id', $ing->id)
                                    ->where('from_unit_id', $recipe->unit_id)
                                    ->where('to_unit_id', $ing->base_unit_id)
                                    ->first();
                                if ($conv && (float) $conv->conversion_value > 0) {
                                    $ingRatio = (float) $conv->conversion_value;
                                } else {
                                    $revConv = UnitConversion::where('product_id', $ing->id)
                                        ->where('from_unit_id', $ing->base_unit_id)
                                        ->where('to_unit_id', $recipe->unit_id)
                                        ->first();
                                    if ($revConv && (float) $revConv->conversion_value > 0) {
                                        $ingRatio = 1 / (float) $revConv->conversion_value;
                                    }
                                }
                            }

                            $wasteMultiplier = 1 + ((float) $recipe->waste_percent / 100);
                            $requiredIngQtyInBase = ((float) $recipe->quantity * $wasteMultiplier * $ingRatio) * $qtyInBaseUnit;

                            $ingFifoCogs = $this->stockService->consumeFifoBatches($ing->id, $warehouseId, $requiredIngQtyInBase);
                            $ingUnitCost = $requiredIngQtyInBase > 0 ? ($ingFifoCogs / $requiredIngQtyInBase) : (float) $ing->purchase_price;
                            $totalRecipeCogs += $ingFifoCogs;

                            $this->stockService->deductStock(
                                $ing->id,
                                $warehouseId,
                                $requiredIngQtyInBase,
                                'SaleRecipe',
                                $sale->id,
                                $ingUnitCost,
                                "Bahan Baku Resep ({$product->name} x {$qty}): {$ing->name} - Faktur {$sale->invoice_number}",
                                $userId
                            );
                        }

                        // Update unit_cost on saleItem with total recipe COGS if calculated
                        if ($qtyInBaseUnit > 0 && $totalRecipeCogs > 0) {
                            $saleItem->update(['unit_cost' => $totalRecipeCogs / $qtyInBaseUnit]);
                        }
                    } else {
                        // Standard finished good product deduction
                        $fifoCogs = $this->stockService->consumeFifoBatches($product->id, $warehouseId, $qtyInBaseUnit);
                        $effectiveUnitCost = $qtyInBaseUnit > 0 ? ($fifoCogs / $qtyInBaseUnit) : $unitCost;

                        $this->stockService->deductStock(
                            $product->id,
                            $warehouseId,
                            $qtyInBaseUnit,
                            'Sale',
                            $sale->id,
                            $effectiveUnitCost,
                            "Penjualan Kasir POS: {$sale->invoice_number}",
                            $userId
                        );
                    }
                }

                // Check and deduct stock for modifier recipes if any
                if (! empty($item['modifiers']) && is_array($item['modifiers'])) {
                    foreach ($item['modifiers'] as $mod) {
                        $modId = $mod['id'] ?? null;
                        if ($modId) {
                            $modModel = Modifier::with('recipes.ingredient')->find($modId);
                            if ($modModel && $modModel->recipes->isNotEmpty()) {
                                foreach ($modModel->recipes as $modRecipe) {
                                    $modIng = $modRecipe->ingredient;
                                    if (! $modIng) {
                                        continue;
                                    }

                                    $modIngRatio = 1.0;
                                    if ($modRecipe->unit_id !== $modIng->base_unit_id) {
                                        $conv = UnitConversion::where('product_id', $modIng->id)
                                            ->where('from_unit_id', $modRecipe->unit_id)
                                            ->where('to_unit_id', $modIng->base_unit_id)
                                            ->first();
                                        if ($conv && (float) $conv->conversion_value > 0) {
                                            $modIngRatio = (float) $conv->conversion_value;
                                        }
                                    }

                                    $requiredModIngQty = ((float) $modRecipe->quantity * $modIngRatio) * $qtyInBaseUnit;
                                    $modIngFifoCogs = $this->stockService->consumeFifoBatches($modIng->id, $warehouseId, $requiredModIngQty);
                                    $modIngUnitCost = $requiredModIngQty > 0 ? ($modIngFifoCogs / $requiredModIngQty) : (float) $modIng->purchase_price;

                                    $this->stockService->deductStock(
                                        $modIng->id,
                                        $warehouseId,
                                        $requiredModIngQty,
                                        'SaleModifierRecipe',
                                        $sale->id,
                                        $modIngUnitCost,
                                        "Bahan Baku Topping ({$modModel->name} x {$qty}): {$modIng->name} - Faktur {$sale->invoice_number}",
                                        $userId
                                    );
                                }
                            }
                        }
                    }
                }
            }

            // Process Free Rewards (Buy X Get Y)
            if (! empty($discResult['free_rewards'])) {
                foreach ($discResult['free_rewards'] as $reward) {
                    $rewardProd = Product::find($reward['product_id']);
                    if ($rewardProd) {
                        $rewardQty = (float) $reward['quantity'];
                        $rewardUnitId = $rewardProd->base_unit_id;
                        $unitCost = (float) $rewardProd->purchase_price;

                        SaleItem::create([
                            'sale_id' => $sale->id,
                            'product_id' => $rewardProd->id,
                            'unit_id' => $rewardUnitId,
                            'conversion_ratio' => 1.0,
                            'quantity' => $rewardQty,
                            'unit_price' => 0,
                            'unit_cost' => $unitCost,
                            'discount_amount' => 0,
                            'subtotal' => 0,
                        ]);

                        $fifoCogs = $this->stockService->consumeFifoBatches($rewardProd->id, $warehouseId, $rewardQty);
                        $effectiveUnitCost = $rewardQty > 0 ? ($fifoCogs / $rewardQty) : $unitCost;

                        $this->stockService->deductStock(
                            $rewardProd->id,
                            $warehouseId,
                            $rewardQty,
                            'Sale',
                            $sale->id,
                            $effectiveUnitCost,
                            "Bonus Promo {$reward['discount_name']} - Faktur {$sale->invoice_number}",
                            $userId
                        );
                    }
                }
            }

            // 4. Update Shift Totals if Shift is open
            if ($shift) {
                $shift->total_transactions += 1;
                $shift->total_sales += $grandTotal;
                if ($paymentMethod === 'cash') {
                    $shift->expected_cash += min($paidAmount, $grandTotal);
                }
                $shift->save();
            }

            return $sale->load(['items.product', 'items.unit', 'customer.group', 'user', 'warehouse']);
        });
    }

    /**
     * Void an existing completed sale transaction.
     */
    public function voidSale(Sale $sale, string $reason, int $voidUserId): Sale
    {
        return DB::transaction(function () use ($sale, $reason, $voidUserId) {
            if ($sale->status === 'void') {
                throw new \Exception('Transaksi ini sudah berstatus VOID.');
            }

            // Return stock for each item (check if item has recipe or direct stock)
            foreach ($sale->items as $item) {
                $product = $item->product ?? Product::find($item->product_id);
                if (! $product || $product->product_type === 'service') {
                    continue;
                }

                $product->loadMissing(['recipes.ingredient', 'recipes.unit']);
                $qtyInBaseUnit = (float) $item->quantity * (float) $item->conversion_ratio;

                if ($product->recipes->isNotEmpty()) {
                    foreach ($product->recipes as $recipe) {
                        $ing = $recipe->ingredient;
                        if (! $ing) {
                            continue;
                        }

                        $ingRatio = 1.0;
                        if ($recipe->unit_id !== $ing->base_unit_id) {
                            $conv = UnitConversion::where('product_id', $ing->id)
                                ->where('from_unit_id', $recipe->unit_id)
                                ->where('to_unit_id', $ing->base_unit_id)
                                ->first();
                            if ($conv && (float) $conv->conversion_value > 0) {
                                $ingRatio = (float) $conv->conversion_value;
                            }
                        }

                        $wasteMultiplier = 1 + ((float) $recipe->waste_percent / 100);
                        $returnedIngQty = ((float) $recipe->quantity * $wasteMultiplier * $ingRatio) * $qtyInBaseUnit;

                        $this->stockService->addStock(
                            $ing->id,
                            $sale->warehouse_id,
                            $returnedIngQty,
                            'SaleVoidRecipe',
                            $sale->id,
                            (float) $ing->purchase_price,
                            "Void Penjualan (Kembali Bahan Baku {$product->name}): {$ing->name} - {$reason}",
                            $voidUserId
                        );
                    }
                } else {
                    $this->stockService->addStock(
                        $item->product_id,
                        $sale->warehouse_id,
                        $qtyInBaseUnit,
                        'SaleVoid',
                        $sale->id,
                        $item->unit_cost,
                        "Void Penjualan Faktur {$sale->invoice_number}: {$reason}",
                        $voidUserId
                    );
                }

                // Return stock for modifiers with recipes
                $item->loadMissing('modifiers');
                if ($item->modifiers) {
                    foreach ($item->modifiers as $mod) {
                        if ($mod->modifier_id) {
                            $modModel = Modifier::with('recipes.ingredient')->find($mod->modifier_id);
                            if ($modModel && $modModel->recipes->isNotEmpty()) {
                                foreach ($modModel->recipes as $modRecipe) {
                                    $modIng = $modRecipe->ingredient;
                                    if (! $modIng) {
                                        continue;
                                    }

                                    $modIngRatio = 1.0;
                                    if ($modRecipe->unit_id !== $modIng->base_unit_id) {
                                        $conv = UnitConversion::where('product_id', $modIng->id)
                                            ->where('from_unit_id', $modRecipe->unit_id)
                                            ->where('to_unit_id', $modIng->base_unit_id)
                                            ->first();
                                        if ($conv && (float) $conv->conversion_value > 0) {
                                            $modIngRatio = (float) $conv->conversion_value;
                                        }
                                    }

                                    $returnedModIngQty = ((float) $modRecipe->quantity * $modIngRatio) * $qtyInBaseUnit;
                                    $this->stockService->addStock(
                                        $modIng->id,
                                        $sale->warehouse_id,
                                        $returnedModIngQty,
                                        'SaleVoidModifierRecipe',
                                        $sale->id,
                                        (float) $modIng->purchase_price,
                                        "Void Penjualan (Kembali Bahan Topping {$modModel->name}): {$modIng->name} - {$reason}",
                                        $voidUserId
                                    );
                                }
                            }
                        }
                    }
                }
            }

            // Revert shift expected cash if part of a shift and cash
            if ($sale->cashier_shift_id && $sale->payment_method === 'cash') {
                $shift = CashierShift::find($sale->cashier_shift_id);
                if ($shift && $shift->status === 'open') {
                    $shift->total_sales -= $sale->grand_total;
                    $shift->total_transactions = max(0, $shift->total_transactions - 1);
                    $shift->expected_cash -= min($sale->paid_amount, $sale->grand_total);
                    $shift->save();
                }
            }

            $sale->update([
                'status' => 'void',
                'void_by' => $voidUserId,
                'void_at' => now(),
                'void_reason' => $reason,
            ]);

            return $sale;
        });
    }
}
