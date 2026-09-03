<?php

namespace App\Services;

use App\Models\Account;
use App\Models\CashFlow;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\UnitConversion;
use Illuminate\Support\Facades\DB;

class SaleReturnService
{
    protected StockService $stockService;
    protected FinanceService $financeService;

    public function __construct(StockService $stockService, FinanceService $financeService)
    {
        $this->stockService = $stockService;
        $this->financeService = $financeService;
    }

    /**
     * Generate Sale Return Number (SR-YYYY-MM-0001)
     */
    public function generateReturnNumber(): string
    {
        $prefix = 'SR-' . now()->format('Y-m-');
        $last = SaleReturn::where('return_number', 'like', "{$prefix}%")
            ->orderBy('return_number', 'desc')
            ->first();

        if (!$last) {
            return $prefix . '0001';
        }

        $lastSeq = (int) substr($last->return_number, -4);
        return $prefix . str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Process Sale Return
     */
    public function processSaleReturn(array $data): SaleReturn
    {
        return DB::transaction(function () use ($data) {
            $sale = Sale::with('items')->findOrFail($data['sale_id']);
            $returnNumber = $this->generateReturnNumber();
            $refundMethod = $data['refund_method'] ?? 'cash';
            $accountId = $data['account_id'] ?? null;

            $totalRefund = 0;
            $itemsData = [];

            // 1. Process Retur Items (Barang Masuk / IN)
            foreach ($data['items'] as $item) {
                $qty = (float) $item['quantity'];
                if ($qty <= 0) continue;

                $product = Product::findOrFail($item['product_id']);
                $unitId = $item['unit_id'] ?? $product->base_unit_id;
                $unitPrice = (float) $item['unit_price'];

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
                $lineTotal = $qty * $unitPrice;
                $totalRefund += $lineTotal;

                $itemsData[] = [
                    'product_id' => $product->id,
                    'unit_id' => $unitId,
                    'quantity' => $qty,
                    'base_quantity' => $baseQty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $lineTotal,
                    'type' => 'return',
                    'batch_number' => $item['batch_number'] ?? null,
                ];
            }

            if (empty($itemsData)) {
                throw new \Exception('Pilih setidaknya 1 item barang yang akan diretur.');
            }

            // 2. Process Replacement Items (Barang Keluar / OUT) if exchange method
            $replacementItemsData = [];
            $totalReplacement = 0;

            if ($refundMethod === 'exchange' && !empty($data['replacement_items'])) {
                foreach ($data['replacement_items'] as $rep) {
                    $repQty = (float) ($rep['quantity'] ?? 0);
                    if ($repQty <= 0) continue;

                    $product = Product::findOrFail($rep['product_id']);
                    $unitId = $rep['unit_id'] ?? $product->base_unit_id;
                    $unitPrice = (float) ($rep['unit_price'] ?? $product->selling_price);

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

                    $baseQty = $repQty * $conversionRatio;
                    $lineTotal = $repQty * $unitPrice;
                    $totalReplacement += $lineTotal;

                    $replacementItemsData[] = [
                        'product_id' => $product->id,
                        'unit_id' => $unitId,
                        'quantity' => $repQty,
                        'base_quantity' => $baseQty,
                        'unit_price' => $unitPrice,
                        'subtotal' => $lineTotal,
                        'type' => 'replacement',
                        'batch_number' => null,
                    ];
                }
            }

            // Create Return Record
            $saleReturn = SaleReturn::create([
                'return_number' => $returnNumber,
                'sale_id' => $sale->id,
                'customer_id' => $sale->customer_id,
                'warehouse_id' => $sale->warehouse_id ?? 1,
                'account_id' => $accountId,
                'return_date' => $data['return_date'] ?? now()->toDateString(),
                'subtotal' => $totalRefund,
                'tax_amount' => 0,
                'refund_amount' => $refundMethod === 'exchange' ? max(0, $totalRefund - $totalReplacement) : $totalRefund,
                'refund_method' => $refundMethod,
                'reason' => $data['reason'],
                'status' => 'completed',
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Save Return Items & IN Stock Movement
            foreach ($itemsData as $item) {
                $item['sale_return_id'] = $saleReturn->id;
                SaleReturnItem::create($item);

                // Auto: Restock Barang ke Gudang (+ Stock Movement IN)
                $this->stockService->addStock(
                    $item['product_id'],
                    $saleReturn->warehouse_id,
                    $item['base_quantity'],
                    'SaleReturn',
                    $saleReturn->id,
                    $item['unit_price'],
                    "Retur Penjualan {$saleReturn->return_number} (Inv: {$sale->invoice_number})",
                    auth()->id()
                );

                // Restore / Buat Batch Baru
                $generatedBatchNumber = $item['batch_number'] ?? ('SR-' . $saleReturn->return_number . '-' . rand(100, 999));
                $this->stockService->createStockBatch(
                    $item['product_id'],
                    $saleReturn->warehouse_id,
                    $item['base_quantity'],
                    $item['unit_price'],
                    null,
                    $generatedBatchNumber,
                    null
                );
            }

            // Save Replacement Items & OUT Stock Movement
            foreach ($replacementItemsData as $repItem) {
                $repItem['sale_return_id'] = $saleReturn->id;
                SaleReturnItem::create($repItem);

                // Deduct stock via FIFO for replacement item
                $fifoCogs = $this->stockService->consumeFifoBatches($repItem['product_id'], $saleReturn->warehouse_id, $repItem['base_quantity']);
                $effectiveUnitCost = $repItem['base_quantity'] > 0 ? ($fifoCogs / $repItem['base_quantity']) : $repItem['unit_price'];

                $this->stockService->deductStock(
                    $repItem['product_id'],
                    $saleReturn->warehouse_id,
                    $repItem['base_quantity'],
                    'SaleReturnExchange',
                    $saleReturn->id,
                    $effectiveUnitCost,
                    "Tukar Barang Retur {$saleReturn->return_number} (Inv: {$sale->invoice_number})",
                    auth()->id()
                );
            }

            // Handle Refund Payment Flow
            if ($refundMethod === 'cash' && $accountId && $totalRefund > 0) {
                $account = Account::findOrFail($accountId);
                $account->decrement('current_balance', $totalRefund);

                // Record Cash Flow (Expense)
                CashFlow::create([
                    'cash_flow_number' => $this->financeService->generateCashFlowNumber(),
                    'account_id' => $account->id,
                    'type' => 'expense',
                    'category' => 'Refund Retur Penjualan',
                    'amount' => $totalRefund,
                    'transaction_date' => $saleReturn->return_date,
                    'reference_type' => SaleReturn::class,
                    'reference_id' => $saleReturn->id,
                    'description' => "Refund Retur {$saleReturn->return_number} (Inv: {$sale->invoice_number})",
                    'created_by' => auth()->id(),
                ]);
            } elseif ($refundMethod === 'credit_deduction') {
                // Kurangi piutang pada transaksi penjualan
                $newGrand = max(0, (float) $sale->grand_total - $totalRefund);
                $sale->grand_total = $newGrand;
                if ((float) $sale->paid_amount >= $newGrand) {
                    $sale->payment_status = 'paid';
                }
                $sale->save();
            }

            return $saleReturn;
        });
    }

    /**
     * Cancel Sale Return
     */
    public function cancelSaleReturn(SaleReturn $saleReturn): void
    {
        DB::transaction(function () use ($saleReturn) {
            if ($saleReturn->status === 'cancelled') {
                $saleReturn->delete();
                return;
            }

            // Reverse stock for return items (Deduct IN items back)
            foreach ($saleReturn->items()->where('type', 'return')->get() as $item) {
                $this->stockService->deductStock(
                    $item->product_id,
                    $saleReturn->warehouse_id,
                    $item->base_quantity,
                    'SaleReturnCancel',
                    $saleReturn->id,
                    $item->unit_price,
                    "Pembatalan Retur Penjualan {$saleReturn->return_number}",
                    auth()->id()
                );

                $this->stockService->consumeFifoBatches(
                    $item->product_id,
                    $saleReturn->warehouse_id,
                    $item->base_quantity
                );
            }

            // Reverse stock for replacement items (Add OUT items back)
            foreach ($saleReturn->items()->where('type', 'replacement')->get() as $rep) {
                $this->stockService->addStock(
                    $rep->product_id,
                    $saleReturn->warehouse_id,
                    $rep->base_quantity,
                    'SaleReturnExchangeCancel',
                    $saleReturn->id,
                    $rep->unit_price,
                    "Pembatalan Tukar Barang Retur {$saleReturn->return_number}",
                    auth()->id()
                );

                $this->stockService->createStockBatch(
                    $rep->product_id,
                    $saleReturn->warehouse_id,
                    $rep->base_quantity,
                    $rep->unit_price,
                    null,
                    'SR-EXCH-RESTORE-' . now()->format('ymd'),
                    null
                );
            }

            // Reverse cash refund
            if ($saleReturn->refund_method === 'cash' && $saleReturn->account_id) {
                $account = Account::find($saleReturn->account_id);
                if ($account) {
                    $account->increment('current_balance', $saleReturn->refund_amount);
                }
            }

            $saleReturn->update(['status' => 'cancelled']);
        });
    }
}
