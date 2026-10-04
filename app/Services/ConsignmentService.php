<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountMutation;
use App\Models\CashFlow;
use App\Models\ConsignmentReceipt;
use App\Models\ConsignmentReceiptItem;
use App\Models\ConsignmentReturn;
use App\Models\ConsignmentReturnItem;
use App\Models\ConsignmentSettlement;
use App\Models\ConsignmentSettlementItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\UnitConversion;
use Illuminate\Support\Facades\DB;

class ConsignmentService
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Generate sequential Consignment Receipt Number (CNR-YYYY-MM-0001)
     */
    public function generateReceiptNumber(): string
    {
        $yearMonth = now()->format('Y-m');
        $prefix = "CNR-{$yearMonth}-";
        $last = ConsignmentReceipt::where('receipt_number', 'like', "{$prefix}%")->orderByDesc('id')->first();
        $nextNumber = $last ? ((int) substr($last->receipt_number, -4) + 1) : 1;

        return $prefix.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate sequential Consignment Settlement Number (CNS-YYYY-MM-0001)
     */
    public function generateSettlementNumber(): string
    {
        $yearMonth = now()->format('Y-m');
        $prefix = "CNS-{$yearMonth}-";
        $last = ConsignmentSettlement::where('settlement_number', 'like', "{$prefix}%")->orderByDesc('id')->first();
        $nextNumber = $last ? ((int) substr($last->settlement_number, -4) + 1) : 1;

        return $prefix.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate sequential Consignment Return Number (CNRT-YYYY-MM-0001)
     */
    public function generateReturnNumber(): string
    {
        $yearMonth = now()->format('Y-m');
        $prefix = "CNRT-{$yearMonth}-";
        $last = ConsignmentReturn::where('return_number', 'like', "{$prefix}%")->orderByDesc('id')->first();
        $nextNumber = $last ? ((int) substr($last->return_number, -4) + 1) : 1;

        return $prefix.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Process Receipt of Consignment Goods (Penerimaan Barang Titipan)
     */
    public function processConsignmentReceipt(array $data): ConsignmentReceipt
    {
        return DB::transaction(function () use ($data) {
            $receiptNumber = $this->generateReceiptNumber();
            $supplierId = $data['supplier_id'];
            $warehouseId = $data['warehouse_id'];
            $receiptDate = $data['receipt_date'] ?? now()->toDateString();
            $userId = auth()->id();

            $receipt = ConsignmentReceipt::create([
                'receipt_number' => $receiptNumber,
                'supplier_id' => $supplierId,
                'warehouse_id' => $warehouseId,
                'user_id' => $userId,
                'receipt_date' => $receiptDate,
                'status' => 'received',
                'total_items' => count($data['items']),
                'total_quantity' => 0,
                'total_estimated_value' => 0,
                'notes' => $data['notes'] ?? null,
            ]);

            $totalQty = 0;
            $totalEstVal = 0;

            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $unitId = (int) $item['unit_id'];
                $qty = (float) $item['quantity'];
                $cost = (float) ($item['consignment_cost'] ?? $product->purchase_price ?? 0);

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

                $qtyBase = $qty * $conversionRatio;
                $lineSubtotal = $qty * $cost;

                $totalQty += $qty;
                $totalEstVal += $lineSubtotal;

                ConsignmentReceiptItem::create([
                    'consignment_receipt_id' => $receipt->id,
                    'product_id' => $product->id,
                    'unit_id' => $unitId,
                    'quantity' => $qty,
                    'conversion_ratio' => $conversionRatio,
                    'quantity_base' => $qtyBase,
                    'consignment_cost' => $cost,
                    'subtotal' => $lineSubtotal,
                    'notes' => $item['notes'] ?? null,
                ]);

                // Increase physical warehouse inventory using StockService
                $this->stockService->addStock(
                    $product->id,
                    $warehouseId,
                    $qtyBase,
                    'consignment_receipt',
                    $receipt->id,
                    $cost,
                    "Penerimaan Konsinyasi {$receiptNumber} dari Supplier",
                    $userId
                );
            }

            $receipt->update([
                'total_quantity' => $totalQty,
                'total_estimated_value' => $totalEstVal,
            ]);

            return $receipt->load(['supplier', 'warehouse', 'items.product', 'items.unit']);
        });
    }

    /**
     * Preview / Calculate Unsettled Consignment Sales for a Supplier and Date Range
     */
    public function calculateUnsettledSales(int $supplierId, string $startDate, string $endDate, ?int $warehouseId = null): array
    {
        $supplier = Supplier::findOrFail($supplierId);

        // Fetch unsettled sale items for this supplier in date range
        $saleItems = SaleItem::with(['product', 'unit', 'sale'])
            ->where('is_consignment', true)
            ->where('consignment_supplier_id', $supplierId)
            ->whereNull('consignment_settlement_id')
            ->whereHas('sale', function ($q) use ($startDate, $endDate, $warehouseId) {
                $q->where('status', 'completed')
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
                if ($warehouseId) {
                    $q->where('warehouse_id', $warehouseId);
                }
            })
            ->get();

        // Group by product
        $grouped = [];
        $totalSoldQty = 0;
        $totalGrossSales = 0;
        $totalSupplierPayable = 0;
        $totalStoreCommission = 0;

        foreach ($saleItems as $item) {
            $pId = $item->product_id;
            $uId = $item->unit_id;
            $key = "{$pId}_{$uId}";

            $qty = (float) $item->quantity;
            $subtotal = (float) $item->subtotal;
            $cCost = (float) $item->consignment_cost;
            $suppPayable = $qty * $cCost;
            $commission = max(0, $subtotal - $suppPayable);

            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product?->name ?? 'Produk',
                    'product_code' => $item->product?->code ?? '-',
                    'unit_id' => $item->unit_id,
                    'unit_name' => $item->unit?->name ?? 'Pcs',
                    'quantity_sold' => 0,
                    'selling_price' => (float) $item->unit_price,
                    'consignment_cost' => $cCost,
                    'gross_sales' => 0,
                    'supplier_payable' => 0,
                    'store_commission' => 0,
                    'sale_item_ids' => [],
                ];
            }

            $grouped[$key]['quantity_sold'] += $qty;
            $grouped[$key]['gross_sales'] += $subtotal;
            $grouped[$key]['supplier_payable'] += $suppPayable;
            $grouped[$key]['store_commission'] += $commission;
            $grouped[$key]['sale_item_ids'][] = $item->id;

            $totalSoldQty += $qty;
            $totalGrossSales += $subtotal;
            $totalSupplierPayable += $suppPayable;
            $totalStoreCommission += $commission;
        }

        return [
            'supplier' => $supplier,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'items' => array_values($grouped),
            'summary' => [
                'total_items_count' => count($grouped),
                'total_sold_quantity' => $totalSoldQty,
                'total_gross_sales' => $totalGrossSales,
                'total_supplier_amount' => $totalSupplierPayable,
                'total_store_commission' => $totalStoreCommission,
            ],
            'sale_item_ids' => $saleItems->pluck('id')->toArray(),
        ];
    }

    /**
     * Create Settlement / Rekonsiliasi & Penagihan Bagi Hasil
     */
    public function createSettlement(array $data): ConsignmentSettlement
    {
        return DB::transaction(function () use ($data) {
            $supplierId = (int) $data['supplier_id'];
            $startDate = $data['start_date'];
            $endDate = $data['end_date'];
            $warehouseId = $data['warehouse_id'] ?? null;
            $isPaidNow = ! empty($data['pay_now']) && $data['pay_now'] == '1';

            $calculation = $this->calculateUnsettledSales($supplierId, $startDate, $endDate, $warehouseId);

            if (empty($calculation['items'])) {
                throw new \Exception('Tidak ada transaksi penjualan konsinyasi yang belum diselesaikan pada periode tersebut.');
            }

            $settlementNumber = $this->generateSettlementNumber();
            $summary = $calculation['summary'];

            $settlement = ConsignmentSettlement::create([
                'settlement_number' => $settlementNumber,
                'supplier_id' => $supplierId,
                'warehouse_id' => $warehouseId,
                'user_id' => auth()->id(),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_sold_quantity' => $summary['total_sold_quantity'],
                'total_gross_sales' => $summary['total_gross_sales'],
                'total_supplier_amount' => $summary['total_supplier_amount'],
                'total_store_commission' => $summary['total_store_commission'],
                'paid_amount' => 0,
                'payment_status' => 'unpaid',
                'payment_method' => $data['payment_method'] ?? null,
                'payment_account_id' => $data['payment_account_id'] ?? null,
                'payment_date' => null,
                'status' => 'approved',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($calculation['items'] as $item) {
                ConsignmentSettlementItem::create([
                    'consignment_settlement_id' => $settlement->id,
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'],
                    'quantity_sold' => $item['quantity_sold'],
                    'selling_price' => $item['selling_price'],
                    'consignment_cost' => $item['consignment_cost'],
                    'gross_sales' => $item['gross_sales'],
                    'supplier_payable' => $item['supplier_payable'],
                    'store_commission' => $item['store_commission'],
                ]);
            }

            // Mark all matched sale items as settled with this settlement id
            if (! empty($calculation['sale_item_ids'])) {
                SaleItem::whereIn('id', $calculation['sale_item_ids'])->update([
                    'consignment_settlement_id' => $settlement->id,
                ]);
            }

            // If user checked "Pay Immediately"
            if ($isPaidNow && ! empty($data['payment_account_id'])) {
                $this->paySettlement($settlement, [
                    'account_id' => $data['payment_account_id'],
                    'payment_method' => $data['payment_method'] ?? 'cash',
                    'payment_date' => $data['payment_date'] ?? now()->toDateString(),
                    'reference_number' => $data['reference_number'] ?? null,
                    'notes' => 'Pelunasan langsung saat pembuatan settlement '.$settlementNumber,
                ]);
            }

            return $settlement->load(['supplier', 'warehouse', 'items.product', 'items.unit', 'paymentAccount']);
        });
    }

    /**
     * Process Settlement Payment from Financial Account (Kas/Bank)
     */
    public function paySettlement(ConsignmentSettlement $settlement, array $paymentData): ConsignmentSettlement
    {
        return DB::transaction(function () use ($settlement, $paymentData) {
            $account = Account::findOrFail($paymentData['account_id']);
            $amount = (float) $settlement->total_supplier_amount;

            if ($settlement->payment_status === 'paid') {
                throw new \Exception('Settlement konsinyasi ini sudah lunas.');
            }

            // 1. Deduct account balance
            $beforeBalance = (float) $account->current_balance;
            $afterBalance = $beforeBalance - $amount;
            $account->update(['current_balance' => $afterBalance]);

            // 2. Record Account Mutation
            AccountMutation::create([
                'account_id' => $account->id,
                'type' => 'out',
                'amount' => $amount,
                'before_balance' => $beforeBalance,
                'after_balance' => $afterBalance,
                'reference_type' => ConsignmentSettlement::class,
                'reference_id' => $settlement->id,
                'description' => "Bagi Hasil Konsinyasi {$settlement->settlement_number} ({$settlement->supplier->name})",
                'created_by' => auth()->id(),
            ]);

            // 3. Record CashFlow
            CashFlow::create([
                'cash_flow_number' => 'CF-CNS-'.now()->format('YmdHis'),
                'account_id' => $account->id,
                'type' => 'expense',
                'category' => 'Bagi Hasil Konsinyasi',
                'amount' => $amount,
                'transaction_date' => $paymentData['payment_date'] ?? now()->toDateString(),
                'reference_type' => ConsignmentSettlement::class,
                'reference_id' => $settlement->id,
                'description' => "Pembayaran bagi hasil konsinyasi {$settlement->settlement_number} kepada {$settlement->supplier->name}",
                'user_id' => auth()->id(),
            ]);

            // 4. Update settlement status
            $settlement->update([
                'paid_amount' => $amount,
                'payment_status' => 'paid',
                'payment_account_id' => $account->id,
                'payment_method' => $paymentData['payment_method'] ?? 'cash',
                'payment_date' => $paymentData['payment_date'] ?? now()->toDateString(),
            ]);

            return $settlement;
        });
    }

    /**
     * Process Return of Unsold Consignment Goods to Supplier
     */
    public function processConsignmentReturn(array $data): ConsignmentReturn
    {
        return DB::transaction(function () use ($data) {
            $returnNumber = $this->generateReturnNumber();
            $supplierId = $data['supplier_id'];
            $warehouseId = $data['warehouse_id'];
            $returnDate = $data['return_date'] ?? now()->toDateString();
            $userId = auth()->id();

            $return = ConsignmentReturn::create([
                'return_number' => $returnNumber,
                'supplier_id' => $supplierId,
                'warehouse_id' => $warehouseId,
                'user_id' => $userId,
                'return_date' => $returnDate,
                'total_items' => count($data['items']),
                'total_quantity' => 0,
                'status' => 'completed',
                'notes' => $data['notes'] ?? null,
            ]);

            $totalQty = 0;

            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $unitId = (int) $item['unit_id'];
                $qty = (float) $item['quantity'];

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

                $qtyBase = $qty * $conversionRatio;
                $totalQty += $qty;

                ConsignmentReturnItem::create([
                    'consignment_return_id' => $return->id,
                    'product_id' => $product->id,
                    'unit_id' => $unitId,
                    'quantity' => $qty,
                    'conversion_ratio' => $conversionRatio,
                    'quantity_base' => $qtyBase,
                    'reason' => $item['reason'] ?? 'Retur sisa konsinyasi',
                ]);

                // Deduct physical inventory
                $this->stockService->deductStock(
                    $product->id,
                    $warehouseId,
                    $qtyBase,
                    'consignment_return',
                    $return->id,
                    0,
                    "Retur Konsinyasi {$returnNumber} ke Supplier",
                    $userId
                );
            }

            $return->update([
                'total_quantity' => $totalQty,
            ]);

            return $return->load(['supplier', 'warehouse', 'items.product', 'items.unit']);
        });
    }
}
