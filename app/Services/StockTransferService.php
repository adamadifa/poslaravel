<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\UnitConversion;
use Illuminate\Support\Facades\DB;

class StockTransferService
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Generate unique Transfer Number (TRF-YYYY-MM-0001)
     */
    public function generateTransferNumber(): string
    {
        $prefix = 'TRF-' . now()->format('Y-m-');
        $last = StockTransfer::where('transfer_number', 'like', "{$prefix}%")
            ->orderBy('transfer_number', 'desc')
            ->first();

        if (!$last) {
            return $prefix . '0001';
        }

        $lastSeq = (int) substr($last->transfer_number, -4);
        return $prefix . str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create Stock Transfer (Draft or Directly Sent)
     */
    public function createStockTransfer(array $data): StockTransfer
    {
        return DB::transaction(function () use ($data) {
            if ($data['from_warehouse_id'] == $data['to_warehouse_id']) {
                throw new \Exception('Gudang asal dan gudang tujuan transfer tidak boleh sama.');
            }

            $transferNumber = $this->generateTransferNumber();

            $transfer = StockTransfer::create([
                'transfer_number' => $transferNumber,
                'from_warehouse_id' => $data['from_warehouse_id'],
                'to_warehouse_id' => $data['to_warehouse_id'],
                'transfer_date' => $data['transfer_date'] ?? now()->toDateString(),
                'status' => 'draft',
                'notes' => $data['notes'] ?? null,
                'sent_by' => auth()->id(),
            ]);

            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $unitId = $item['unit_id'] ?? $product->base_unit_id;
                $qty = (float) $item['quantity_sent'];

                // Calculate base quantity
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
                $unitCost = (float) ($product->purchase_price ?: 0);

                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $product->id,
                    'unit_id' => $unitId,
                    'quantity_sent' => $qty,
                    'quantity_received' => 0,
                    'base_quantity_sent' => $baseQty,
                    'base_quantity_received' => 0,
                    'unit_cost' => $unitCost,
                    'batch_number' => $item['batch_number'] ?? null,
                ]);
            }

            // If user requested to immediately dispatch/send
            if (isset($data['action']) && $data['action'] === 'dispatch') {
                $this->dispatchTransfer($transfer);
            }

            return $transfer;
        });
    }

    /**
     * Dispatch Transfer: Status draft -> in_transit (Stock leaves from_warehouse)
     */
    public function dispatchTransfer(StockTransfer $transfer): void
    {
        DB::transaction(function () use ($transfer) {
            if ($transfer->status !== 'draft') {
                throw new \Exception('Hanya transfer berstatus Draft yang dapat dikirim.');
            }

            foreach ($transfer->items as $item) {
                // Deduct from origin warehouse
                $this->stockService->deductStock(
                    $item->product_id,
                    $transfer->from_warehouse_id,
                    $item->base_quantity_sent,
                    'StockTransferOut',
                    $transfer->id,
                    $item->unit_cost,
                    "Transfer Keluar ke {$transfer->toWarehouse->name} ({$transfer->transfer_number})",
                    auth()->id()
                );

                // Consume origin FIFO batches
                $this->stockService->consumeFifoBatches(
                    $item->product_id,
                    $transfer->from_warehouse_id,
                    $item->base_quantity_sent
                );
            }

            $transfer->update([
                'status' => 'in_transit',
                'sent_by' => auth()->id(),
                'sent_at' => now(),
            ]);
        });
    }

    /**
     * Receive Transfer: Status in_transit -> completed (Stock enters to_warehouse)
     */
    public function receiveTransfer(StockTransfer $transfer, array $receivedData): void
    {
        DB::transaction(function () use ($transfer, $receivedData) {
            if ($transfer->status !== 'in_transit') {
                throw new \Exception('Hanya transfer yang sedang Dalam Perjalanan (In Transit) yang dapat diterima.');
            }

            foreach ($transfer->items as $item) {
                $receivedQty = isset($receivedData['items'][$item->id]['quantity_received']) 
                    ? (float) $receivedData['items'][$item->id]['quantity_received']
                    : $item->quantity_sent;

                $conversionRatio = $item->quantity_sent > 0 
                    ? ($item->base_quantity_sent / $item->quantity_sent) 
                    : 1.0;

                $baseReceivedQty = $receivedQty * $conversionRatio;

                $item->update([
                    'quantity_received' => $receivedQty,
                    'base_quantity_received' => $baseReceivedQty,
                ]);

                if ($baseReceivedQty > 0) {
                    // Add stock to destination warehouse
                    $this->stockService->addStock(
                        $item->product_id,
                        $transfer->to_warehouse_id,
                        $baseReceivedQty,
                        'StockTransferIn',
                        $transfer->id,
                        $item->unit_cost,
                        "Transfer Masuk dari {$transfer->fromWarehouse->name} ({$transfer->transfer_number})",
                        auth()->id()
                    );

                    // Create new FIFO batch in destination warehouse
                    $this->stockService->createStockBatch(
                        $item->product_id,
                        $transfer->to_warehouse_id,
                        $baseReceivedQty,
                        $item->unit_cost,
                        null,
                        $item->batch_number ?? ('TRF-BATCH-' . now()->format('ymd') . '-' . $transfer->id),
                        null
                    );
                }
            }

            $transfer->update([
                'status' => 'completed',
                'received_by' => auth()->id(),
                'received_at' => now(),
            ]);
        });
    }

    /**
     * Cancel Transfer (Restores stock if already in_transit)
     */
    public function cancelTransfer(StockTransfer $transfer): void
    {
        DB::transaction(function () use ($transfer) {
            if ($transfer->status === 'completed') {
                throw new \Exception('Transfer yang sudah selesai diterima tidak dapat dibatalkan.');
            }

            if ($transfer->status === 'in_transit') {
                // Restore stock back to origin warehouse
                foreach ($transfer->items as $item) {
                    $this->stockService->addStock(
                        $item->product_id,
                        $transfer->from_warehouse_id,
                        $item->base_quantity_sent,
                        'StockTransferCancel',
                        $transfer->id,
                        $item->unit_cost,
                        "Pembatalan Transfer {$transfer->transfer_number}",
                        auth()->id()
                    );

                    $this->stockService->createStockBatch(
                        $item->product_id,
                        $transfer->from_warehouse_id,
                        $item->base_quantity_sent,
                        $item->unit_cost,
                        null,
                        $item->batch_number ?? ('RESTORE-TRF-' . now()->format('ymd')),
                        null
                    );
                }
            }

            $transfer->update(['status' => 'cancelled']);
        });
    }
}
