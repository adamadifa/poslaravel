<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use Illuminate\Support\Facades\DB;

class StockOpnameService
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Generate unique Opname Number (SO-YYYY-MM-0001)
     */
    public function generateOpnameNumber(): string
    {
        $prefix = 'SO-' . now()->format('Y-m-');
        $lastOpname = StockOpname::where('opname_number', 'like', "{$prefix}%")
            ->orderBy('opname_number', 'desc')
            ->first();

        if (!$lastOpname) {
            return $prefix . '0001';
        }

        $lastSeq = (int) substr($lastOpname->opname_number, -4);
        return $prefix . str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create new Stock Opname (Draft / In Progress)
     */
    public function createStockOpname(array $data): StockOpname
    {
        return DB::transaction(function () use ($data) {
            $opnameNumber = $this->generateOpnameNumber();

            $opname = StockOpname::create([
                'opname_number' => $opnameNumber,
                'warehouse_id' => $data['warehouse_id'],
                'opname_date' => $data['opname_date'] ?? now()->toDateString(),
                'status' => $data['status'] ?? 'draft',
                'notes' => $data['notes'] ?? null,
                'conducted_by' => auth()->id(),
            ]);

            foreach ($data['items'] as $item) {
                $productId = $item['product_id'];
                $physicalQty = (float) $item['physical_qty'];
                
                // Get current system stock
                $currentStock = ProductStock::where('product_id', $productId)
                    ->where('warehouse_id', $data['warehouse_id'])
                    ->value('quantity') ?? 0;

                $product = Product::find($productId);
                $unitCost = $product ? (float) $product->purchase_price : 0;
                $diffQty = $physicalQty - (float) $currentStock;
                $diffValue = $diffQty * $unitCost;

                StockOpnameItem::create([
                    'stock_opname_id' => $opname->id,
                    'product_id' => $productId,
                    'system_qty' => $currentStock,
                    'physical_qty' => $physicalQty,
                    'difference_qty' => $diffQty,
                    'unit_cost' => $unitCost,
                    'difference_value' => $diffValue,
                    'reason' => $item['reason'] ?? null,
                ]);
            }

            return $opname;
        });
    }

    /**
     * Update Stock Opname Items & Quantities
     */
    public function updateStockOpname(StockOpname $opname, array $data): StockOpname
    {
        return DB::transaction(function () use ($opname, $data) {
            if ($opname->status === 'completed') {
                throw new \Exception('Stok Opname yang sudah disetujui (Completed) tidak dapat diubah lagi.');
            }

            $opname->update([
                'opname_date' => $data['opname_date'] ?? $opname->opname_date,
                'notes' => $data['notes'] ?? $opname->notes,
                'status' => $data['status'] ?? $opname->status,
            ]);

            if (isset($data['items'])) {
                $opname->items()->delete();

                foreach ($data['items'] as $item) {
                    $productId = $item['product_id'];
                    $physicalQty = (float) $item['physical_qty'];
                    
                    $currentStock = ProductStock::where('product_id', $productId)
                        ->where('warehouse_id', $opname->warehouse_id)
                        ->value('quantity') ?? 0;

                    $product = Product::find($productId);
                    $unitCost = $product ? (float) $product->purchase_price : 0;
                    $diffQty = $physicalQty - (float) $currentStock;
                    $diffValue = $diffQty * $unitCost;

                    StockOpnameItem::create([
                        'stock_opname_id' => $opname->id,
                        'product_id' => $productId,
                        'system_qty' => $currentStock,
                        'physical_qty' => $physicalQty,
                        'difference_qty' => $diffQty,
                        'unit_cost' => $unitCost,
                        'difference_value' => $diffValue,
                        'reason' => $item['reason'] ?? null,
                    ]);
                }
            }

            return $opname;
        });
    }

    /**
     * Approve and Finalize Stock Opname (Auto-Adjust Stock & Record Stock Movements)
     */
    public function approveStockOpname(StockOpname $opname): void
    {
        DB::transaction(function () use ($opname) {
            if ($opname->status === 'completed') {
                throw new \Exception('Stok Opname ini sudah pernah disetujui.');
            }

            if ($opname->status === 'cancelled') {
                throw new \Exception('Stok Opname yang dibatalkan tidak dapat disetujui.');
            }

            foreach ($opname->items as $item) {
                $diffQty = (float) $item->difference_qty;

                if ($diffQty > 0) {
                    // Fisik LEBIH BANYAK dari sistem -> Tambah Stok (Stock Movement IN)
                    $this->stockService->addStock(
                        $item->product_id,
                        $opname->warehouse_id,
                        $diffQty,
                        'StockOpname',
                        $opname->id,
                        $item->unit_cost,
                        "Penyesuaian Opname Fisik Lebih {$opname->opname_number}: " . ($item->reason ?? 'Selisih Lebih Fisik'),
                        auth()->id()
                    );

                    // Buat batch baru untuk selisih lebih
                    $this->stockService->createStockBatch(
                        $item->product_id,
                        $opname->warehouse_id,
                        $diffQty,
                        $item->unit_cost,
                        null,
                        'SO-BATCH-' . now()->format('ymd') . '-' . $opname->id,
                        null
                    );
                } elseif ($diffQty < 0) {
                    // Fisik LEBIH SEDIKIT dari sistem -> Kurangi Stok (Stock Movement OUT)
                    $deductQty = abs($diffQty);

                    $this->stockService->deductStock(
                        $item->product_id,
                        $opname->warehouse_id,
                        $deductQty,
                        'StockOpname',
                        $opname->id,
                        $item->unit_cost,
                        "Penyesuaian Opname Fisik Kurang {$opname->opname_number}: " . ($item->reason ?? 'Selisih Kurang Fisik/Hilang/Rusak'),
                        auth()->id()
                    );

                    // Potong batch FIFO
                    $this->stockService->consumeFifoBatches(
                        $item->product_id,
                        $opname->warehouse_id,
                        $deductQty
                    );
                }
            }

            $opname->update([
                'status' => 'completed',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        });
    }

    /**
     * Cancel Stock Opname
     */
    public function cancelStockOpname(StockOpname $opname): void
    {
        if ($opname->status === 'completed') {
            throw new \Exception('Stok Opname yang sudah disetujui (Completed) tidak dapat dibatalkan karena mutasi persediaan sudah terposting.');
        }

        $opname->update(['status' => 'cancelled']);
    }
}
