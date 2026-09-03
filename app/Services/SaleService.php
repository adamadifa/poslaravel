<?php

namespace App\Services;

use App\Models\CashierShift;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
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

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Complete a POS Sale transaction in a DB Transaction.
     *
     * @param array $payload
     * @return Sale
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

            $customer = !empty($payload['customer_id']) ? Customer::with('group')->find($payload['customer_id']) : null;
            $items = $payload['items'] ?? [];
            $promoCode = $payload['promo_code'] ?? null;

            // 1. Calculate discount through DiscountService
            $discResult = $this->discountService->calculateCartDiscounts($items, $customer, $promoCode);

            $subtotal = $discResult['subtotal'];
            $discountAmount = $discResult['total_discount'] + (float) ($payload['manual_discount'] ?? 0);
            $taxAmount = (float) ($payload['tax_amount'] ?? 0);
            $grandTotal = max(0, $subtotal - $discountAmount + $taxAmount);

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
                'customer_id' => $customer?->id,
                'sale_date' => now(),
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'status' => 'completed',
                'reference_number' => $payload['reference_number'] ?? null,
                'notes' => $payload['notes'] ?? null,
            ]);

            // 3. Process Sale Items and Realtime Stock Deduction
            foreach ($items as $idx => $item) {
                $product = Product::findOrFail($item['product_id']);
                $unitId = (int) $item['unit_id'];
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

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'unit_id' => $unitId,
                    'conversion_ratio' => $conversionRatio,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'unit_cost' => $unitCost,
                    'discount_amount' => $itemDiscount,
                    'subtotal' => $lineSubtotal,
                ]);

                // Deduct stock via StockService (FIFO ready) and consume FIFO batches
                $fifoCogs = $this->stockService->consumeFifoBatches($product->id, $warehouseId, $qtyInBaseUnit);
                $effectiveUnitCost = $qtyInBaseUnit > 0 ? ($fifoCogs / $qtyInBaseUnit) : $unitCost;

                $this->stockService->deductStock(
                    $product->id,
                    $warehouseId,
                    $qtyInBaseUnit,
                    'Sale',
                    $sale->id,
                    $effectiveUnitCost,
                    "Penjualan POS Faktur {$sale->invoice_number}",
                    $userId
                );
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

            // Return stock for each item
            foreach ($sale->items as $item) {
                $qtyInBaseUnit = (float) $item->quantity * (float) $item->conversion_ratio;
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
