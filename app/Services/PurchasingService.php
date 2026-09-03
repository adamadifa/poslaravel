<?php

namespace App\Services;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseReceipt;
use App\Models\PurchaseReceiptItem;
use App\Models\UnitConversion;
use Illuminate\Support\Facades\DB;

class PurchasingService
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Generate sequential PO number (PO-YYYY-MM-0001)
     */
    public function generatePoNumber(): string
    {
        $yearMonth = now()->format('Y-m');
        $prefix = "PO-{$yearMonth}-";
        $last = PurchaseOrder::where('po_number', 'like', "{$prefix}%")->orderByDesc('id')->first();
        $nextNumber = $last ? ((int) substr($last->po_number, -4) + 1) : 1;
        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate sequential GRN number (GRN-YYYY-MM-0001)
     */
    public function generateGrnNumber(): string
    {
        $yearMonth = now()->format('Y-m');
        $prefix = "GRN-{$yearMonth}-";
        $last = PurchaseReceipt::where('grn_number', 'like', "{$prefix}%")->orderByDesc('id')->first();
        $nextNumber = $last ? ((int) substr($last->grn_number, -4) + 1) : 1;
        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create a Purchase Order (PO)
     */
    public function createPurchaseOrder(array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($data) {
            $poNumber = $this->generatePoNumber();

            $po = PurchaseOrder::create([
                'po_number' => $poNumber,
                'supplier_id' => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'],
                'user_id' => auth()->id(),
                'order_date' => $data['order_date'] ?? now()->toDateString(),
                'expected_date' => $data['expected_date'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'subtotal' => 0,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'tax_amount' => $data['tax_amount'] ?? 0,
                'shipping_cost' => $data['shipping_cost'] ?? 0,
                'grand_total' => 0,
                'notes' => $data['notes'] ?? null,
            ]);

            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $qty = (float) $item['quantity_ordered'];
                $price = (float) $item['unit_price'];
                $discPct = (float) ($item['discount_percent'] ?? 0);
                $discAmt = ($qty * $price) * ($discPct / 100);
                $lineSubtotal = ($qty * $price) - $discAmt;
                $subtotal += $lineSubtotal;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'],
                    'quantity_ordered' => $qty,
                    'quantity_received' => 0,
                    'unit_price' => $price,
                    'discount_percent' => $discPct,
                    'discount_amount' => $discAmt,
                    'subtotal' => $lineSubtotal,
                ]);
            }

            $discountAmount = (float) ($data['discount_amount'] ?? 0);
            $taxAmount = (float) ($data['tax_amount'] ?? 0);
            $shippingCost = (float) ($data['shipping_cost'] ?? 0);
            $grandTotal = max(0, $subtotal - $discountAmount + $taxAmount + $shippingCost);

            $po->update([
                'subtotal' => $subtotal,
                'grand_total' => $grandTotal,
            ]);

            return $po->load(['supplier', 'warehouse', 'items.product', 'items.unit']);
        });
    }

    /**
     * Update an existing Purchase Order (PO)
     */
    public function updatePurchaseOrder(PurchaseOrder $po, array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($po, $data) {
            $po->update([
                'supplier_id' => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'],
                'order_date' => $data['order_date'] ?? $po->order_date,
                'expected_date' => $data['expected_date'] ?? null,
                'status' => $data['status'] ?? $po->status,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'tax_amount' => $data['tax_amount'] ?? 0,
                'shipping_cost' => $data['shipping_cost'] ?? 0,
                'notes' => $data['notes'] ?? null,
            ]);

            // Recreate PO items
            $po->items()->delete();

            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $qty = (float) $item['quantity_ordered'];
                $price = (float) $item['unit_price'];
                $discPct = (float) ($item['discount_percent'] ?? 0);
                $discAmt = ($qty * $price) * ($discPct / 100);
                $lineSubtotal = ($qty * $price) - $discAmt;
                $subtotal += $lineSubtotal;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'],
                    'quantity_ordered' => $qty,
                    'quantity_received' => 0,
                    'unit_price' => $price,
                    'discount_percent' => $discPct,
                    'discount_amount' => $discAmt,
                    'subtotal' => $lineSubtotal,
                ]);
            }

            $discountAmount = (float) ($data['discount_amount'] ?? 0);
            $taxAmount = (float) ($data['tax_amount'] ?? 0);
            $shippingCost = (float) ($data['shipping_cost'] ?? 0);
            $grandTotal = max(0, $subtotal - $discountAmount + $taxAmount + $shippingCost);

            $po->update([
                'subtotal' => $subtotal,
                'grand_total' => $grandTotal,
            ]);

            return $po->load(['supplier', 'warehouse', 'items.product', 'items.unit']);
        });
    }

    /**
     * Receive goods (GRN) from a PO or directly from Supplier,
     * add inventory, create FIFO stock batches, and update PO status.
     */
    public function processGoodsReceipt(array $data): PurchaseReceipt
    {
        return DB::transaction(function () use ($data) {
            $grnNumber = $this->generateGrnNumber();
            $poId = $data['purchase_order_id'] ?? null;
            $supplierId = $data['supplier_id'];
            $warehouseId = $data['warehouse_id'];
            $receiptDate = $data['receipt_date'] ?? now()->toDateString();
            $supplier = \App\Models\Supplier::findOrFail($supplierId);

            // Calculate Payment Due Date from Supplier's Payment Terms
            $termDays = (int) ($supplier->payment_term_days ?? 0);
            $paymentDueDate = now()->parse($receiptDate)->addDays($termDays)->toDateString();

            $receipt = PurchaseReceipt::create([
                'grn_number' => $grnNumber,
                'purchase_order_id' => $poId,
                'supplier_id' => $supplierId,
                'warehouse_id' => $warehouseId,
                'user_id' => auth()->id(),
                'receipt_date' => $receiptDate,
                'supplier_invoice_number' => $data['supplier_invoice_number'] ?? null,
                'status' => 'confirmed',
                'subtotal' => 0,
                'tax_amount' => $data['tax_amount'] ?? 0,
                'grand_total' => 0,
                'payment_status' => 'unpaid',
                'payment_due_date' => $paymentDueDate,
                'notes' => $data['notes'] ?? null,
            ]);

            $subtotal = 0;

            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $unitId = (int) $item['unit_id'];
                $qtyReceived = (float) $item['quantity_received'];
                $unitCost = (float) $item['unit_cost'];
                $lineSubtotal = $qtyReceived * $unitCost;
                $subtotal += $lineSubtotal;

                // Resolve conversion to base unit
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

                $baseQuantity = $qtyReceived * $conversionRatio;
                $baseUnitCost = $conversionRatio > 0 ? ($unitCost / $conversionRatio) : $unitCost;

                $receiptItem = PurchaseReceiptItem::create([
                    'purchase_receipt_id' => $receipt->id,
                    'purchase_order_item_id' => $item['purchase_order_item_id'] ?? null,
                    'product_id' => $product->id,
                    'unit_id' => $unitId,
                    'quantity_received' => $qtyReceived,
                    'base_quantity' => $baseQuantity,
                    'unit_cost' => $unitCost,
                    'base_unit_cost' => $baseUnitCost,
                    'subtotal' => $lineSubtotal,
                    'batch_number' => $item['batch_number'] ?? ('BATCH-' . now()->format('ymd') . '-' . rand(100, 999)),
                    'expiry_date' => $item['expiry_date'] ?? null,
                ]);

                // 1. Add Stock Movement & Update Warehouse ProductStock
                $this->stockService->addStock(
                    $product->id,
                    $warehouseId,
                    $baseQuantity,
                    'PurchaseReceipt',
                    $receipt->id,
                    $baseUnitCost,
                    "Penerimaan Barang {$receipt->grn_number}",
                    auth()->id()
                );

                // 2. Create FIFO Stock Batch
                $this->stockService->createStockBatch(
                    $product->id,
                    $warehouseId,
                    $baseQuantity,
                    $baseUnitCost,
                    $receiptItem->id,
                    $receiptItem->batch_number,
                    $receiptItem->expiry_date
                );

                // 3. Update Product latest HPP / purchase price
                $product->purchase_price = $baseUnitCost;
                $product->save();

                // 4. Update PO Item received quantity if PO exists
                if (!empty($item['purchase_order_item_id'])) {
                    $poItem = PurchaseOrderItem::find($item['purchase_order_item_id']);
                    if ($poItem) {
                        $poItem->quantity_received += $qtyReceived;
                        $poItem->save();
                    }
                }
            }

            $taxAmount = (float) ($data['tax_amount'] ?? 0);
            $grandTotal = $subtotal + $taxAmount;

            $receipt->update([
                'subtotal' => $subtotal,
                'grand_total' => $grandTotal,
            ]);

            // Update PO Status if linked
            if ($poId) {
                $po = PurchaseOrder::with('items')->find($poId);
                if ($po) {
                    $allReceived = $po->items->every(fn($i) => $i->quantity_received >= $i->quantity_ordered);
                    $anyReceived = $po->items->some(fn($i) => $i->quantity_received > 0);

                    if ($allReceived) {
                        $po->status = 'received';
                    } elseif ($anyReceived) {
                        $po->status = 'partial';
                    }
                    $po->save();
                }
            }

            return $receipt->load(['supplier', 'warehouse', 'items.product', 'items.unit']);
        });
    }

    /**
     * Generate sequential Return number (PR-YYYY-MM-0001)
     */
    public function generateReturnNumber(): string
    {
        $yearMonth = now()->format('Y-m');
        $prefix = "PR-{$yearMonth}-";
        $last = \App\Models\PurchaseReturn::where('return_number', 'like', "{$prefix}%")->orderByDesc('id')->first();
        $nextNumber = $last ? ((int) substr($last->return_number, -4) + 1) : 1;
        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Process Purchase Return (Retur Pembelian)
     * Deducts stock, writes stock movement (type: out), reduces FIFO batch or creates FIFO adjustment, and records return.
     */
    public function processPurchaseReturn(array $data): \App\Models\PurchaseReturn
    {
        return DB::transaction(function () use ($data) {
            $returnNumber = $this->generateReturnNumber();
            $receiptId = $data['purchase_receipt_id'] ?? null;
            $supplierId = $data['supplier_id'];
            $warehouseId = $data['warehouse_id'];

            $return = \App\Models\PurchaseReturn::create([
                'return_number' => $returnNumber,
                'purchase_receipt_id' => $receiptId,
                'supplier_id' => $supplierId,
                'warehouse_id' => $warehouseId,
                'user_id' => auth()->id(),
                'return_date' => $data['return_date'] ?? now()->toDateString(),
                'status' => 'confirmed',
                'total_amount' => 0,
                'reason' => $data['reason'] ?? null,
            ]);

            $totalAmount = 0;

            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $unitId = (int) $item['unit_id'];
                $qty = (float) $item['quantity'];
                $unitCost = (float) $item['unit_cost'];
                $lineSubtotal = $qty * $unitCost;
                $totalAmount += $lineSubtotal;

                // Conversion to base unit
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

                $baseQuantity = $qty * $conversionRatio;

                \App\Models\PurchaseReturnItem::create([
                    'purchase_return_id' => $return->id,
                    'purchase_receipt_item_id' => $item['purchase_receipt_item_id'] ?? null,
                    'product_id' => $product->id,
                    'unit_id' => $unitId,
                    'quantity' => $qty,
                    'base_quantity' => $baseQuantity,
                    'unit_cost' => $unitCost,
                    'subtotal' => $lineSubtotal,
                    'batch_number' => $item['batch_number'] ?? null,
                ]);

                // 1. Deduct Stock & Record Stock Movement Out
                $this->stockService->deductStock(
                    $product->id,
                    $warehouseId,
                    $baseQuantity,
                    'PurchaseReturn',
                    $return->id,
                    $unitCost / $conversionRatio,
                    "Retur Pembelian {$return->return_number} (Alasan: " . ($data['reason'] ?? '-') . ")",
                    auth()->id()
                );

                // 2. FIFO Stock Batches deduction
                $this->stockService->consumeFifoBatches(
                    $product->id,
                    $warehouseId,
                    $baseQuantity
                );
            }

            $return->update(['total_amount' => $totalAmount]);

            return $return->load(['supplier', 'warehouse', 'items.product', 'items.unit']);
        });
    }

    /**
     * Cancel/Delete Purchase Return (restores stock if cancelled)
     */
    public function cancelPurchaseReturn(\App\Models\PurchaseReturn $purchaseReturn): void
    {
        DB::transaction(function () use ($purchaseReturn) {
            if ($purchaseReturn->status === 'cancelled') {
                $purchaseReturn->delete();
                return;
            }

            // Reverse stock: add back the returned quantities
            foreach ($purchaseReturn->items as $item) {
                $this->stockService->addStock(
                    $item->product_id,
                    $purchaseReturn->warehouse_id,
                    $item->base_quantity,
                    'PurchaseReturnCancellation',
                    $purchaseReturn->id,
                    $item->unit_cost / ($item->base_quantity / max(1, $item->quantity)),
                    "Pembatalan Retur {$purchaseReturn->return_number}",
                    auth()->id()
                );

                // Restore batch: if batch exists, add back remaining qty, else create restored batch
                $existingBatch = null;
                if (!empty($item->batch_number)) {
                    $existingBatch = \App\Models\StockBatch::where('product_id', $item->product_id)
                        ->where('warehouse_id', $purchaseReturn->warehouse_id)
                        ->where('batch_number', $item->batch_number)
                        ->first();
                }

                if ($existingBatch) {
                    $existingBatch->increment('qty_remaining', $item->base_quantity);
                } else {
                    $this->stockService->createStockBatch(
                        $item->product_id,
                        $purchaseReturn->warehouse_id,
                        $item->base_quantity,
                        $item->unit_cost / ($item->base_quantity / max(1, $item->quantity)),
                        null,
                        $item->batch_number ?? ('BATCH-RESTORE-' . now()->format('ymd')),
                        null
                    );
                }
            }

            $purchaseReturn->update(['status' => 'cancelled']);
        });
    }
}
