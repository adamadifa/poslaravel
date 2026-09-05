<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockBatch;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockAlertController extends Controller
{
    /**
     * Display low stock alerts and expiring batch monitoring
     */
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'low_stock'); // low_stock or expiring
        $warehouseId = $request->query('warehouse_id');
        $categoryId = $request->query('category_id');
        $search = $request->query('search');
        $daysThreshold = (int) $request->query('days', 30); // 30, 60, 90 days for expiry

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        // 1. Low Stock Query (Stok total <= min_stock)
        $lowStockQuery = Product::with(['category', 'baseUnit', 'stocks.warehouse'])
            ->where('is_active', true)
            ->where('min_stock', '>', 0)
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->when($search, function ($q, $search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->whereHas('stocks', function ($q) use ($warehouseId) {
                if ($warehouseId) {
                    $q->where('warehouse_id', $warehouseId);
                }
            })
            ->whereRaw('(' . ($warehouseId 
                ? 'SELECT COALESCE(SUM(quantity), 0) FROM product_stocks WHERE product_stocks.product_id = products.id AND product_stocks.warehouse_id = ' . intval($warehouseId)
                : 'SELECT COALESCE(SUM(quantity), 0) FROM product_stocks WHERE product_stocks.product_id = products.id'
            ) . ') <= products.min_stock')
            ->addSelect([
                'current_stock' => DB::table('product_stocks')
                    ->selectRaw('COALESCE(SUM(quantity), 0)')
                    ->whereColumn('product_stocks.product_id', 'products.id')
                    ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ])
            ->orderBy('current_stock', 'asc');

        $lowStockProducts = $lowStockQuery->paginate(15, ['*'], 'low_page')->withQueryString();

        // 2. Expiring / Expired Batches Query
        $targetDate = now()->addDays($daysThreshold)->toDateString();
        $expiringBatches = StockBatch::with(['product.category', 'product.baseUnit', 'warehouse'])
            ->where('qty_remaining', '>', 0)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', $targetDate)
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->when($search, function ($q, $search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('batch_number', 'like', "%{$search}%")
                      ->orWhereHas('product', fn($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
                });
            })
            ->orderBy('expiry_date', 'asc')
            ->paginate(15, ['*'], 'exp_page')
            ->withQueryString();

        // Stats summary counts
        $totalLowStockCount = Product::where('is_active', true)
            ->where('min_stock', '>', 0)
            ->whereHas('stocks')
            ->whereRaw('(SELECT COALESCE(SUM(quantity), 0) FROM product_stocks WHERE product_stocks.product_id = products.id) <= products.min_stock')
            ->count();

        $totalExpiringCount = StockBatch::where('qty_remaining', '>', 0)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', $targetDate)
            ->count();

        return view('stocks.alerts.index', [
            'title' => 'Peringatan & Monitoring Stok',
            'headerTitle' => 'Peringatan Stok Minimum & Kedaluwarsa',
            'headerDescription' => 'Pantau persediaan produk yang menipis di bawah batas minimum dan deteksi dini barang yang mendekati expired date.',
            'breadcrumbParent' => 'Inventaris & Stok',
            'breadcrumbCurrent' => 'Peringatan Stok',
            'tab' => $tab,
            'warehouses' => $warehouses,
            'categories' => $categories,
            'lowStockProducts' => $lowStockProducts,
            'expiringBatches' => $expiringBatches,
            'totalLowStockCount' => $totalLowStockCount,
            'totalExpiringCount' => $totalExpiringCount,
            'warehouseId' => $warehouseId,
            'categoryId' => $categoryId,
            'search' => $search,
            'daysThreshold' => $daysThreshold,
        ]);
    }
}
