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
}
