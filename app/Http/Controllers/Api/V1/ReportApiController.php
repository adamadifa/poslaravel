<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\CashFlow;
use App\Models\CashierShift;
use App\Models\Payment;
use App\Models\ProductStock;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReceipt;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockOpname;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportApiController extends BaseApiController
{
    /**
     * 1. Laporan Ringkasan Penjualan & KPI (Sales Summary & Daily Trend)
     */
    public function getSalesSummary(Request $request): JsonResponse
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->toDateString());
        $warehouseId = $request->query('warehouse_id');
        $userId = $request->query('user_id');
        $paymentMethod = $request->query('payment_method');

        $query = Sale::query()
            ->whereDate('sale_date', '>=', $startDate)
            ->whereDate('sale_date', '<=', $endDate)
            ->where('status', '!=', 'void');

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }
        if ($userId) {
            $query->where('user_id', $userId);
        }
        if ($paymentMethod && $paymentMethod !== 'all') {
            $query->where('payment_method', $paymentMethod);
        }

        $totalSales = (float) (clone $query)->sum('grand_total');
        $totalTransactions = (int) (clone $query)->count();
        $totalSubtotal = (float) (clone $query)->sum('subtotal');
        $totalDiscount = (float) (clone $query)->sum('discount_amount');
        $totalTax = (float) (clone $query)->sum('tax_amount');
        $averageOrderValue = $totalTransactions > 0 ? ($totalSales / $totalTransactions) : 0.0;

        $totalCash = (float) (clone $query)->where('payment_method', 'cash')->sum('grand_total');
        $totalNonCash = $totalSales - $totalCash;

        // Calculate HPP (COGS) and Gross Profit
        $saleIds = (clone $query)->pluck('id');
        $totalHpp = (float) (SaleItem::whereIn('sale_id', $saleIds)
            ->select(DB::raw('SUM(quantity * unit_cost) as total_cogs'))
            ->value('total_cogs') ?? 0);

        $grossProfit = $totalSales - $totalHpp;
        $profitMarginPercent = $totalSales > 0 ? (($grossProfit / $totalSales) * 100) : 0.0;

        // Daily trend within range
        $dailyTrends = Sale::whereIn('id', $saleIds)
            ->select(
                DB::raw('DATE(sale_date) as date'),
                DB::raw('SUM(grand_total) as total_amount'),
                DB::raw('COUNT(id) as total_count')
            )
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get()
            ->map(fn ($row) => [
                'date' => $row->date,
                'formatted_date' => Carbon::parse($row->date)->translatedFormat('d M'),
                'total_amount' => (float) $row->total_amount,
                'total_count' => (int) $row->total_count,
            ]);

        return $this->sendResponse([
            'meta' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'kpis' => [
                'total_sales' => $totalSales,
                'total_transactions' => $totalTransactions,
                'total_subtotal' => $totalSubtotal,
                'total_discount' => $totalDiscount,
                'total_tax' => $totalTax,
                'average_order_value' => $averageOrderValue,
                'total_cash' => $totalCash,
                'total_non_cash' => $totalNonCash,
                'total_hpp' => $totalHpp,
                'gross_profit' => $grossProfit,
                'profit_margin_percent' => round($profitMarginPercent, 1),
            ],
            'daily_trends' => $dailyTrends,
        ], 'Ringkasan laporan penjualan berhasil dimuat.');
    }

    /**
     * 2. Laporan Penjualan per Produk (Top Performing Products & Profit Margins)
     */
    public function getSalesByProduct(Request $request): JsonResponse
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->toDateString());
        $categoryId = $request->query('category_id');
        $warehouseId = $request->query('warehouse_id');
        $search = $request->query('search') ?? $request->query('q');
        $limit = (int) ($request->query('limit', 20));

        $query = SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereDate('sales.sale_date', '>=', $startDate)
            ->whereDate('sales.sale_date', '<=', $endDate)
            ->where('sales.status', '!=', 'void');

        if ($warehouseId) {
            $query->where('sales.warehouse_id', $warehouseId);
        }
        if ($categoryId) {
            $query->where('products.category_id', $categoryId);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('products.name', 'like', "%{$search}%")
                    ->orWhere('products.code', 'like', "%{$search}%")
                    ->orWhere('products.barcode', 'like', "%{$search}%");
            });
        }

        $products = $query->select(
            'products.id as product_id',
            'products.name as product_name',
            'products.code as product_code',
            'categories.name as category_name',
            DB::raw('SUM(sale_items.quantity) as total_qty'),
            DB::raw('SUM(sale_items.subtotal) as total_revenue'),
            DB::raw('SUM(sale_items.quantity * sale_items.unit_cost) as total_cost'),
            DB::raw('SUM(sale_items.subtotal - (sale_items.quantity * sale_items.unit_cost)) as gross_profit')
        )
            ->groupBy('products.id', 'products.name', 'products.code', 'categories.name')
            ->orderByDesc(DB::raw('SUM(sale_items.subtotal)'))
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $rev = (float) $item->total_revenue;
                $cost = (float) $item->total_cost;
                $profit = (float) $item->gross_profit;
                $margin = $rev > 0 ? (($profit / $rev) * 100) : 0.0;

                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'product_code' => $item->product_code,
                    'category_name' => $item->category_name ?? 'Umum',
                    'total_qty' => (float) $item->total_qty,
                    'total_revenue' => $rev,
                    'total_cost' => $cost,
                    'gross_profit' => $profit,
                    'margin_percent' => round($margin, 1),
                ];
            });

        return $this->sendResponse($products, 'Laporan penjualan per produk berhasil dimuat.');
    }

    /**
     * 3. Laporan Penjualan per Kategori
     */
    public function getSalesByCategory(Request $request): JsonResponse
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->toDateString());
        $warehouseId = $request->query('warehouse_id');

        $query = SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereDate('sales.sale_date', '>=', $startDate)
            ->whereDate('sales.sale_date', '<=', $endDate)
            ->where('sales.status', '!=', 'void');

        if ($warehouseId) {
            $query->where('sales.warehouse_id', $warehouseId);
        }

        $categories = $query->select(
            DB::raw('COALESCE(categories.name, "Tanpa Kategori") as category_name'),
            DB::raw('COUNT(DISTINCT products.id) as unique_products_count'),
            DB::raw('SUM(sale_items.quantity) as total_qty'),
            DB::raw('SUM(sale_items.subtotal) as total_revenue'),
            DB::raw('SUM(sale_items.quantity * sale_items.unit_cost) as total_cost'),
            DB::raw('SUM(sale_items.subtotal - (sale_items.quantity * sale_items.unit_cost)) as gross_profit')
        )
            ->groupBy('categories.name')
            ->orderByDesc(DB::raw('SUM(sale_items.subtotal)'))
            ->get()
            ->map(function ($cat) {
                $rev = (float) $cat->total_revenue;
                $profit = (float) $cat->gross_profit;
                $margin = $rev > 0 ? (($profit / $rev) * 100) : 0.0;

                return [
                    'category_name' => $cat->category_name,
                    'products_count' => (int) $cat->unique_products_count,
                    'total_qty' => (float) $cat->total_qty,
                    'total_revenue' => $rev,
                    'gross_profit' => $profit,
                    'margin_percent' => round($margin, 1),
                ];
            });

        return $this->sendResponse($categories, 'Laporan penjualan per kategori berhasil dimuat.');
    }

    /**
     * 4. Laporan Laba Rugi Sederhana (Profit & Loss Statement)
     */
    public function getProfitLoss(Request $request): JsonResponse
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->toDateString());
        $warehouseId = $request->query('warehouse_id');

        // 1. Penjualan
        $salesQuery = Sale::whereDate('sale_date', '>=', $startDate)
            ->whereDate('sale_date', '<=', $endDate)
            ->where('status', '!=', 'void');

        if ($warehouseId) {
            $salesQuery->where('warehouse_id', $warehouseId);
        }

        $grossSales = (float) (clone $salesQuery)->sum('subtotal');
        $salesDiscounts = (float) (clone $salesQuery)->sum('discount_amount');
        $salesTax = (float) (clone $salesQuery)->sum('tax_amount');
        $netSales = (float) (clone $salesQuery)->sum('grand_total');

        // 2. HPP (Cost of Goods Sold)
        $saleIds = (clone $salesQuery)->pluck('id');
        $totalHpp = (float) (SaleItem::whereIn('sale_id', $saleIds)
            ->select(DB::raw('SUM(quantity * unit_cost) as total_cogs'))
            ->value('total_cogs') ?? 0);

        $grossProfit = $netSales - $totalHpp;

        // 3. Biaya Operasional (Buku Kas Keluar)
        $expenseQuery = CashFlow::whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->where('type', 'expense');

        $totalExpenses = (float) (clone $expenseQuery)->sum('amount');
        $expensesByCategory = (clone $expenseQuery)
            ->select('category', DB::raw('SUM(amount) as total_expense'))
            ->groupBy('category')
            ->orderByDesc('total_expense')
            ->get()
            ->map(fn ($e) => [
                'category' => $e->category,
                'amount' => (float) $e->total_expense,
            ]);

        // 4. Pendapatan Kas Lainnya (Buku Kas Masuk Non-Sale)
        $extraIncomeQuery = CashFlow::whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->where('type', 'income')
            ->whereNull('reference_type');

        $totalExtraIncome = (float) (clone $extraIncomeQuery)->sum('amount');

        // 5. Laba Bersih
        $netProfit = $grossProfit - $totalExpenses + $totalExtraIncome;
        $netProfitMargin = $netSales > 0 ? (($netProfit / $netSales) * 100) : 0.0;

        return $this->sendResponse([
            'meta' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'revenue' => [
                'gross_sales' => $grossSales,
                'discounts' => $salesDiscounts,
                'tax' => $salesTax,
                'net_sales' => $netSales,
                'extra_income' => $totalExtraIncome,
            ],
            'cogs' => [
                'total_hpp' => $totalHpp,
                'gross_profit' => $grossProfit,
                'gross_margin_percent' => $netSales > 0 ? round(($grossProfit / $netSales) * 100, 1) : 0.0,
            ],
            'expenses' => [
                'total_expenses' => $totalExpenses,
                'breakdown' => $expensesByCategory,
            ],
            'bottom_line' => [
                'net_profit' => $netProfit,
                'net_margin_percent' => round($netProfitMargin, 1),
                'is_profitable' => $netProfit >= 0,
            ],
        ], 'Laporan Laba Rugi (P&L) berhasil dimuat.');
    }

    /**
     * 5. Laporan Nilai Persediaan Stok (Inventory Valuation)
     */
    public function getInventoryValuation(Request $request): JsonResponse
    {
        $warehouseId = $request->query('warehouse_id');
        $categoryId = $request->query('category_id');

        $query = ProductStock::with(['product.category', 'product.baseUnit', 'warehouse'])
            ->join('products', 'product_stocks.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereNull('products.deleted_at')
            ->where('products.is_active', true);

        if ($warehouseId) {
            $query->where('product_stocks.warehouse_id', $warehouseId);
        }
        if ($categoryId) {
            $query->where('products.category_id', $categoryId);
        }

        $allStocks = $query->select('product_stocks.*')->get();

        $totalItemsCount = $allStocks->count();
        $totalStockQty = (float) $allStocks->sum('quantity');

        $totalValuation = (float) $allStocks->reduce(function ($carry, $stock) {
            $purchasePrice = (float) ($stock->product->purchase_price ?? 0);

            return $carry + ($stock->quantity * $purchasePrice);
        }, 0);

        $totalPotentialRevenue = (float) $allStocks->reduce(function ($carry, $stock) {
            $sellingPrice = (float) ($stock->product->selling_price ?? 0);

            return $carry + ($stock->quantity * $sellingPrice);
        }, 0);

        $lowStockCount = ProductStock::join('products', 'product_stocks.product_id', '=', 'products.id')
            ->whereRaw('product_stocks.quantity <= products.min_stock AND product_stocks.quantity > 0')
            ->count();

        $outOfStockCount = ProductStock::join('products', 'product_stocks.product_id', '=', 'products.id')
            ->where('product_stocks.quantity', '<=', 0)
            ->count();

        return $this->sendResponse([
            'summary' => [
                'total_products' => $totalItemsCount,
                'total_stock_qty' => $totalStockQty,
                'total_valuation_cost' => $totalValuation,
                'total_potential_revenue' => $totalPotentialRevenue,
                'potential_profit' => $totalPotentialRevenue - $totalValuation,
                'low_stock_items' => $lowStockCount,
                'out_of_stock_items' => $outOfStockCount,
            ],
        ], 'Laporan nilai persediaan stok berhasil dimuat.');
    }

    /**
     * 7. Laporan Penjualan per Pelanggan (Sales by Customer)
     */
    public function getSalesByCustomer(Request $request): JsonResponse
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->toDateString());
        $search = $request->query('search') ?? $request->query('q');

        $query = Sale::query()
            ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
            ->whereDate('sales.sale_date', '>=', $startDate)
            ->whereDate('sales.sale_date', '<=', $endDate)
            ->where('sales.status', '!=', 'void');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('customers.name', 'like', "%{$search}%")
                    ->orWhere('customers.phone', 'like', "%{$search}%")
                    ->orWhere('customers.code', 'like', "%{$search}%");
            });
        }

        $customerReport = $query->select(
            'customers.id as customer_id',
            DB::raw('COALESCE(customers.name, "Pelanggan Umum (Walk-in)") as customer_name'),
            'customers.phone as customer_phone',
            'customers.code as customer_code',
            DB::raw('COUNT(sales.id) as total_orders'),
            DB::raw('SUM(sales.grand_total) as total_spent'),
            DB::raw('AVG(sales.grand_total) as avg_spent'),
            DB::raw('MAX(sales.sale_date) as last_order_date')
        )
            ->groupBy('customers.id', 'customers.name', 'customers.phone', 'customers.code')
            ->orderByDesc(DB::raw('SUM(sales.grand_total)'))
            ->get()
            ->map(fn ($c) => [
                'customer_id' => $c->customer_id,
                'customer_name' => $c->customer_name,
                'customer_phone' => $c->customer_phone ?? '-',
                'customer_code' => $c->customer_code ?? '-',
                'total_orders' => (int) $c->total_orders,
                'total_spent' => (float) $c->total_spent,
                'avg_spent' => (float) $c->avg_spent,
                'last_order_date' => $c->last_order_date ? Carbon::parse($c->last_order_date)->format('d M Y') : '-',
            ]);

        return $this->sendResponse($customerReport, 'Laporan penjualan per pelanggan berhasil dimuat.');
    }

    /**
     * 8. Laporan Pembelian (Purchase Orders Summary)
     */
    public function getPurchases(Request $request): JsonResponse
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->toDateString());
        $supplierId = $request->query('supplier_id');
        $warehouseId = $request->query('warehouse_id');

        $query = PurchaseOrder::with(['supplier', 'warehouse', 'user'])
            ->whereDate('order_date', '>=', $startDate)
            ->whereDate('order_date', '<=', $endDate);

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        $baseQuery = clone $query;
        $totalPurchases = (float) (clone $baseQuery)->where('status', '!=', 'cancelled')->sum('grand_total');
        $totalOrders = (int) (clone $baseQuery)->where('status', '!=', 'cancelled')->count();
        $totalDiscount = (float) (clone $baseQuery)->where('status', '!=', 'cancelled')->sum('discount_amount');

        $supplierBreakdown = (clone $baseQuery)
            ->where('purchase_orders.status', '!=', 'cancelled')
            ->join('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id')
            ->select(
                'suppliers.id as supplier_id',
                'suppliers.name as supplier_name',
                'suppliers.code as supplier_code',
                DB::raw('COUNT(purchase_orders.id) as total_po_count'),
                DB::raw('SUM(purchase_orders.grand_total) as total_amount')
            )
            ->groupBy('suppliers.id', 'suppliers.name', 'suppliers.code')
            ->orderByDesc(DB::raw('SUM(purchase_orders.grand_total)'))
            ->get()
            ->map(fn ($s) => [
                'supplier_id' => $s->supplier_id,
                'supplier_name' => $s->supplier_name,
                'supplier_code' => $s->supplier_code,
                'total_po_count' => (int) $s->total_po_count,
                'total_amount' => (float) $s->total_amount,
            ]);

        return $this->sendResponse([
            'summary' => [
                'total_purchases' => $totalPurchases,
                'total_orders' => $totalOrders,
                'total_discount' => $totalDiscount,
            ],
            'supplier_breakdown' => $supplierBreakdown,
        ], 'Laporan pembelian berhasil dimuat.');
    }

    /**
     * 9. Laporan Hasil Stok Opname
     */
    public function getStockOpnames(Request $request): JsonResponse
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->toDateString());
        $warehouseId = $request->query('warehouse_id');

        $query = StockOpname::with(['warehouse', 'conductor', 'approver'])
            ->whereDate('opname_date', '>=', $startDate)
            ->whereDate('opname_date', '<=', $endDate);

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        $opnames = $query->latest('opname_date')->get()->map(fn ($o) => [
            'id' => $o->id,
            'opname_number' => $o->opname_number,
            'opname_date' => $o->opname_date ? Carbon::parse($o->opname_date)->format('d M Y') : '-',
            'warehouse_name' => $o->warehouse->name ?? '-',
            'conductor_name' => $o->conductor->name ?? '-',
            'status' => $o->status,
            'total_items_count' => (int) $o->total_items_count,
            'total_difference_qty' => (float) $o->total_difference_qty,
            'total_difference_cost' => (float) $o->total_difference_cost,
        ]);

        return $this->sendResponse($opnames, 'Laporan stok opname berhasil dimuat.');
    }

    /**
     * 10. Laporan Hutang Supplier (AP Aging)
     */
    public function getPayables(Request $request): JsonResponse
    {
        $supplierId = $request->query('supplier_id');

        $query = PurchaseReceipt::with(['purchaseOrder.supplier', 'warehouse'])
            ->where('status', 'received');

        if ($supplierId) {
            $query->whereHas('purchaseOrder', function ($q) use ($supplierId) {
                $q->where('supplier_id', $supplierId);
            });
        }

        $receipts = $query->latest('receipt_date')->get();
        $receiptIds = $receipts->pluck('id');

        $payments = Payment::where('payable_type', PurchaseReceipt::class)
            ->whereIn('payable_id', $receiptIds)
            ->select('payable_id', DB::raw('SUM(amount) as paid_amount'))
            ->groupBy('payable_id')
            ->pluck('paid_amount', 'payable_id');

        $payablesList = [];
        $totalPayable = 0.0;
        $totalPaid = 0.0;
        $totalOutstanding = 0.0;

        foreach ($receipts as $r) {
            $grandTotal = (float) ($r->purchaseOrder->grand_total ?? 0);
            $paid = (float) ($payments[$r->id] ?? 0);
            $outstanding = max(0, $grandTotal - $paid);

            if ($outstanding > 0) {
                $days = Carbon::now()->diffInDays($r->receipt_date ? Carbon::parse($r->receipt_date) : Carbon::now());
                $totalPayable += $grandTotal;
                $totalPaid += $paid;
                $totalOutstanding += $outstanding;

                $payablesList[] = [
                    'receipt_id' => $r->id,
                    'receipt_number' => $r->receipt_number,
                    'po_number' => $r->purchaseOrder->po_number ?? '-',
                    'supplier_name' => $r->purchaseOrder->supplier->name ?? 'Supplier',
                    'receipt_date' => $r->receipt_date ? Carbon::parse($r->receipt_date)->format('d M Y') : '-',
                    'days_outstanding' => $days,
                    'total_amount' => $grandTotal,
                    'paid_amount' => $paid,
                    'outstanding_amount' => $outstanding,
                ];
            }
        }

        return $this->sendResponse([
            'summary' => [
                'total_payable' => $totalPayable,
                'total_paid' => $totalPaid,
                'total_outstanding' => $totalOutstanding,
            ],
            'payables' => $payablesList,
        ], 'Laporan hutang supplier berhasil dimuat.');
    }

    /**
     * 11. Laporan Piutang Pelanggan (AR Aging)
     */
    public function getReceivables(Request $request): JsonResponse
    {
        $customerId = $request->query('customer_id');

        $query = Sale::with(['customer', 'warehouse'])
            ->where('payment_status', '!=', 'paid')
            ->where('status', '!=', 'void');

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        $sales = $query->latest('sale_date')->get();
        $saleIds = $sales->pluck('id');

        $payments = Payment::where('payable_type', Sale::class)
            ->whereIn('payable_id', $saleIds)
            ->select('payable_id', DB::raw('SUM(amount) as paid_amount'))
            ->groupBy('payable_id')
            ->pluck('paid_amount', 'payable_id');

        $receivablesList = [];
        $totalReceivable = 0.0;
        $totalPaid = 0.0;
        $totalOutstanding = 0.0;

        foreach ($sales as $s) {
            $grandTotal = (float) $s->grand_total;
            $directPaid = (float) ($s->paid_amount ?? 0);
            $extraPaid = (float) ($payments[$s->id] ?? 0);
            $totalPaidForSale = min($grandTotal, $directPaid + $extraPaid);
            $outstanding = max(0, $grandTotal - $totalPaidForSale);

            if ($outstanding > 0) {
                $days = Carbon::now()->diffInDays($s->sale_date ? Carbon::parse($s->sale_date) : Carbon::now());
                $totalReceivable += $grandTotal;
                $totalPaid += $totalPaidForSale;
                $totalOutstanding += $outstanding;

                $receivablesList[] = [
                    'sale_id' => $s->id,
                    'invoice_number' => $s->invoice_number,
                    'customer_name' => $s->customer->name ?? 'Pelanggan Umum',
                    'sale_date' => $s->sale_date ? Carbon::parse($s->sale_date)->format('d M Y') : '-',
                    'days_outstanding' => $days,
                    'total_amount' => $grandTotal,
                    'paid_amount' => $totalPaidForSale,
                    'outstanding_amount' => $outstanding,
                ];
            }
        }

        return $this->sendResponse([
            'summary' => [
                'total_receivable' => $totalReceivable,
                'total_paid' => $totalPaid,
                'total_outstanding' => $totalOutstanding,
            ],
            'receivables' => $receivablesList,
        ], 'Laporan piutang pelanggan berhasil dimuat.');
    }

    /**
     * 12. Laporan Arus Kas & Rekening Bank
     */
    public function getCashFlows(Request $request): JsonResponse
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->toDateString());
        $accountId = $request->query('account_id');

        $query = CashFlow::with(['account', 'creator'])
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate);

        if ($accountId) {
            $query->where('account_id', $accountId);
        }

        $income = (float) (clone $query)->where('type', 'income')->sum('amount');
        $expense = (float) (clone $query)->where('type', 'expense')->sum('amount');
        $netCash = $income - $expense;

        $items = $query->latest('transaction_date')->limit(50)->get()->map(fn ($cf) => [
            'id' => $cf->id,
            'cash_flow_number' => $cf->cash_flow_number,
            'type' => $cf->type,
            'category' => $cf->category,
            'amount' => (float) $cf->amount,
            'description' => $cf->description ?? '-',
            'transaction_date' => $cf->transaction_date ? Carbon::parse($cf->transaction_date)->format('d M Y') : '-',
            'account_name' => $cf->account->account_name ?? 'Kas Utama',
        ]);

        return $this->sendResponse([
            'summary' => [
                'total_income' => $income,
                'total_expense' => $expense,
                'net_cash' => $netCash,
            ],
            'cash_flows' => $items,
        ], 'Laporan arus kas berhasil dimuat.');
    }

    /**
     * 13. Laporan Rekap Shift Kasir
     */
    public function getCashierShifts(Request $request): JsonResponse
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->toDateString());
        $userId = $request->query('user_id');
        $warehouseId = $request->query('warehouse_id');
        $perPage = (int) ($request->query('per_page', 20));

        $query = CashierShift::with(['user', 'warehouse', 'expenses.user'])
            ->whereDate('opened_at', '>=', $startDate)
            ->whereDate('opened_at', '<=', $endDate);

        if ($userId) {
            $query->where('user_id', $userId);
        }
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        $totalSales = (float) (clone $query)->sum('total_sales');
        $totalExpenses = (float) (clone $query)->sum('total_expenses');
        $totalCount = (int) (clone $query)->count();
        $totalCashDifference = (float) (clone $query)->sum('cash_difference');

        $shifts = $query->latest('opened_at')->paginate($perPage);

        return $this->sendResponse([
            'shifts' => $shifts->items(),
            'pagination' => [
                'current_page' => $shifts->currentPage(),
                'last_page' => $shifts->lastPage(),
                'per_page' => $shifts->perPage(),
                'total' => $shifts->total(),
            ],
            'summary' => [
                'total_shifts' => $totalCount,
                'total_sales' => $totalSales,
                'total_expenses' => $totalExpenses,
                'total_cash_difference' => $totalCashDifference,
            ],
        ], 'Laporan rekap shift kasir berhasil dimuat.');
    }
}
