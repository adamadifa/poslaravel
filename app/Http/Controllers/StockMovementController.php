<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockBatch;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    /**
     * Display Kartu Stok (Stock Card) and FIFO Batches
     */
    public function index(Request $request)
    {
        $productId = $request->query('product_id');
        $warehouseId = $request->query('warehouse_id');
        $type = $request->query('type'); // in / out
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $search = $request->query('search');

        // Main Query for Stock Movements
        $query = StockMovement::with(['product.baseUnit', 'warehouse', 'creator'])
            ->when($productId, fn($q) => $q->where('product_id', $productId))
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->when($type, fn($q) => $q->where('type', $type))
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->when($search, function ($q, $search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('description', 'like', "%{$search}%")
                      ->orWhere('reference_type', 'like', "%{$search}%")
                      ->orWhereHas('product', fn($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
                });
            });

        $movements = $query->latest('id')->paginate(20)->withQueryString();

        // Warehouse and Product lists for filtering
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::with('baseUnit')->where('is_active', true)->orderBy('name')->get();

        // Active Stock Batches (FIFO overview)
        $batches = StockBatch::with(['product.baseUnit', 'warehouse'])
            ->when($productId, fn($q) => $q->where('product_id', $productId))
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->where('qty_remaining', '>', 0)
            ->orderBy('entry_date', 'asc')
            ->limit(30)
            ->get();

        // Current Physical Stock Summary if Product & Warehouse selected
        $currentStock = null;
        if ($productId) {
            $stockQuery = ProductStock::where('product_id', $productId);
            if ($warehouseId) {
                $stockQuery->where('warehouse_id', $warehouseId);
            }
            $currentStock = $stockQuery->sum('quantity');
        }

        return view('stocks.index', [
            'title' => 'Kartu Stok (Stock Card)',
            'headerTitle' => 'Kartu Stok & Alokasi FIFO',
            'headerDescription' => 'Lacak riwayat mutasi masuk/keluar barang (Stock Card), cek batch aktif FIFO, dan audit mutasi persediaan.',
            'breadcrumbParent' => 'Inventaris & Stok',
            'breadcrumbCurrent' => 'Kartu Stok',
            'movements' => $movements,
            'batches' => $batches,
            'warehouses' => $warehouses,
            'products' => $products,
            'productId' => $productId,
            'warehouseId' => $warehouseId,
            'type' => $type,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'search' => $search,
            'currentStock' => $currentStock,
        ]);
    }
}
