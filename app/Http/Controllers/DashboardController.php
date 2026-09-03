<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\StockBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Low stock count
        $lowStockCount = Product::where('is_active', true)
            ->where('min_stock', '>', 0)
            ->whereHas('stocks')
            ->addSelect([
                'current_stock' => DB::table('product_stocks')
                    ->selectRaw('COALESCE(SUM(quantity), 0)')
                    ->whereColumn('product_stocks.product_id', 'products.id')
            ])
            ->havingRaw('current_stock <= products.min_stock')
            ->count();

        // 2. Expiring batch count (<= 30 days)
        $expiringCount = StockBatch::where('qty_remaining', '>', 0)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays(30)->toDateString())
            ->count();

        // 3. Sales Timeframe Metrics
        $todaySales = Sale::whereDate('sale_date', today())->where('status', 'completed')->sum('grand_total');
        $todayTransactions = Sale::whereDate('sale_date', today())->where('status', 'completed')->count();
        
        $thisWeekSales = Sale::whereBetween('sale_date', [now()->startOfWeek(), now()->endOfWeek()])
            ->where('status', 'completed')->sum('grand_total');
            
        $thisMonthSales = Sale::whereBetween('sale_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->where('status', 'completed')->sum('grand_total');

        // 4. Overdue Receivables & Payables
        $overdueReceivables = Sale::where('payment_status', '!=', 'paid')
            ->where('status', '!=', 'void')
            ->whereDate('sale_date', '<=', now()->subDays(30))
            ->sum(DB::raw('grand_total - paid_amount'));

        $overduePayables = \App\Models\PurchaseReceipt::join('purchase_orders', 'purchase_receipts.purchase_order_id', '=', 'purchase_orders.id')
            ->where('purchase_receipts.status', 'received')
            ->whereDate('purchase_receipts.receipt_date', '<=', now()->subDays(30))
            ->sum('purchase_orders.grand_total');

        // 5. Trend Penjualan 30 Hari Terakhir
        $thirtyDaysAgo = now()->subDays(29)->startOfDay();
        $rawTrend = Sale::where('sale_date', '>=', $thirtyDaysAgo)
            ->where('status', 'completed')
            ->select(
                DB::raw('DATE(sale_date) as date'),
                DB::raw('SUM(grand_total) as total_sales'),
                DB::raw('COUNT(id) as total_count')
            )
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get()
            ->keyBy('date');

        $trendDates = [];
        $trendSales = [];
        $trendCounts = [];

        for ($i = 29; $i >= 0; $i--) {
            $d = now()->subDays($i)->format('Y-m-d');
            $trendDates[] = now()->subDays($i)->format('d M');
            $trendSales[] = (float) ($rawTrend[$d]->total_sales ?? 0);
            $trendCounts[] = (int) ($rawTrend[$d]->total_count ?? 0);
        }

        // 6. Top 10 Produk Terlaris (30 Hari Terakhir / All Time)
        $topProducts = \App\Models\SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.status', 'completed')
            ->select(
                'products.name as product_name',
                'products.code as product_code',
                DB::raw('SUM(sale_items.quantity) as total_qty'),
                DB::raw('SUM(sale_items.subtotal) as total_revenue')
            )
            ->groupBy('products.name', 'products.code')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        // 7. Penjualan per Kategori (Pie/Donut Chart Data)
        $categorySales = \App\Models\SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->where('sales.status', 'completed')
            ->select(
                DB::raw('COALESCE(categories.name, "Tanpa Kategori") as category_name'),
                DB::raw('SUM(sale_items.subtotal) as total_revenue')
            )
            ->groupBy('categories.name')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();

        // 8. Metode Pembayaran (Cash vs QRIS vs Transfer vs Tempo)
        $paymentDistribution = Sale::where('status', 'completed')
            ->select(
                'payment_method',
                DB::raw('COUNT(id) as count'),
                DB::raw('SUM(grand_total) as total')
            )
            ->groupBy('payment_method')
            ->get();

        // 9. Jam Ramai Penjualan (Hourly Rush Traffic POS)
        $hourlySales = Sale::where('status', 'completed')
            ->whereDate('sale_date', '>=', now()->subDays(7))
            ->select(
                DB::raw('HOUR(sale_date) as hour'),
                DB::raw('COUNT(id) as total_tx')
            )
            ->groupBy('hour')
            ->orderBy('hour', 'ASC')
            ->pluck('total_tx', 'hour');

        $hourlyLabels = [];
        $hourlyData = [];
        for ($h = 8; $h <= 22; $h++) {
            $hourlyLabels[] = sprintf('%02d:00', $h);
            $hourlyData[] = (int) ($hourlySales[$h] ?? 0);
        }

        // 10. Perbandingan Penjualan per Outlet / Cabang (Multi-Cabang)
        $outletSales = Sale::join('warehouses', 'sales.warehouse_id', '=', 'warehouses.id')
            ->where('sales.status', 'completed')
            ->select(
                'warehouses.name as outlet_name',
                DB::raw('COUNT(sales.id) as total_tx'),
                DB::raw('SUM(sales.grand_total) as total_sales')
            )
            ->groupBy('warehouses.name')
            ->orderByDesc('total_sales')
            ->get();

        // 11. Transaksi Kasir Terbaru (Realtime Recent Activity)
        $recentSales = Sale::with(['user', 'customer', 'warehouse'])
            ->latest('sale_date')
            ->limit(6)
            ->get();

        $totalCustomers = \App\Models\Customer::where('is_active', true)->count();
        $totalProducts = Product::where('is_active', true)->count();

        return view('dashboard.index', [
            'headerTitle' => 'Dashboard Utama & Pusat Analitik',
            'headerDescription' => 'Ringkasan operasional bisnis, pemantauan inventaris real-time, dan tren analitik penjualan terpadu.',
            'lowStockCount' => $lowStockCount,
            'expiringCount' => $expiringCount,
            'todaySales' => $todaySales,
            'todayTransactions' => $todayTransactions,
            'thisWeekSales' => $thisWeekSales,
            'thisMonthSales' => $thisMonthSales,
            'overdueReceivables' => $overdueReceivables,
            'overduePayables' => $overduePayables,
            'trendDates' => $trendDates,
            'trendSales' => $trendSales,
            'trendCounts' => $trendCounts,
            'topProducts' => $topProducts,
            'categorySales' => $categorySales,
            'paymentDistribution' => $paymentDistribution,
            'hourlyLabels' => $hourlyLabels,
            'hourlyData' => $hourlyData,
            'outletSales' => $outletSales,
            'recentSales' => $recentSales,
            'totalCustomers' => $totalCustomers,
            'totalProducts' => $totalProducts,
        ]);
    }
}
