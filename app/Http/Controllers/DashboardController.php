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

        // 3. Real POS Sales Metrics
        $todaySales = Sale::whereDate('sale_date', today())->where('status', 'completed')->sum('grand_total');
        $todayTransactions = Sale::whereDate('sale_date', today())->where('status', 'completed')->count();
        $totalCustomers = \App\Models\Customer::where('is_active', true)->count();
        $totalProducts = Product::where('is_active', true)->count();

        return view('dashboard.index', [
            'headerTitle' => 'Dashboard',
            'headerDescription' => 'Ringkasan operasional bisnis, pemantauan inventaris, dan performa penjualan.',
            'lowStockCount' => $lowStockCount,
            'expiringCount' => $expiringCount,
            'todaySales' => $todaySales,
            'todayTransactions' => $todayTransactions,
            'totalCustomers' => $totalCustomers,
            'totalProducts' => $totalProducts,
        ]);
    }
}
