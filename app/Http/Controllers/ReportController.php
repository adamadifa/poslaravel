<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\CashFlow;
use App\Models\CashierShift;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\ProductStock;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReceipt;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockOpname;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\ReportExportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function __construct(
        protected ReportExportService $exportService
    ) {}

    /**
     * Report Hub Navigation / Overview Dashboard
     */
    public function index(Request $request)
    {
        return redirect()->route('reports.sales');
    }

    /**
     * 6.1 Laporan Penjualan (Harian / Transaksi)
     */
    public function sales(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $warehouseId = $request->get('warehouse_id');
        $userId = $request->get('user_id');
        $paymentStatus = $request->get('payment_status');
        $paymentMethod = $request->get('payment_method');

        $query = Sale::with(['user', 'customer', 'warehouse', 'items.product', 'items.unit'])
            ->whereDate('sale_date', '>=', $startDate)
            ->whereDate('sale_date', '<=', $endDate)
            ->where('status', '!=', 'void');

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }
        if ($userId) {
            $query->where('user_id', $userId);
        }
        if ($paymentStatus) {
            $query->where('payment_status', $paymentStatus);
        }
        if ($paymentMethod) {
            $query->where('payment_method', $paymentMethod);
        }

        $sales = (clone $query)->latest('sale_date')->paginate(15)->withQueryString();

        // Calculate KPIs for active filter
        $kpiQuery = clone $query;
        $totalSales = (clone $kpiQuery)->sum('grand_total');
        $totalTransactions = (clone $kpiQuery)->count();
        $totalSubtotal = (clone $kpiQuery)->sum('subtotal');
        $totalDiscount = (clone $kpiQuery)->sum('discount_amount');
        $totalTax = (clone $kpiQuery)->sum('tax_amount');
        $averageOrderValue = $totalTransactions > 0 ? ($totalSales / $totalTransactions) : 0;

        // Calculate total HPP & Profit for these sales
        $saleIds = (clone $kpiQuery)->pluck('id');
        $totalHpp = SaleItem::whereIn('sale_id', $saleIds)
            ->select(DB::raw('SUM(quantity * unit_cost) as total_cogs'))
            ->value('total_cogs') ?? 0;

        $grossProfit = $totalSales - $totalHpp;
        $profitMarginPercent = $totalSales > 0 ? (($grossProfit / $totalSales) * 100) : 0;

        // Daily Trend for Chart (Within filtered range)
        $chartData = Sale::whereIn('id', $saleIds)
            ->select(
                DB::raw('DATE(sale_date) as date'),
                DB::raw('SUM(grand_total) as total_amount'),
                DB::raw('COUNT(id) as total_count')
            )
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        $warehouses = Warehouse::where('is_active', true)->get();
        $cashiers = User::orderBy('name')->get();

        return view('reports.sales', compact(
            'sales',
            'totalSales',
            'totalTransactions',
            'totalSubtotal',
            'totalDiscount',
            'totalTax',
            'averageOrderValue',
            'totalHpp',
            'grossProfit',
            'profitMarginPercent',
            'chartData',
            'warehouses',
            'cashiers',
            'startDate',
            'endDate',
            'warehouseId',
            'userId',
            'paymentStatus',
            'paymentMethod'
        ));
    }

    /**
     * 6.1 Laporan Penjualan per Produk & Margin Laba Kotor
     */
    public function salesByProduct(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $categoryId = $request->get('category_id');
        $warehouseId = $request->get('warehouse_id');
        $search = $request->get('search');

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

        $baseQuery = clone $query;

        $report = (clone $baseQuery)->select(
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
            ->paginate(20)
            ->withQueryString();

        // Totals summary for all filtered records (no grouping, no order clause)
        $totalSummary = (clone $baseQuery)->select(
            DB::raw('SUM(sale_items.quantity) as sum_qty'),
            DB::raw('SUM(sale_items.subtotal) as sum_revenue'),
            DB::raw('SUM(sale_items.quantity * sale_items.unit_cost) as sum_cost')
        )->first();

        $sumRevenue = $totalSummary->sum_revenue ?? 0;
        $sumCost = $totalSummary->sum_cost ?? 0;
        $sumProfit = $sumRevenue - $sumCost;
        $sumMarginPercent = $sumRevenue > 0 ? (($sumProfit / $sumRevenue) * 100) : 0;

        $categories = Category::orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->get();

        return view('reports.sales_by_product', compact(
            'report',
            'categories',
            'warehouses',
            'sumRevenue',
            'sumCost',
            'sumProfit',
            'sumMarginPercent',
            'startDate',
            'endDate',
            'categoryId',
            'warehouseId',
            'search'
        ));
    }

    /**
     * Export Sales by Product Report to Excel (.xlsx)
     */
    public function exportSalesByProductExcel(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $categoryId = $request->get('category_id');
        $warehouseId = $request->get('warehouse_id');
        $search = $request->get('search');

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
            ->get();

        $warehouse = $warehouseId ? Warehouse::find($warehouseId) : null;
        $category = $categoryId ? Category::find($categoryId) : null;

        $meta = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'warehouse' => $warehouse ? $warehouse->name : 'Semua Cabang / Gudang',
        ];

        return $this->exportService->exportSalesByProduct($products, $meta);
    }

    /**
     * Export Sales by Product Report to PDF (Live Preview & Print)
     */
    public function exportSalesByProductPdf(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $categoryId = $request->get('category_id');
        $warehouseId = $request->get('warehouse_id');
        $search = $request->get('search');

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
            ->get();

        $warehouse = $warehouseId ? Warehouse::find($warehouseId) : null;
        $category = $categoryId ? Category::find($categoryId) : null;

        $pdf = Pdf::loadView('reports.sales_by_product_pdf', compact(
            'products',
            'startDate',
            'endDate',
            'warehouse',
            'category'
        ))->setPaper('a4', 'landscape');

        return $pdf->stream("Laporan_Penjualan_Produk_Margin_{$startDate}_sampai_{$endDate}.pdf");
    }

    /**
     * 6.1 Laporan Penjualan per Kategori
     */
    public function salesByCategory(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $warehouseId = $request->get('warehouse_id');

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

        $categoriesReport = $query->select(
            DB::raw('COALESCE(categories.name, "Tanpa Kategori") as category_name'),
            DB::raw('COUNT(DISTINCT products.id) as unique_products_count'),
            DB::raw('SUM(sale_items.quantity) as total_qty'),
            DB::raw('SUM(sale_items.subtotal) as total_revenue'),
            DB::raw('SUM(sale_items.quantity * sale_items.unit_cost) as total_cost'),
            DB::raw('SUM(sale_items.subtotal - (sale_items.quantity * sale_items.unit_cost)) as gross_profit')
        )
            ->groupBy('categories.name')
            ->orderByDesc(DB::raw('SUM(sale_items.subtotal)'))
            ->get();

        $warehouses = Warehouse::where('is_active', true)->get();

        return view('reports.sales_by_category', compact(
            'categoriesReport',
            'warehouses',
            'startDate',
            'endDate',
            'warehouseId'
        ));
    }

    /**
     * 6.1 Laporan Penjualan per Pelanggan (Customer)
     */
    public function salesByCustomer(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $search = $request->get('search');

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
            ->orderByDesc('total_spent')
            ->paginate(15)
            ->withQueryString();

        return view('reports.sales_by_customer', compact(
            'customerReport',
            'startDate',
            'endDate',
            'search'
        ));
    }

    /**
     * Export Sales Report to PDF
     */
    public function exportSalesPdf(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $warehouseId = $request->get('warehouse_id');

        $query = Sale::with(['user', 'customer', 'warehouse'])
            ->whereDate('sale_date', '>=', $startDate)
            ->whereDate('sale_date', '<=', $endDate)
            ->where('status', '!=', 'void');

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        $sales = $query->latest('sale_date')->get();
        $totalSales = $sales->sum('grand_total');
        $totalTransactions = $sales->count();
        $warehouse = $warehouseId ? Warehouse::find($warehouseId) : null;

        $pdf = Pdf::loadView('reports.sales_pdf', compact(
            'sales',
            'totalSales',
            'totalTransactions',
            'startDate',
            'endDate',
            'warehouse'
        ))->setPaper('a4', 'landscape');

        return $pdf->stream("Laporan_Penjualan_{$startDate}_sampai_{$endDate}.pdf");
    }

    /**
     * Export Sales Report to Excel (.xlsx)
     */
    public function exportSalesExcel(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $warehouseId = $request->get('warehouse_id');
        $userId = $request->get('user_id');
        $paymentStatus = $request->get('payment_status');
        $paymentMethod = $request->get('payment_method');

        $query = Sale::with(['user', 'customer', 'warehouse'])
            ->whereDate('sale_date', '>=', $startDate)
            ->whereDate('sale_date', '<=', $endDate)
            ->where('status', '!=', 'void');

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }
        if ($userId) {
            $query->where('user_id', $userId);
        }
        if ($paymentStatus) {
            $query->where('payment_status', $paymentStatus);
        }
        if ($paymentMethod) {
            $query->where('payment_method', $paymentMethod);
        }

        $sales = $query->latest('sale_date')->get();
        $warehouse = $warehouseId ? Warehouse::find($warehouseId) : null;

        $meta = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'warehouse' => $warehouse ? $warehouse->name : 'Semua Cabang / Gudang',
        ];

        return $this->exportService->exportSales($sales, $meta);
    }

    /**
     * 6.2 Laporan Pembelian per Supplier & Periode
     */
    public function purchases(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $supplierId = $request->get('supplier_id');
        $warehouseId = $request->get('warehouse_id');
        $status = $request->get('status');

        $query = PurchaseOrder::with(['supplier', 'warehouse', 'user', 'items.product'])
            ->whereDate('order_date', '>=', $startDate)
            ->whereDate('order_date', '<=', $endDate);

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }
        if ($status) {
            $query->where('status', $status);
        }

        $baseQuery = clone $query;
        $purchases = (clone $baseQuery)->latest('order_date')->paginate(15)->withQueryString();

        // Summary KPIs
        $totalPurchases = (clone $baseQuery)->where('status', '!=', 'cancelled')->sum('grand_total');
        $totalOrders = (clone $baseQuery)->where('status', '!=', 'cancelled')->count();
        $totalDiscount = (clone $baseQuery)->where('status', '!=', 'cancelled')->sum('discount_amount');
        $totalTax = (clone $baseQuery)->where('status', '!=', 'cancelled')->sum('tax_amount');
        $totalShipping = (clone $baseQuery)->where('status', '!=', 'cancelled')->sum('shipping_cost');

        // Purchases by Supplier breakdown
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
            ->get();

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->get();

        return view('reports.purchases', compact(
            'purchases',
            'totalPurchases',
            'totalOrders',
            'totalDiscount',
            'totalTax',
            'totalShipping',
            'supplierBreakdown',
            'suppliers',
            'warehouses',
            'startDate',
            'endDate',
            'supplierId',
            'warehouseId',
            'status'
        ));
    }

    /**
     * Export Purchase Orders Report to PDF
     */
    public function exportPurchasesPdf(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $supplierId = $request->get('supplier_id');
        $warehouseId = $request->get('warehouse_id');
        $status = $request->get('status');

        $query = PurchaseOrder::with(['supplier', 'warehouse', 'user'])
            ->whereDate('order_date', '>=', $startDate)
            ->whereDate('order_date', '<=', $endDate);

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }
        if ($status) {
            $query->where('status', $status);
        }

        $purchases = $query->latest('order_date')->get();
        $totalPurchases = $purchases->where('status', '!=', 'cancelled')->sum('grand_total');
        $totalOrders = $purchases->where('status', '!=', 'cancelled')->count();
        $supplier = $supplierId ? Supplier::find($supplierId) : null;
        $warehouse = $warehouseId ? Warehouse::find($warehouseId) : null;

        $pdf = Pdf::loadView('reports.purchases_pdf', compact(
            'purchases',
            'totalPurchases',
            'totalOrders',
            'startDate',
            'endDate',
            'supplier',
            'warehouse'
        ))->setPaper('a4', 'landscape');

        return $pdf->stream("Laporan_Pembelian_{$startDate}_sampai_{$endDate}.pdf");
    }

    /**
     * Export Purchase Orders Report to Excel (.xlsx)
     */
    public function exportPurchasesExcel(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $supplierId = $request->get('supplier_id');
        $warehouseId = $request->get('warehouse_id');
        $status = $request->get('status');

        $query = PurchaseOrder::with(['supplier', 'warehouse', 'user'])
            ->whereDate('order_date', '>=', $startDate)
            ->whereDate('order_date', '<=', $endDate);

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }
        if ($status) {
            $query->where('status', $status);
        }

        $purchases = $query->latest('order_date')->get();
        $warehouse = $warehouseId ? Warehouse::find($warehouseId) : null;
        $supplier = $supplierId ? Supplier::find($supplierId) : null;

        $meta = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'warehouse' => $warehouse ? $warehouse->name : ($supplier ? 'Supplier: '.$supplier->name : 'Semua Gudang'),
        ];

        return $this->exportService->exportPurchases($purchases, $meta);
    }

    /**
     * 6.3 Laporan Stok Saat Ini & Nilai Persediaan (Qty x HPP)
     */
    public function stocks(Request $request)
    {
        $warehouseId = $request->get('warehouse_id');
        $categoryId = $request->get('category_id');
        $search = $request->get('search');
        $filterStock = $request->get('filter_stock'); // all, low, out

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
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('products.name', 'like', "%{$search}%")
                    ->orWhere('products.code', 'like', "%{$search}%")
                    ->orWhere('products.barcode', 'like', "%{$search}%");
            });
        }
        if ($filterStock === 'low') {
            $query->whereRaw('product_stocks.quantity <= products.min_stock AND product_stocks.quantity > 0');
        } elseif ($filterStock === 'out') {
            $query->where('product_stocks.quantity', '<=', 0);
        }

        $baseQuery = clone $query;

        $stocks = (clone $baseQuery)
            ->select('product_stocks.*')
            ->orderBy('products.name')
            ->paginate(20)
            ->withQueryString();

        // Calculate Totals / Inventory Valuation
        $allStocks = (clone $baseQuery)->get();
        $totalItemsCount = $allStocks->count();
        $totalStockQty = $allStocks->sum('quantity');

        // Nilai persediaan: Sum(quantity * purchase_price)
        $totalValuation = $allStocks->reduce(function ($carry, $stock) {
            $purchasePrice = $stock->product->purchase_price ?? 0;

            return $carry + ($stock->quantity * $purchasePrice);
        }, 0);

        // Potensi nilai jual: Sum(quantity * selling_price)
        $totalPotentialRevenue = $allStocks->reduce(function ($carry, $stock) {
            $sellingPrice = $stock->product->selling_price ?? 0;

            return $carry + ($stock->quantity * $sellingPrice);
        }, 0);

        // Total produk kritis / habis
        $lowStockCount = ProductStock::join('products', 'product_stocks.product_id', '=', 'products.id')
            ->whereRaw('product_stocks.quantity <= products.min_stock')
            ->count();

        $warehouses = Warehouse::where('is_active', true)->get();
        $categories = Category::orderBy('name')->get();

        return view('reports.stocks', compact(
            'stocks',
            'totalItemsCount',
            'totalStockQty',
            'totalValuation',
            'totalPotentialRevenue',
            'lowStockCount',
            'warehouses',
            'categories',
            'warehouseId',
            'categoryId',
            'search',
            'filterStock'
        ));
    }

    /**
     * 6.3 Laporan Hasil Stok Opname
     */
    public function stockOpnames(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $warehouseId = $request->get('warehouse_id');
        $status = $request->get('status');

        $query = StockOpname::with(['warehouse', 'conductor', 'approver', 'items.product'])
            ->whereDate('opname_date', '>=', $startDate)
            ->whereDate('opname_date', '<=', $endDate);

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }
        if ($status) {
            $query->where('status', $status);
        }

        $opnames = (clone $query)->latest('opname_date')->paginate(15)->withQueryString();

        $warehouses = Warehouse::where('is_active', true)->get();

        return view('reports.stock_opnames', compact(
            'opnames',
            'warehouses',
            'startDate',
            'endDate',
            'warehouseId',
            'status'
        ));
    }

    /**
     * Export Stock Report to PDF
     */
    public function exportStocksPdf(Request $request)
    {
        $warehouseId = $request->get('warehouse_id');
        $categoryId = $request->get('categoryId');
        $search = $request->get('search');
        $filterStock = $request->get('filter_stock');

        $query = ProductStock::with(['product.category', 'product.baseUnit', 'warehouse'])
            ->join('products', 'product_stocks.product_id', '=', 'products.id')
            ->whereNull('products.deleted_at')
            ->where('products.is_active', true);

        if ($warehouseId) {
            $query->where('product_stocks.warehouse_id', $warehouseId);
        }
        if ($categoryId) {
            $query->where('products.category_id', $categoryId);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('products.name', 'like', "%{$search}%")
                    ->orWhere('products.code', 'like', "%{$search}%");
            });
        }
        if ($filterStock === 'low') {
            $query->whereRaw('product_stocks.quantity <= products.min_stock AND product_stocks.quantity > 0');
        } elseif ($filterStock === 'out') {
            $query->where('product_stocks.quantity', '<=', 0);
        }

        $stocks = $query->select('product_stocks.*')->orderBy('products.name')->get();
        $totalValuation = $stocks->reduce(fn ($c, $s) => $c + ($s->quantity * ($s->product->purchase_price ?? 0)), 0);
        $totalQty = $stocks->sum('quantity');
        $warehouse = $warehouseId ? Warehouse::find($warehouseId) : null;

        $pdf = Pdf::loadView('reports.stocks_pdf', compact(
            'stocks',
            'totalValuation',
            'totalQty',
            'warehouse'
        ))->setPaper('a4', 'landscape');

        return $pdf->stream('Laporan_Nilai_Persediaan_Stok_'.now()->format('Ymd').'.pdf');
    }

    /**
     * Export Stock Report to Excel (.xlsx)
     */
    public function exportStocksExcel(Request $request)
    {
        $warehouseId = $request->get('warehouse_id');
        $categoryId = $request->get('category_id');
        $search = $request->get('search');
        $filterStock = $request->get('filter_stock');

        $query = ProductStock::with(['product.category', 'product.baseUnit', 'warehouse'])
            ->join('products', 'product_stocks.product_id', '=', 'products.id')
            ->whereNull('products.deleted_at')
            ->where('products.is_active', true);

        if ($warehouseId) {
            $query->where('product_stocks.warehouse_id', $warehouseId);
        }
        if ($categoryId) {
            $query->where('products.category_id', $categoryId);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('products.name', 'like', "%{$search}%")
                    ->orWhere('products.code', 'like', "%{$search}%");
            });
        }
        if ($filterStock === 'low') {
            $query->whereRaw('product_stocks.quantity <= products.min_stock AND product_stocks.quantity > 0');
        } elseif ($filterStock === 'out') {
            $query->where('product_stocks.quantity', '<=', 0);
        }

        $stocks = $query->select('product_stocks.*')->orderBy('products.name')->get();
        $warehouse = $warehouseId ? Warehouse::find($warehouseId) : null;

        $meta = [
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'warehouse' => $warehouse ? $warehouse->name : 'Semua Cabang / Gudang',
        ];

        return $this->exportService->exportStocks($stocks, $meta);
    }

    /**
     * 6.4 Laporan Hutang Supplier (AP Aging & Outstanding)
     */
    public function payables(Request $request)
    {
        $supplierId = $request->get('supplier_id');

        // Fetch all purchase receipts or purchase orders with pending payment
        $query = PurchaseReceipt::with(['purchaseOrder.supplier', 'warehouse'])
            ->where('status', 'received');

        if ($supplierId) {
            $query->whereHas('purchaseOrder', function ($q) use ($supplierId) {
                $q->where('supplier_id', $supplierId);
            });
        }

        $receipts = $query->latest('receipt_date')->get();

        // Calculate total payments made per receipt
        $receiptIds = $receipts->pluck('id');
        $payments = Payment::where('payable_type', PurchaseReceipt::class)
            ->whereIn('payable_id', $receiptIds)
            ->select('payable_id', DB::raw('SUM(amount) as paid_amount'))
            ->groupBy('payable_id')
            ->pluck('paid_amount', 'payable_id');

        $payablesData = collect();
        $totalPayable = 0;
        $totalPaid = 0;
        $totalOutstanding = 0;
        $agingSummary = [
            '0_30' => 0,
            '31_60' => 0,
            '61_90' => 0,
            '90_plus' => 0,
        ];

        foreach ($receipts as $r) {
            $grandTotal = $r->purchaseOrder->grand_total ?? 0;
            $paid = (float) ($payments[$r->id] ?? 0);
            $outstanding = max(0, $grandTotal - $paid);

            if ($outstanding > 0) {
                $days = Carbon::now()->diffInDays($r->receipt_date ? Carbon::parse($r->receipt_date) : Carbon::now());

                if ($days <= 30) {
                    $agingSummary['0_30'] += $outstanding;
                    $agingGroup = '0 - 30 Hari';
                } elseif ($days <= 60) {
                    $agingSummary['31_60'] += $outstanding;
                    $agingGroup = '31 - 60 Hari';
                } elseif ($days <= 90) {
                    $agingSummary['61_90'] += $outstanding;
                    $agingGroup = '61 - 90 Hari';
                } else {
                    $agingSummary['90_plus'] += $outstanding;
                    $agingGroup = '> 90 Hari';
                }

                $totalPayable += $grandTotal;
                $totalPaid += $paid;
                $totalOutstanding += $outstanding;

                $payablesData->push((object) [
                    'receipt_id' => $r->id,
                    'receipt_number' => $r->receipt_number,
                    'po_number' => $r->purchaseOrder->po_number ?? '-',
                    'supplier_name' => $r->purchaseOrder->supplier->name ?? 'Supplier',
                    'receipt_date' => $r->receipt_date,
                    'days_outstanding' => $days,
                    'aging_group' => $agingGroup,
                    'total_amount' => $grandTotal,
                    'paid_amount' => $paid,
                    'outstanding_amount' => $outstanding,
                ]);
            }
        }

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('reports.payables', compact(
            'payablesData',
            'totalPayable',
            'totalPaid',
            'totalOutstanding',
            'agingSummary',
            'suppliers',
            'supplierId'
        ));
    }

    /**
     * 6.4 Laporan Piutang Pelanggan (AR Aging & Outstanding)
     */
    public function receivables(Request $request)
    {
        $customerId = $request->get('customer_id');

        $query = Sale::with(['customer', 'warehouse'])
            ->where('payment_status', '!=', 'paid')
            ->where('status', '!=', 'void');

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        $sales = $query->latest('sale_date')->get();

        // Calculate paid amounts
        $saleIds = $sales->pluck('id');
        $payments = Payment::where('payable_type', Sale::class)
            ->whereIn('payable_id', $saleIds)
            ->select('payable_id', DB::raw('SUM(amount) as paid_amount'))
            ->groupBy('payable_id')
            ->pluck('paid_amount', 'payable_id');

        $receivablesData = collect();
        $totalReceivable = 0;
        $totalPaid = 0;
        $totalOutstanding = 0;
        $agingSummary = [
            '0_30' => 0,
            '31_60' => 0,
            '61_90' => 0,
            '90_plus' => 0,
        ];

        foreach ($sales as $s) {
            $grandTotal = (float) $s->grand_total;
            $directPaid = (float) ($s->paid_amount ?? 0);
            $extraPaid = (float) ($payments[$s->id] ?? 0);
            $totalPaidForSale = min($grandTotal, $directPaid + $extraPaid);
            $outstanding = max(0, $grandTotal - $totalPaidForSale);

            if ($outstanding > 0) {
                $days = Carbon::now()->diffInDays($s->sale_date ? Carbon::parse($s->sale_date) : Carbon::now());

                if ($days <= 30) {
                    $agingSummary['0_30'] += $outstanding;
                    $agingGroup = '0 - 30 Hari';
                } elseif ($days <= 60) {
                    $agingSummary['31_60'] += $outstanding;
                    $agingGroup = '31 - 60 Hari';
                } elseif ($days <= 90) {
                    $agingSummary['61_90'] += $outstanding;
                    $agingGroup = '61 - 90 Hari';
                } else {
                    $agingSummary['90_plus'] += $outstanding;
                    $agingGroup = '> 90 Hari';
                }

                $totalReceivable += $grandTotal;
                $totalPaid += $totalPaidForSale;
                $totalOutstanding += $outstanding;

                $receivablesData->push((object) [
                    'sale_id' => $s->id,
                    'invoice_number' => $s->invoice_number,
                    'customer_name' => $s->customer->name ?? 'Pelanggan Umum',
                    'customer_phone' => $s->customer->phone ?? '-',
                    'sale_date' => $s->sale_date,
                    'days_outstanding' => $days,
                    'aging_group' => $agingGroup,
                    'total_amount' => $grandTotal,
                    'paid_amount' => $totalPaidForSale,
                    'outstanding_amount' => $outstanding,
                ]);
            }
        }

        $customers = Customer::orderBy('name')->get();

        return view('reports.receivables', compact(
            'receivablesData',
            'totalReceivable',
            'totalPaid',
            'totalOutstanding',
            'agingSummary',
            'customers',
            'customerId'
        ));
    }

    /**
     * 6.4 Laporan Mutasi Arus Kas / Bank
     */
    public function cashFlows(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $accountId = $request->get('account_id');
        $type = $request->get('type');

        $query = CashFlow::with(['account', 'creator'])
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate);

        if ($accountId) {
            $query->where('account_id', $accountId);
        }
        if ($type) {
            $query->where('type', $type);
        }

        $baseQuery = clone $query;
        $cashFlows = (clone $baseQuery)->latest('transaction_date')->latest('id')->paginate(20)->withQueryString();

        $totalCashIn = (clone $baseQuery)->where('type', 'in')->sum('amount');
        $totalCashOut = (clone $baseQuery)->where('type', 'out')->sum('amount');
        $netCashFlow = $totalCashIn - $totalCashOut;

        $accounts = Account::where('is_active', true)->orderBy('name')->get();

        return view('reports.cash_flows', compact(
            'cashFlows',
            'totalCashIn',
            'totalCashOut',
            'netCashFlow',
            'accounts',
            'startDate',
            'endDate',
            'accountId',
            'type'
        ));
    }

    /**
     * 6.4 Laporan Laba Rugi Sederhana (Pendapatan - HPP - Biaya)
     */
    public function profitLoss(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $warehouseId = $request->get('warehouse_id');

        // 1. Total Penjualan & Diskon
        $salesQuery = Sale::whereDate('sale_date', '>=', $startDate)
            ->whereDate('sale_date', '<=', $endDate)
            ->where('status', '!=', 'void');

        if ($warehouseId) {
            $salesQuery->where('warehouse_id', $warehouseId);
        }

        $grossSales = (clone $salesQuery)->sum('subtotal');
        $salesDiscounts = (clone $salesQuery)->sum('discount_amount');
        $netSales = (clone $salesQuery)->sum('grand_total');

        // 2. Total HPP (Cost of Goods Sold)
        $saleIds = (clone $salesQuery)->pluck('id');
        $totalHpp = SaleItem::whereIn('sale_id', $saleIds)
            ->select(DB::raw('SUM(quantity * unit_cost) as total_cogs'))
            ->value('total_cogs') ?? 0;

        $grossProfit = $netSales - $totalHpp;

        // 3. Biaya Operasional / Kas Keluar
        $expenseQuery = CashFlow::whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->where('type', 'out');

        $totalExpenses = (clone $expenseQuery)->sum('amount');
        $expensesByCategory = (clone $expenseQuery)
            ->select('category', DB::raw('SUM(amount) as total_expense'))
            ->groupBy('category')
            ->get();

        // 4. Laba Bersih (Net Profit)
        $netProfit = $grossProfit - $totalExpenses;
        $netProfitMargin = $netSales > 0 ? (($netProfit / $netSales) * 100) : 0;

        $warehouses = Warehouse::where('is_active', true)->get();

        return view('reports.profit_loss', compact(
            'grossSales',
            'salesDiscounts',
            'netSales',
            'totalHpp',
            'grossProfit',
            'totalExpenses',
            'expensesByCategory',
            'netProfit',
            'netProfitMargin',
            'warehouses',
            'startDate',
            'endDate',
            'warehouseId'
        ));
    }

    /**
     * 6.4 Rekap Shift Kasir
     */
    public function cashierShifts(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $userId = $request->get('user_id');
        $warehouseId = $request->get('warehouse_id');

        $query = CashierShift::with(['user', 'warehouse', 'expenses.user', 'agentTransactions.account'])
            ->whereDate('opened_at', '>=', $startDate)
            ->whereDate('opened_at', '<=', $endDate);

        if ($userId) {
            $query->where('user_id', $userId);
        }
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        $shifts = (clone $query)->latest('opened_at')->paginate(15)->withQueryString();

        $totalShiftSales = (clone $query)->sum('total_sales');
        $totalShiftExpenses = (clone $query)->sum('total_expenses');
        $totalShiftCount = (clone $query)->count();
        $totalCashDifference = (clone $query)->sum('cash_difference');
        $totalAgentCashIn = (clone $query)->sum('total_agent_cash_in');
        $totalAgentCashOut = (clone $query)->sum('total_agent_cash_out');
        $totalAgentProfit = (clone $query)->sum('total_agent_profit');

        $cashiers = User::orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->get();

        return view('reports.cashier_shifts', compact(
            'shifts',
            'totalShiftSales',
            'totalShiftExpenses',
            'totalShiftCount',
            'totalCashDifference',
            'totalAgentCashIn',
            'totalAgentCashOut',
            'totalAgentProfit',
            'cashiers',
            'warehouses',
            'startDate',
            'endDate',
            'userId',
            'warehouseId'
        ));
    }

    /**
     * Export Sales by Category to Excel (.xlsx)
     */
    public function exportSalesByCategoryExcel(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $warehouseId = $request->get('warehouse_id');

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

        $categoriesReport = $query->select(
            DB::raw('COALESCE(categories.name, "Tanpa Kategori") as category_name'),
            DB::raw('COUNT(DISTINCT products.id) as unique_products_count'),
            DB::raw('SUM(sale_items.quantity) as total_qty'),
            DB::raw('SUM(sale_items.subtotal) as total_revenue'),
            DB::raw('SUM(sale_items.quantity * sale_items.unit_cost) as total_cost'),
            DB::raw('SUM(sale_items.subtotal - (sale_items.quantity * sale_items.unit_cost)) as gross_profit')
        )
            ->groupBy('categories.name')
            ->orderByDesc(DB::raw('SUM(sale_items.subtotal)'))
            ->get();

        $warehouse = $warehouseId ? Warehouse::find($warehouseId) : null;

        $meta = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'warehouse' => $warehouse ? $warehouse->name : 'Semua Cabang / Gudang',
        ];

        return $this->exportService->exportSalesByCategory($categoriesReport, $meta);
    }

    /**
     * Export Sales by Category to PDF
     */
    public function exportSalesByCategoryPdf(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $warehouseId = $request->get('warehouse_id');

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

        $categoriesReport = $query->select(
            DB::raw('COALESCE(categories.name, "Tanpa Kategori") as category_name'),
            DB::raw('COUNT(DISTINCT products.id) as unique_products_count'),
            DB::raw('SUM(sale_items.quantity) as total_qty'),
            DB::raw('SUM(sale_items.subtotal) as total_revenue'),
            DB::raw('SUM(sale_items.quantity * sale_items.unit_cost) as total_cost'),
            DB::raw('SUM(sale_items.subtotal - (sale_items.quantity * sale_items.unit_cost)) as gross_profit')
        )
            ->groupBy('categories.name')
            ->orderByDesc(DB::raw('SUM(sale_items.subtotal)'))
            ->get();

        $warehouse = $warehouseId ? Warehouse::find($warehouseId) : null;

        $pdf = Pdf::loadView('reports.sales_by_category_pdf', compact(
            'categoriesReport',
            'startDate',
            'endDate',
            'warehouse'
        ))->setPaper('a4', 'landscape');

        return $pdf->stream("Laporan_Penjualan_Kategori_{$startDate}_sampai_{$endDate}.pdf");
    }

    /**
     * Export Sales by Customer to Excel (.xlsx)
     */
    public function exportSalesByCustomerExcel(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $search = $request->get('search');

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

        $customers = $query->select(
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
            ->orderByDesc('total_spent')
            ->get();

        $meta = [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        return $this->exportService->exportSalesByCustomer($customers, $meta);
    }

    /**
     * Export Sales by Customer to PDF
     */
    public function exportSalesByCustomerPdf(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $search = $request->get('search');

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

        $customers = $query->select(
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
            ->orderByDesc('total_spent')
            ->get();

        $pdf = Pdf::loadView('reports.sales_by_customer_pdf', compact(
            'customers',
            'startDate',
            'endDate'
        ))->setPaper('a4', 'landscape');

        return $pdf->stream("Laporan_Penjualan_Pelanggan_{$startDate}_sampai_{$endDate}.pdf");
    }

    /**
     * Export Stock Opnames to Excel (.xlsx)
     */
    public function exportStockOpnamesExcel(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $warehouseId = $request->get('warehouse_id');
        $status = $request->get('status');

        $query = StockOpname::with(['warehouse', 'conductor', 'approver'])
            ->whereDate('opname_date', '>=', $startDate)
            ->whereDate('opname_date', '<=', $endDate);

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }
        if ($status) {
            $query->where('status', $status);
        }

        $opnames = $query->latest('opname_date')->get();
        $warehouse = $warehouseId ? Warehouse::find($warehouseId) : null;

        $meta = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'warehouse' => $warehouse ? $warehouse->name : 'Semua Gudang',
        ];

        return $this->exportService->exportStockOpnames($opnames, $meta);
    }

    /**
     * Export Stock Opnames to PDF
     */
    public function exportStockOpnamesPdf(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $warehouseId = $request->get('warehouse_id');
        $status = $request->get('status');

        $query = StockOpname::with(['warehouse', 'conductor', 'approver'])
            ->whereDate('opname_date', '>=', $startDate)
            ->whereDate('opname_date', '<=', $endDate);

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }
        if ($status) {
            $query->where('status', $status);
        }

        $opnames = $query->latest('opname_date')->get();
        $warehouse = $warehouseId ? Warehouse::find($warehouseId) : null;

        $pdf = Pdf::loadView('reports.stock_opnames_pdf', compact(
            'opnames',
            'startDate',
            'endDate',
            'warehouse'
        ))->setPaper('a4', 'landscape');

        return $pdf->stream("Laporan_Stok_Opname_{$startDate}_sampai_{$endDate}.pdf");
    }

    /**
     * Export Payables to Excel (.xlsx)
     */
    public function exportPayablesExcel(Request $request)
    {
        $supplierId = $request->get('supplier_id');

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

        $payablesData = collect();

        foreach ($receipts as $r) {
            $grandTotal = $r->purchaseOrder->grand_total ?? 0;
            $paid = (float) ($payments[$r->id] ?? 0);
            $outstanding = max(0, $grandTotal - $paid);

            if ($outstanding > 0) {
                $days = Carbon::now()->diffInDays($r->receipt_date ? Carbon::parse($r->receipt_date) : Carbon::now());
                $agingGroup = $days <= 30 ? '0-30 Hari' : ($days <= 60 ? '31-60 Hari' : ($days <= 90 ? '61-90 Hari' : '>90 Hari'));

                $payablesData->push((object) [
                    'receipt_id' => $r->id,
                    'receipt_number' => $r->receipt_number,
                    'po_number' => $r->purchaseOrder->po_number ?? '-',
                    'supplier_name' => $r->purchaseOrder->supplier->name ?? 'Supplier',
                    'receipt_date' => $r->receipt_date,
                    'days_outstanding' => $days,
                    'aging_group' => $agingGroup,
                    'total_amount' => $grandTotal,
                    'paid_amount' => $paid,
                    'outstanding_amount' => $outstanding,
                ]);
            }
        }

        $supplier = $supplierId ? Supplier::find($supplierId) : null;
        $meta = [
            'warehouse' => $supplier ? 'Supplier: '.$supplier->name : 'Semua Supplier',
        ];

        return $this->exportService->exportPayables($payablesData, $meta);
    }

    /**
     * Export Payables to PDF
     */
    public function exportPayablesPdf(Request $request)
    {
        $supplierId = $request->get('supplier_id');

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

        $payablesData = collect();

        foreach ($receipts as $r) {
            $grandTotal = $r->purchaseOrder->grand_total ?? 0;
            $paid = (float) ($payments[$r->id] ?? 0);
            $outstanding = max(0, $grandTotal - $paid);

            if ($outstanding > 0) {
                $days = Carbon::now()->diffInDays($r->receipt_date ? Carbon::parse($r->receipt_date) : Carbon::now());

                $payablesData->push((object) [
                    'receipt_id' => $r->id,
                    'receipt_number' => $r->receipt_number,
                    'po_number' => $r->purchaseOrder->po_number ?? '-',
                    'supplier_name' => $r->purchaseOrder->supplier->name ?? 'Supplier',
                    'receipt_date' => $r->receipt_date,
                    'days_outstanding' => $days,
                    'total_amount' => $grandTotal,
                    'paid_amount' => $paid,
                    'outstanding_amount' => $outstanding,
                ]);
            }
        }

        $supplier = $supplierId ? Supplier::find($supplierId) : null;

        $pdf = Pdf::loadView('reports.payables_pdf', compact(
            'payablesData',
            'supplier'
        ))->setPaper('a4', 'landscape');

        return $pdf->stream('Laporan_Hutang_Supplier_'.now()->format('Ymd').'.pdf');
    }

    /**
     * Export Receivables to Excel (.xlsx)
     */
    public function exportReceivablesExcel(Request $request)
    {
        $customerId = $request->get('customer_id');

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

        $receivablesData = collect();

        foreach ($sales as $s) {
            $grandTotal = (float) $s->grand_total;
            $directPaid = (float) ($s->paid_amount ?? 0);
            $extraPaid = (float) ($payments[$s->id] ?? 0);
            $totalPaidForSale = min($grandTotal, $directPaid + $extraPaid);
            $outstanding = max(0, $grandTotal - $totalPaidForSale);

            if ($outstanding > 0) {
                $days = Carbon::now()->diffInDays($s->sale_date ? Carbon::parse($s->sale_date) : Carbon::now());
                $agingGroup = $days <= 30 ? '0-30 Hari' : ($days <= 60 ? '31-60 Hari' : ($days <= 90 ? '61-90 Hari' : '>90 Hari'));

                $receivablesData->push((object) [
                    'sale_id' => $s->id,
                    'invoice_number' => $s->invoice_number,
                    'customer_name' => $s->customer->name ?? 'Pelanggan Umum',
                    'customer_phone' => $s->customer->phone ?? '-',
                    'sale_date' => $s->sale_date,
                    'days_outstanding' => $days,
                    'aging_group' => $agingGroup,
                    'total_amount' => $grandTotal,
                    'paid_amount' => $totalPaidForSale,
                    'outstanding_amount' => $outstanding,
                ]);
            }
        }

        $customer = $customerId ? Customer::find($customerId) : null;
        $meta = [
            'warehouse' => $customer ? 'Pelanggan: '.$customer->name : 'Semua Pelanggan',
        ];

        return $this->exportService->exportReceivables($receivablesData, $meta);
    }

    /**
     * Export Receivables to PDF
     */
    public function exportReceivablesPdf(Request $request)
    {
        $customerId = $request->get('customer_id');

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

        $receivablesData = collect();

        foreach ($sales as $s) {
            $grandTotal = (float) $s->grand_total;
            $directPaid = (float) ($s->paid_amount ?? 0);
            $extraPaid = (float) ($payments[$s->id] ?? 0);
            $totalPaidForSale = min($grandTotal, $directPaid + $extraPaid);
            $outstanding = max(0, $grandTotal - $totalPaidForSale);

            if ($outstanding > 0) {
                $days = Carbon::now()->diffInDays($s->sale_date ? Carbon::parse($s->sale_date) : Carbon::now());

                $receivablesData->push((object) [
                    'sale_id' => $s->id,
                    'invoice_number' => $s->invoice_number,
                    'customer_name' => $s->customer->name ?? 'Pelanggan Umum',
                    'customer_phone' => $s->customer->phone ?? '-',
                    'sale_date' => $s->sale_date,
                    'days_outstanding' => $days,
                    'total_amount' => $grandTotal,
                    'paid_amount' => $totalPaidForSale,
                    'outstanding_amount' => $outstanding,
                ]);
            }
        }

        $customer = $customerId ? Customer::find($customerId) : null;

        $pdf = Pdf::loadView('reports.receivables_pdf', compact(
            'receivablesData',
            'customer'
        ))->setPaper('a4', 'landscape');

        return $pdf->stream('Laporan_Piutang_Pelanggan_'.now()->format('Ymd').'.pdf');
    }

    /**
     * Export Cash Flows to Excel (.xlsx)
     */
    public function exportCashFlowsExcel(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $accountId = $request->get('account_id');
        $type = $request->get('type');

        $query = CashFlow::with(['account', 'creator'])
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate);

        if ($accountId) {
            $query->where('account_id', $accountId);
        }
        if ($type) {
            $query->where('type', $type);
        }

        $cashFlows = $query->latest('transaction_date')->latest('id')->get();
        $account = $accountId ? Account::find($accountId) : null;

        $meta = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'warehouse' => $account ? 'Rekening: '.$account->name : 'Semua Akun Kas/Bank',
        ];

        return $this->exportService->exportCashFlows($cashFlows, $meta);
    }

    /**
     * Export Cash Flows to PDF
     */
    public function exportCashFlowsPdf(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $accountId = $request->get('account_id');
        $type = $request->get('type');

        $query = CashFlow::with(['account', 'creator'])
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate);

        if ($accountId) {
            $query->where('account_id', $accountId);
        }
        if ($type) {
            $query->where('type', $type);
        }

        $cashFlows = $query->latest('transaction_date')->latest('id')->get();
        $account = $accountId ? Account::find($accountId) : null;

        $pdf = Pdf::loadView('reports.cash_flows_pdf', compact(
            'cashFlows',
            'startDate',
            'endDate',
            'account'
        ))->setPaper('a4', 'landscape');

        return $pdf->stream("Laporan_Mutasi_Arus_Kas_{$startDate}_sampai_{$endDate}.pdf");
    }

    /**
     * Export Profit and Loss to Excel (.xlsx)
     */
    public function exportProfitLossExcel(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $warehouseId = $request->get('warehouse_id');

        // 1. Sales
        $salesQuery = Sale::whereDate('sale_date', '>=', $startDate)
            ->whereDate('sale_date', '<=', $endDate)
            ->where('status', '!=', 'void');

        if ($warehouseId) {
            $salesQuery->where('warehouse_id', $warehouseId);
        }

        $grossSales = (clone $salesQuery)->sum('subtotal');
        $salesDiscounts = (clone $salesQuery)->sum('discount_amount');
        $netSales = (clone $salesQuery)->sum('grand_total');

        // 2. Total HPP
        $saleIds = (clone $salesQuery)->pluck('id');
        $totalHpp = SaleItem::whereIn('sale_id', $saleIds)
            ->select(DB::raw('SUM(quantity * unit_cost) as total_cogs'))
            ->value('total_cogs') ?? 0;

        $grossProfit = $netSales - $totalHpp;

        // 3. Biaya Operasional
        $expenseQuery = CashFlow::whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->where('type', 'out');

        $totalExpenses = (clone $expenseQuery)->sum('amount');
        $expensesByCategory = (clone $expenseQuery)
            ->select('category', DB::raw('SUM(amount) as total_expense'))
            ->groupBy('category')
            ->get();

        // 4. Laba Bersih
        $netProfit = $grossProfit - $totalExpenses;
        $netProfitMargin = $netSales > 0 ? (($netProfit / $netSales) * 100) : 0;

        $warehouse = $warehouseId ? Warehouse::find($warehouseId) : null;

        $data = [
            'grossSales' => $grossSales,
            'salesDiscounts' => $salesDiscounts,
            'netSales' => $netSales,
            'totalHpp' => $totalHpp,
            'grossProfit' => $grossProfit,
            'totalExpenses' => $totalExpenses,
            'expensesByCategory' => $expensesByCategory,
            'netProfit' => $netProfit,
            'netProfitMargin' => $netProfitMargin,
        ];

        $meta = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'warehouse' => $warehouse ? $warehouse->name : 'Semua Cabang / Gudang',
        ];

        return $this->exportService->exportProfitLoss($data, $meta);
    }

    /**
     * Export Profit and Loss to PDF
     */
    public function exportProfitLossPdf(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $warehouseId = $request->get('warehouse_id');

        $salesQuery = Sale::whereDate('sale_date', '>=', $startDate)
            ->whereDate('sale_date', '<=', $endDate)
            ->where('status', '!=', 'void');

        if ($warehouseId) {
            $salesQuery->where('warehouse_id', $warehouseId);
        }

        $grossSales = (clone $salesQuery)->sum('subtotal');
        $salesDiscounts = (clone $salesQuery)->sum('discount_amount');
        $netSales = (clone $salesQuery)->sum('grand_total');

        $saleIds = (clone $salesQuery)->pluck('id');
        $totalHpp = SaleItem::whereIn('sale_id', $saleIds)
            ->select(DB::raw('SUM(quantity * unit_cost) as total_cogs'))
            ->value('total_cogs') ?? 0;

        $grossProfit = $netSales - $totalHpp;

        $expenseQuery = CashFlow::whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->where('type', 'out');

        $totalExpenses = (clone $expenseQuery)->sum('amount');
        $expensesByCategory = (clone $expenseQuery)
            ->select('category', DB::raw('SUM(amount) as total_expense'))
            ->groupBy('category')
            ->get();

        $netProfit = $grossProfit - $totalExpenses;
        $netProfitMargin = $netSales > 0 ? (($netProfit / $netSales) * 100) : 0;

        $warehouse = $warehouseId ? Warehouse::find($warehouseId) : null;

        $pdf = Pdf::loadView('reports.profit_loss_pdf', compact(
            'grossSales',
            'salesDiscounts',
            'netSales',
            'totalHpp',
            'grossProfit',
            'totalExpenses',
            'expensesByCategory',
            'netProfit',
            'netProfitMargin',
            'startDate',
            'endDate',
            'warehouse'
        ))->setPaper('a4', 'landscape');

        return $pdf->stream("Laporan_Laba_Rugi_{$startDate}_sampai_{$endDate}.pdf");
    }

    /**
     * Export Cashier Shifts to Excel (.xlsx)
     */
    public function exportCashierShiftsExcel(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $userId = $request->get('user_id');
        $warehouseId = $request->get('warehouse_id');

        $query = CashierShift::with(['user', 'warehouse', 'expenses.user'])
            ->whereDate('opened_at', '>=', $startDate)
            ->whereDate('opened_at', '<=', $endDate);

        if ($userId) {
            $query->where('user_id', $userId);
        }
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        $shifts = $query->latest('opened_at')->get();
        $cashier = $userId ? User::find($userId) : null;
        $warehouse = $warehouseId ? Warehouse::find($warehouseId) : null;

        $meta = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'warehouse' => $warehouse ? $warehouse->name : ($cashier ? 'Kasir: '.$cashier->name : 'Semua Cabang'),
        ];

        return $this->exportService->exportCashierShifts($shifts, $meta);
    }

    /**
     * Export Cashier Shifts to PDF
     */
    public function exportCashierShiftsPdf(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $userId = $request->get('user_id');
        $warehouseId = $request->get('warehouse_id');

        $query = CashierShift::with(['user', 'warehouse', 'expenses.user'])
            ->whereDate('opened_at', '>=', $startDate)
            ->whereDate('opened_at', '<=', $endDate);

        if ($userId) {
            $query->where('user_id', $userId);
        }
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        $shifts = $query->latest('opened_at')->get();
        $cashier = $userId ? User::find($userId) : null;
        $warehouse = $warehouseId ? Warehouse::find($warehouseId) : null;

        $pdf = Pdf::loadView('reports.cashier_shifts_pdf', compact(
            'shifts',
            'startDate',
            'endDate',
            'cashier',
            'warehouse'
        ))->setPaper('a4', 'landscape');

        return $pdf->stream("Laporan_Rekap_Shift_Kasir_{$startDate}_sampai_{$endDate}.pdf");
    }
}
