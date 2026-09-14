<?php

namespace App\Models;

namespace App\Services;

use App\Models\DiningTable;
use App\Models\QueueCounter;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Generate an atomic daily queue number for Take Away orders (e.g. A-001, A-002).
     */
    public function generateQueueNumber(int $warehouseId, string $prefix = 'A'): string
    {
        return DB::transaction(function () use ($warehouseId, $prefix) {
            $today = today()->toDateString();

            $counter = QueueCounter::where('warehouse_id', $warehouseId)
                ->whereDate('counter_date', $today)
                ->where('prefix', $prefix)
                ->lockForUpdate()
                ->first();

            if (! $counter) {
                $counter = QueueCounter::create([
                    'warehouse_id' => $warehouseId,
                    'counter_date' => $today,
                    'prefix' => $prefix,
                    'last_number' => 1,
                ]);
            } else {
                $counter->increment('last_number');
            }

            return sprintf('%s-%03d', $prefix, $counter->last_number);
        });
    }

    /**
     * Update individual sale item status for Kitchen workflow.
     */
    public function updateItemStatus(int $saleItemId, string $status): bool
    {
        $item = SaleItem::find($saleItemId);
        if (! $item) {
            return false;
        }

        $item->item_status = $status;
        if ($status === 'preparing' && ! $item->prepared_at) {
            $item->prepared_at = now();
        } elseif ($status === 'served' && ! $item->served_at) {
            $item->served_at = now();
        }
        $item->save();

        // Check if all items in this sale have reached ready/served
        $sale = $item->sale;
        if ($sale) {
            $allItems = $sale->items()->get();
            $allReadyOrServed = $allItems->every(fn ($i) => in_array($i->item_status, ['ready', 'served']));
            if ($allReadyOrServed && in_array($sale->order_status, ['new_order', 'preparing'])) {
                $sale->update(['order_status' => 'ready']);
            }
        }

        return true;
    }

    /**
     * Mark an entire sale order as ready from Kitchen Display.
     */
    public function markOrderReady(int $saleId): bool
    {
        $sale = Sale::with('items')->find($saleId);
        if (! $sale) {
            return false;
        }

        $sale->update(['order_status' => 'ready']);
        foreach ($sale->items as $item) {
            if ($item->item_status !== 'served') {
                $item->update(['item_status' => 'ready']);
            }
        }

        return true;
    }

    /**
     * Bump order (mark as served / completed from kitchen).
     */
    public function bumpOrder(int $saleId): bool
    {
        $sale = Sale::with('items')->find($saleId);
        if (! $sale) {
            return false;
        }

        $sale->update(['order_status' => 'served']);
        foreach ($sale->items as $item) {
            $item->update([
                'item_status' => 'served',
                'served_at' => now(),
            ]);
        }

        return true;
    }

    /**
     * Move table for active Dine-in order.
     */
    public function moveTable(int $saleId, int $newTableId): bool
    {
        return DB::transaction(function () use ($saleId, $newTableId) {
            $sale = Sale::find($saleId);
            $newTable = DiningTable::find($newTableId);

            if (! $sale || ! $newTable || $newTable->status !== 'available') {
                return false;
            }

            // Free old table
            if ($sale->dining_table_id) {
                $oldTable = DiningTable::find($sale->dining_table_id);
                $oldTable?->update([
                    'status' => 'available',
                    'current_sale_id' => null,
                ]);
            }

            // Occupy new table
            $newTable->update([
                'status' => 'occupied',
                'current_sale_id' => $sale->id,
            ]);

            $sale->update(['dining_table_id' => $newTable->id]);

            return true;
        });
    }
}
