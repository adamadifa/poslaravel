<?php

namespace App\Services;

use App\Models\ProductStock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Deduct stock for a product from a warehouse and record stock movement.
     *
     * @param int $productId
     * @param int $warehouseId
     * @param float $qtyInBaseUnit
     * @param string $referenceType
     * @param int $referenceId
     * @param float $unitCost
     * @param string|null $description
     * @param int|null $userId
     * @return ProductStock
     */
    public function deductStock(
        int $productId,
        int $warehouseId,
        float $qtyInBaseUnit,
        string $referenceType,
        int $referenceId,
        float $unitCost = 0,
        ?string $description = null,
        ?int $userId = null
    ): ProductStock {
        $stock = ProductStock::firstOrCreate(
            ['product_id' => $productId, 'warehouse_id' => $warehouseId],
            ['quantity' => 0, 'reserved_qty' => 0]
        );

        $beforeStock = (float) $stock->quantity;
        $afterStock = $beforeStock - $qtyInBaseUnit;

        $stock->quantity = $afterStock;
        $stock->save();

        // Record stock movement (append-only)
        StockMovement::create([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'type' => 'out',
            'quantity' => $qtyInBaseUnit,
            'unit_cost' => $unitCost,
            'before_stock' => $beforeStock,
            'after_stock' => $afterStock,
            'description' => $description,
            'created_by' => $userId ?: auth()->id(),
            'created_at' => now(),
        ]);

        return $stock;
    }

    /**
     * Add stock for a product in a warehouse and record stock movement.
     */
    public function addStock(
        int $productId,
        int $warehouseId,
        float $qtyInBaseUnit,
        string $referenceType,
        int $referenceId,
        float $unitCost = 0,
        ?string $description = null,
        ?int $userId = null
    ): ProductStock {
        $stock = ProductStock::firstOrCreate(
            ['product_id' => $productId, 'warehouse_id' => $warehouseId],
            ['quantity' => 0, 'reserved_qty' => 0]
        );

        $beforeStock = (float) $stock->quantity;
        $afterStock = $beforeStock + $qtyInBaseUnit;

        $stock->quantity = $afterStock;
        $stock->save();

        StockMovement::create([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'type' => 'in',
            'quantity' => $qtyInBaseUnit,
            'unit_cost' => $unitCost,
            'before_stock' => $beforeStock,
            'after_stock' => $afterStock,
            'description' => $description,
            'created_by' => $userId ?: auth()->id(),
            'created_at' => now(),
        ]);

        return $stock;
    }

    /**
     * Create a new FIFO stock batch upon receiving goods.
     */
    public function createStockBatch(
        int $productId,
        int $warehouseId,
        float $qtyInBaseUnit,
        float $unitCostBase,
        ?int $purchaseReceiptItemId = null,
        ?string $batchNumber = null,
        ?string $expiryDate = null
    ): \App\Models\StockBatch {
        return \App\Models\StockBatch::create([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'purchase_receipt_item_id' => $purchaseReceiptItemId,
            'batch_number' => $batchNumber,
            'expiry_date' => $expiryDate,
            'qty_in' => $qtyInBaseUnit,
            'qty_remaining' => $qtyInBaseUnit,
            'unit_cost' => $unitCostBase,
            'entry_date' => now()->toDateString(),
        ]);
    }

    /**
     * Consume stock batches using FIFO (First-In, First-Out) algorithm upon POS sale.
     * Returns total cost of goods sold (COGS / HPP).
     *
     * @param int $productId
     * @param int $warehouseId
     * @param float $qtyToConsume
     * @return float Total COGS consumed
     */
    public function consumeFifoBatches(int $productId, int $warehouseId, float $qtyToConsume): float
    {
        $batches = \App\Models\StockBatch::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where('qty_remaining', '>', 0)
            ->orderBy('entry_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $remainingNeeded = $qtyToConsume;
        $totalCogs = 0;

        foreach ($batches as $batch) {
            if ($remainingNeeded <= 0) break;

            $take = min((float) $batch->qty_remaining, $remainingNeeded);
            $batch->qty_remaining -= $take;
            $batch->save();

            $totalCogs += $take * (float) $batch->unit_cost;
            $remainingNeeded -= $take;
        }

        // If not enough batches existed, fallback cost to product purchase_price
        if ($remainingNeeded > 0) {
            $product = \App\Models\Product::find($productId);
            $fallbackCost = $product ? (float) $product->purchase_price : 0;
            $totalCogs += $remainingNeeded * $fallbackCost;
        }

        return $totalCogs;
    }
}
