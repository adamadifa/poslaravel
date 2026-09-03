<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\UnitConversion;
use Illuminate\Support\Facades\DB;

class StockAdjustmentService
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Generate unique Adjustment Number (ADJ-YYYY-MM-0001)
     */
    public function generateAdjustmentNumber(): string
    {
        $prefix = 'ADJ-' . now()->format('Y-m-');
        $last = StockAdjustment::where('adjustment_number', 'like', "{$prefix}%")
            ->orderBy('adjustment_number', 'desc')
            ->first();

        if (!$last) {
            return $prefix . '0001';
        }

        $lastSeq = (int) substr($last->adjustment_number, -4);
        return $prefix . str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create Stock Adjustment (Draft or Directly Approved)
     */
    public function createStockAdjustment(array $data): StockAdjustment
    {
        return DB::transaction(function () use ($data) {
            $adjustmentNumber = $this->generateAdjustmentNumber();

            $adjustment = StockAdjustment::create([
                'adjustment_number' => $adjustmentNumber,
                'warehouse_id' => $data['warehouse_id'],
                'adjustment_date' => $data['adjustment_date'] ?? now()->toDateString(),
                'type' => $data['type'], // addition / reduction
                'reason' => $data['reason'],
                'status' => 'draft',
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $unitId = $item['unit_id'] ?? $product->base_unit_id;
                $qty = (float) $item['quantity'];

                // Calculate conversion ratio to base unit
                $conversionRatio = 1.0;
                if ($unitId != $product->base_unit_id) {
                    $conv = UnitConversion::where('product_id', $product->id)
                        ->where('from_unit_id', $unitId)
                        ->where('to_unit_id', $product->base_unit_id)
                        ->first();
                    if ($conv && $conv->conversion_value > 0) {
                        $conversionRatio = (float) $conv->conversion_value;
                    }
                }

                $baseQty = $qty * $conversionRatio;
                $unitCost = (float) ($item['unit_cost'] ?? ($product->purchase_price ?: 0));
                $totalCost = $baseQty * $unitCost;

                StockAdjustmentItem::create([
                    'stock_adjustment_id' => $adjustment->id,
                    'product_id' => $product->id,
                    'unit_id' => $unitId,
                    'quantity' => $qty,
                    'base_quantity' => $baseQty,
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                    'batch_number' => $item['batch_number'] ?? null,
                ]);
            }

            // If user requested to immediately approve
            if (isset($data['action']) && $data['action'] === 'approve') {
                $this->approveAdjustment($adjustment);
            }

            return $adjustment;
        });
    }

    /**
     * Update existing Stock Adjustment (Draft only)
     */
    public function updateStockAdjustment(StockAdjustment $adjustment, array $data): StockAdjustment
    {
        return DB::transaction(function () use ($adjustment, $data) {
            if ($adjustment->status === 'approved') {
                throw new \Exception('Penyesuaian stok yang sudah disetujui (Approved) tidak dapat diubah.');
            }

            $adjustment->update([
                'warehouse_id' => $data['warehouse_id'] ?? $adjustment->warehouse_id,
                'adjustment_date' => $data['adjustment_date'] ?? $adjustment->adjustment_date,
                'type' => $data['type'] ?? $adjustment->type,
                'reason' => $data['reason'] ?? $adjustment->reason,
                'notes' => $data['notes'] ?? $adjustment->notes,
            ]);

            if (isset($data['items'])) {
                $adjustment->items()->delete();

                foreach ($data['items'] as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    $unitId = $item['unit_id'] ?? $product->base_unit_id;
                    $qty = (float) $item['quantity'];

                    $conversionRatio = 1.0;
                    if ($unitId != $product->base_unit_id) {
                        $conv = UnitConversion::where('product_id', $product->id)
                            ->where('from_unit_id', $unitId)
                            ->where('to_unit_id', $product->base_unit_id)
                            ->first();
                        if ($conv && $conv->conversion_value > 0) {
                            $conversionRatio = (float) $conv->conversion_value;
                        }
                    }

                    $baseQty = $qty * $conversionRatio;
                    $unitCost = (float) ($item['unit_cost'] ?? ($product->purchase_price ?: 0));
                    $totalCost = $baseQty * $unitCost;

                    StockAdjustmentItem::create([
                        'stock_adjustment_id' => $adjustment->id,
                        'product_id' => $product->id,
                        'unit_id' => $unitId,
                        'quantity' => $qty,
                        'base_quantity' => $baseQty,
                        'unit_cost' => $unitCost,
                        'total_cost' => $totalCost,
                        'batch_number' => $item['batch_number'] ?? null,
                    ]);
                }
            }

            if (isset($data['action']) && $data['action'] === 'approve') {
                $this->approveAdjustment($adjustment);
            }

            return $adjustment;
        });
    }

    /**
     * Approve Stock Adjustment and Update Inventory & Batches
     */
    public function approveAdjustment(StockAdjustment $adjustment): void
    {
        DB::transaction(function () use ($adjustment) {
            if ($adjustment->status === 'approved') {
                throw new \Exception('Penyesuaian stok ini sudah pernah disetujui.');
            }

            if ($adjustment->status === 'cancelled') {
                throw new \Exception('Penyesuaian stok yang dibatalkan tidak dapat disetujui.');
            }

            foreach ($adjustment->items as $item) {
                if ($adjustment->type === 'addition') {
                    // Penambahan Stok (+): Stock Movement IN
                    $this->stockService->addStock(
                        $item->product_id,
                        $adjustment->warehouse_id,
                        $item->base_quantity,
                        'StockAdjustmentIn',
                        $adjustment->id,
                        $item->unit_cost,
                        "Penyesuaian Stok Tambah {$adjustment->adjustment_number} ({$adjustment->reason})",
                        auth()->id()
                    );

                    // Buat Batch Baru
                    $this->stockService->createStockBatch(
                        $item->product_id,
                        $adjustment->warehouse_id,
                        $item->base_quantity,
                        $item->unit_cost,
                        null,
                        $item->batch_number ?? ('ADJ-BATCH-' . now()->format('ymd') . '-' . $adjustment->id),
                        null
                    );
                } else {
                    // Pengurangan Stok (-): Stock Movement OUT
                    $this->stockService->deductStock(
                        $item->product_id,
                        $adjustment->warehouse_id,
                        $item->base_quantity,
                        'StockAdjustmentOut',
                        $adjustment->id,
                        $item->unit_cost,
                        "Penyesuaian Stok Kurang {$adjustment->adjustment_number} ({$adjustment->reason})",
                        auth()->id()
                    );

                    // Potong Batch FIFO
                    $this->stockService->consumeFifoBatches(
                        $item->product_id,
                        $adjustment->warehouse_id,
                        $item->base_quantity
                    );
                }
            }

            $adjustment->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        });
    }

    /**
     * Cancel Adjustment (Reverses stock and FIFO batches if already approved)
     */
    public function cancelAdjustment(StockAdjustment $adjustment): void
    {
        DB::transaction(function () use ($adjustment) {
            if ($adjustment->status === 'cancelled') {
                $adjustment->delete();
                return;
            }

            if ($adjustment->status === 'approved') {
                // Reverse stock movements
                foreach ($adjustment->items as $item) {
                    if ($adjustment->type === 'addition') {
                        // Tadinya Tambah (+) -> Sekarang Dibatalkan dengan Kurangi (-)
                        $this->stockService->deductStock(
                            $item->product_id,
                            $adjustment->warehouse_id,
                            $item->base_quantity,
                            'StockAdjustmentCancel',
                            $adjustment->id,
                            $item->unit_cost,
                            "Pembatalan Penyesuaian Tambah {$adjustment->adjustment_number}",
                            auth()->id()
                        );

                        $this->stockService->consumeFifoBatches(
                            $item->product_id,
                            $adjustment->warehouse_id,
                            $item->base_quantity
                        );
                    } else {
                        // Tadinya Kurang (-) -> Sekarang Dibatalkan dengan Tambah Kembali (+)
                        $this->stockService->addStock(
                            $item->product_id,
                            $adjustment->warehouse_id,
                            $item->base_quantity,
                            'StockAdjustmentCancel',
                            $adjustment->id,
                            $item->unit_cost,
                            "Pembatalan Penyesuaian Kurang {$adjustment->adjustment_number}",
                            auth()->id()
                        );

                        $this->stockService->createStockBatch(
                            $item->product_id,
                            $adjustment->warehouse_id,
                            $item->base_quantity,
                            $item->unit_cost,
                            null,
                            $item->batch_number ?? ('RESTORE-ADJ-' . now()->format('ymd')),
                            null
                        );
                    }
                }
            }

            $adjustment->update(['status' => 'cancelled']);
        });
    }
}
