<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockAdjustment;
use App\Models\StockBatch;
use App\Models\StockMovement;
use App\Models\StockOpname;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use App\Services\StockAdjustmentService;
use App\Services\StockOpnameService;
use App\Services\StockTransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StockApiController extends BaseApiController
{
    protected StockOpnameService $opnameService;

    protected StockTransferService $transferService;

    protected StockAdjustmentService $adjustmentService;

    public function __construct(
        StockOpnameService $opnameService,
        StockTransferService $transferService,
        StockAdjustmentService $adjustmentService
    ) {
        $this->opnameService = $opnameService;
        $this->transferService = $transferService;
        $this->adjustmentService = $adjustmentService;
    }

    /**
     * Get stock card movements list with filters and summary.
     */
    public function getMovements(Request $request): JsonResponse
    {
        $productId = $request->query('product_id');
        $warehouseId = $request->query('warehouse_id');
        $type = $request->query('type'); // in, out, all
        $tab = $request->query('tab') ?? $request->query('product_type'); // 'products', 'raw_materials', or 'all'
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $search = $request->query('q') ?? $request->query('search');
        $limit = (int) ($request->query('limit', 50));

        $query = StockMovement::with(['product.baseUnit', 'warehouse', 'creator'])
            ->when($tab && $tab !== 'all', function ($q) use ($tab) {
                if ($tab === 'raw_materials' || $tab === 'raw_material') {
                    $q->whereHas('product', fn ($pq) => $pq->where('product_type', 'raw_material'));
                } else {
                    $q->whereHas('product', fn ($pq) => $pq->where('product_type', '!=', 'raw_material'));
                }
            })
            ->when($productId, fn ($q) => $q->where('product_id', $productId))
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->when($type && $type !== 'all', fn ($q) => $q->where('type', $type))
            ->when($startDate, fn ($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('created_at', '<=', $endDate))
            ->when($search, function ($q, $search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('description', 'like', "%{$search}%")
                        ->orWhere('reference_type', 'like', "%{$search}%")
                        ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
                });
            });

        // Calculate summary for the filtered scope
        $movements = (clone $query)->latest('id')->limit($limit)->get();

        $totalIn = (clone $query)->where('type', 'in')->sum('quantity');
        $totalOut = (clone $query)->where('type', 'out')->sum('quantity');

        $currentStock = null;
        if ($productId) {
            $stockQ = ProductStock::where('product_id', $productId);
            if ($warehouseId) {
                $stockQ->where('warehouse_id', $warehouseId);
            }
            $currentStock = (float) $stockQ->sum('quantity');
        }

        return $this->sendResponse([
            'movements' => $movements,
            'summary' => [
                'total_in' => (float) $totalIn,
                'total_out' => (float) $totalOut,
                'current_stock' => $currentStock,
                'movements_count' => $movements->count(),
            ],
        ], 'Data riwayat mutasi stok berhasil dimuat.');
    }

    /**
     * Get active FIFO stock batches.
     */
    public function getBatches(Request $request): JsonResponse
    {
        $productId = $request->query('product_id');
        $warehouseId = $request->query('warehouse_id');
        $tab = $request->query('tab') ?? $request->query('product_type');
        $search = $request->query('q') ?? $request->query('search');

        $batches = StockBatch::with(['product.baseUnit', 'warehouse'])
            ->where('qty_remaining', '>', 0)
            ->when($tab && $tab !== 'all', function ($q) use ($tab) {
                if ($tab === 'raw_materials' || $tab === 'raw_material') {
                    $q->whereHas('product', fn ($pq) => $pq->where('product_type', 'raw_material'));
                } else {
                    $q->whereHas('product', fn ($pq) => $pq->where('product_type', '!=', 'raw_material'));
                }
            })
            ->when($productId, fn ($q) => $q->where('product_id', $productId))
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->when($search, function ($q, $search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('batch_number', 'like', "%{$search}%")
                        ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
                });
            })
            ->orderBy('entry_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $totalBatchStock = $batches->sum('qty_remaining');
        $totalBatchValue = $batches->sum(fn ($b) => $b->qty_remaining * $b->unit_cost);

        return $this->sendResponse([
            'batches' => $batches,
            'total_stock' => (float) $totalBatchStock,
            'total_valuation' => (float) $totalBatchValue,
            'total_batches_count' => $batches->count(),
        ], 'Data Batch FIFO berhasil dimuat.');
    }

    /**
     * Get product specific stock card overview (Stock by warehouse, FIFO batches, and latest movements).
     */
    public function getProductStockCard(Request $request, Product $product): JsonResponse
    {
        $warehouseId = $request->query('warehouse_id');

        $product->load(['baseUnit', 'category']);

        $stocks = ProductStock::with('warehouse')
            ->where('product_id', $product->id)
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->get();

        $batches = StockBatch::with('warehouse')
            ->where('product_id', $product->id)
            ->where('qty_remaining', '>', 0)
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->orderBy('entry_date', 'asc')
            ->get();

        $recentMovements = StockMovement::with(['warehouse', 'creator'])
            ->where('product_id', $product->id)
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->latest('id')
            ->limit(30)
            ->get();

        $totalStock = $stocks->sum('quantity');

        return $this->sendResponse([
            'product' => $product,
            'total_stock' => (float) $totalStock,
            'stocks_by_warehouse' => $stocks,
            'fifo_batches' => $batches,
            'recent_movements' => $recentMovements,
        ], "Kartu Stok produk {$product->name} berhasil dimuat.");
    }

    /**
     * Get stock alerts (Low stock products and expiring/expired FIFO batches)
     */
    public function getAlerts(Request $request): JsonResponse
    {
        $warehouseId = $request->query('warehouse_id');
        $categoryId = $request->query('category_id');
        $tab = $request->query('tab') ?? $request->query('product_type'); // 'all', 'products', 'raw_materials'
        $search = $request->query('q') ?? $request->query('search');
        $daysThreshold = (int) ($request->query('days', 30));

        // 1. Low Stock Query (current_stock <= min_stock)
        $lowStockQuery = Product::with(['category', 'baseUnit', 'stocks' => function ($q) use ($warehouseId) {
            if ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            }
        }])
            ->where('is_active', true)
            ->where('min_stock', '>', 0)
            ->when($tab && $tab !== 'all', function ($q) use ($tab) {
                if ($tab === 'raw_materials' || $tab === 'raw_material') {
                    $q->where('product_type', 'raw_material');
                } else {
                    $q->where('product_type', '!=', 'raw_material');
                }
            })
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->when($search, function ($q, $search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->whereRaw('('.($warehouseId
                ? 'SELECT COALESCE(SUM(quantity), 0) FROM product_stocks WHERE product_stocks.product_id = products.id AND product_stocks.warehouse_id = '.(int) $warehouseId
                : 'SELECT COALESCE(SUM(quantity), 0) FROM product_stocks WHERE product_stocks.product_id = products.id'
            ).') <= products.min_stock')
            ->select('products.*')
            ->addSelect([
                'current_stock' => DB::table('product_stocks')
                    ->selectRaw('COALESCE(SUM(quantity), 0)')
                    ->whereColumn('product_stocks.product_id', 'products.id')
                    ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId)),
            ])
            ->orderBy('current_stock', 'asc')
            ->orderBy('min_stock', 'desc');

        $lowStockProducts = $lowStockQuery->get()->map(function ($product) {
            $cur = (float) $product->current_stock;
            $min = (float) $product->min_stock;
            $product->stock = $cur;
            $product->deficit = max(0, $min - $cur);
            $product->stock_percentage = $min > 0 ? round(($cur / $min) * 100, 1) : 0;
            $product->is_out_of_stock = $cur <= 0;

            return $product;
        });

        // 2. Expiring / Expired Batches Query
        $targetDate = now()->addDays($daysThreshold)->toDateString();
        $today = now()->toDateString();

        $expiringBatches = StockBatch::with(['product.category', 'product.baseUnit', 'warehouse'])
            ->where('qty_remaining', '>', 0)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', $targetDate)
            ->when($tab && $tab !== 'all', function ($q) use ($tab) {
                if ($tab === 'raw_materials' || $tab === 'raw_material') {
                    $q->whereHas('product', fn ($pq) => $pq->where('product_type', 'raw_material'));
                } else {
                    $q->whereHas('product', fn ($pq) => $pq->where('product_type', '!=', 'raw_material'));
                }
            })
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->when($search, function ($q, $search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('batch_number', 'like', "%{$search}%")
                        ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
                });
            })
            ->orderBy('expiry_date', 'asc')
            ->get();

        // Stats summary
        $outOfStockCount = $lowStockProducts->where('is_out_of_stock', true)->count();
        $expiredCount = $expiringBatches->where('expiry_date', '<', $today)->count();
        $expiringSoonCount = $expiringBatches->where('expiry_date', '>=', $today)->count();
        $expiringValuation = $expiringBatches->sum(fn ($b) => $b->qty_remaining * $b->unit_cost);

        return $this->sendResponse([
            'low_stock_products' => $lowStockProducts,
            'expiring_batches' => $expiringBatches,
            'summary' => [
                'total_low_stock' => $lowStockProducts->count(),
                'out_of_stock_count' => $outOfStockCount,
                'total_expiring' => $expiringBatches->count(),
                'expired_count' => $expiredCount,
                'expiring_soon_count' => $expiringSoonCount,
                'expiring_valuation' => (float) $expiringValuation,
            ],
        ], 'Data monitoring peringatan stok berhasil dimuat.');
    }

    /**
     * Get list of Stock Opnames.
     */
    public function getOpnames(Request $request): JsonResponse
    {
        $warehouseId = $request->query('warehouse_id');
        $status = $request->query('status');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $search = $request->query('q') ?? $request->query('search');

        $opnames = StockOpname::with(['warehouse', 'conductor', 'approver', 'items.product.baseUnit'])
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->when($status && $status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($startDate, fn ($q) => $q->whereDate('opname_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('opname_date', '<=', $endDate))
            ->when($search, function ($q, $search) {
                $q->where('opname_number', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            })
            ->latest('opname_date')
            ->latest('id')
            ->get();

        return $this->sendResponse($opnames, 'Daftar dokumen Stok Opname berhasil dimuat.');
    }

    /**
     * Get single Stock Opname detail.
     */
    public function getOpnameDetail(StockOpname $stockOpname): JsonResponse
    {
        $stockOpname->load(['warehouse', 'conductor', 'approver', 'items.product.baseUnit']);

        return $this->sendResponse($stockOpname, 'Detail Stok Opname berhasil dimuat.');
    }

    /**
     * Store new Stock Opname.
     */
    public function storeOpname(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'warehouse_id' => 'required|exists:warehouses,id',
            'opname_date' => 'required|date',
            'status' => 'required|in:draft,in_progress',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.physical_qty' => 'required|numeric|min:0',
            'items.*.reason' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi gagal', $validator->errors()->toArray(), 422);
        }

        try {
            $opname = $this->opnameService->createStockOpname($validator->validated());
            $opname->load(['warehouse', 'conductor', 'items.product.baseUnit']);

            return $this->sendResponse($opname, "Dokumen Stok Opname {$opname->opname_number} berhasil dibuat.", 201);
        } catch (\Exception $e) {
            return $this->sendError('Gagal membuat stok opname: '.$e->getMessage(), [], 500);
        }
    }

    /**
     * Update existing Stock Opname.
     */
    public function updateOpname(Request $request, StockOpname $stockOpname): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'opname_date' => 'required|date',
            'status' => 'required|in:draft,in_progress',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.physical_qty' => 'required|numeric|min:0',
            'items.*.reason' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi gagal', $validator->errors()->toArray(), 422);
        }

        try {
            $opname = $this->opnameService->updateStockOpname($stockOpname, $validator->validated());
            $opname->load(['warehouse', 'conductor', 'items.product.baseUnit']);

            return $this->sendResponse($opname, "Stok Opname {$opname->opname_number} berhasil diperbarui.");
        } catch (\Exception $e) {
            return $this->sendError('Gagal memperbarui stok opname: '.$e->getMessage(), [], 500);
        }
    }

    /**
     * Approve and Execute Stock Opname (Auto-Adjust Stock & Record Movements).
     */
    public function approveOpname(StockOpname $stockOpname): JsonResponse
    {
        try {
            $this->opnameService->approveStockOpname($stockOpname);
            $stockOpname->load(['warehouse', 'conductor', 'approver', 'items.product.baseUnit']);

            return $this->sendResponse($stockOpname, "Stok Opname {$stockOpname->opname_number} telah disetujui. Mutasi stok dan batch persediaan telah disinkronkan otomatis.");
        } catch (\Exception $e) {
            return $this->sendError('Gagal menyetujui stok opname: '.$e->getMessage(), [], 422);
        }
    }

    /**
     * Delete or Cancel Stock Opname.
     */
    public function destroyOpname(StockOpname $stockOpname): JsonResponse
    {
        try {
            if ($stockOpname->status === 'draft') {
                $stockOpname->delete();

                return $this->sendResponse(null, "Draft Stok Opname {$stockOpname->opname_number} berhasil dihapus.");
            }

            $this->opnameService->cancelStockOpname($stockOpname);

            return $this->sendResponse(null, "Stok Opname {$stockOpname->opname_number} berhasil dibatalkan.");
        } catch (\Exception $e) {
            return $this->sendError('Gagal memproses pembatalan: '.$e->getMessage(), [], 422);
        }
    }

    // =========================================================================
    // TRANSFER ANTAR GUDANG
    // =========================================================================

    /**
     * Get list of Stock Transfers.
     */
    public function getTransfers(Request $request): JsonResponse
    {
        $fromWarehouseId = $request->query('from_warehouse_id');
        $toWarehouseId = $request->query('to_warehouse_id');
        $status = $request->query('status');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $search = $request->query('q') ?? $request->query('search');

        $transfers = StockTransfer::with(['fromWarehouse', 'toWarehouse', 'sender', 'receiver', 'items.product.baseUnit', 'items.unit'])
            ->when($fromWarehouseId, fn ($q) => $q->where('from_warehouse_id', $fromWarehouseId))
            ->when($toWarehouseId, fn ($q) => $q->where('to_warehouse_id', $toWarehouseId))
            ->when($status && $status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($startDate, fn ($q) => $q->whereDate('transfer_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('transfer_date', '<=', $endDate))
            ->when($search, function ($q, $search) {
                $q->where('transfer_number', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            })
            ->latest('transfer_date')
            ->latest('id')
            ->get();

        return $this->sendResponse($transfers, 'Daftar dokumen Transfer Antar Gudang berhasil dimuat.');
    }

    /**
     * Get single Stock Transfer detail.
     */
    public function getTransferDetail(StockTransfer $stockTransfer): JsonResponse
    {
        $stockTransfer->load(['fromWarehouse', 'toWarehouse', 'sender', 'receiver', 'items.product.baseUnit', 'items.unit']);

        return $this->sendResponse($stockTransfer, 'Detail Transfer Stok berhasil dimuat.');
    }

    /**
     * Store new Stock Transfer.
     */
    public function storeTransfer(Request $request): JsonResponse
    {
        // Normalise payload items (accept quantity or quantity_sent)
        $input = $request->all();
        if (isset($input['items']) && is_array($input['items'])) {
            foreach ($input['items'] as $k => $item) {
                if (! isset($item['quantity_sent']) && isset($item['quantity'])) {
                    $input['items'][$k]['quantity_sent'] = $item['quantity'];
                }
            }
        }
        if (! isset($input['action']) && isset($input['status'])) {
            $input['action'] = $input['status'] === 'in_transit' ? 'dispatch' : 'draft';
        }

        $validator = Validator::make($input, [
            'from_warehouse_id' => 'required|exists:warehouses,id|different:to_warehouse_id',
            'to_warehouse_id' => 'required|exists:warehouses,id',
            'transfer_date' => 'required|date',
            'notes' => 'nullable|string',
            'action' => 'nullable|in:draft,dispatch',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_id' => 'nullable|exists:units,id',
            'items.*.quantity_sent' => 'required|numeric|min:0.0001',
            'items.*.batch_number' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi gagal', $validator->errors()->toArray(), 422);
        }

        try {
            $transfer = $this->transferService->createStockTransfer($validator->validated());
            $transfer->load(['fromWarehouse', 'toWarehouse', 'sender', 'receiver', 'items.product.baseUnit', 'items.unit']);

            $msg = $transfer->status === 'in_transit'
                ? "Transfer {$transfer->transfer_number} berhasil dibuat & langsung dikirim (In Transit)."
                : "Draft Transfer {$transfer->transfer_number} berhasil disimpan.";

            return $this->sendResponse($transfer, $msg, 201);
        } catch (\Exception $e) {
            return $this->sendError('Gagal membuat transfer stok: '.$e->getMessage(), [], 500);
        }
    }

    /**
     * Dispatch draft transfer (send goods from origin warehouse).
     */
    public function dispatchTransfer(StockTransfer $stockTransfer): JsonResponse
    {
        try {
            $this->transferService->dispatchTransfer($stockTransfer);
            $stockTransfer->load(['fromWarehouse', 'toWarehouse', 'sender', 'receiver', 'items.product.baseUnit', 'items.unit']);

            return $this->sendResponse($stockTransfer, "Transfer {$stockTransfer->transfer_number} telah dikirim (In Transit). Stok gudang asal telah dikurangi.");
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengirim transfer: '.$e->getMessage(), [], 422);
        }
    }

    /**
     * Confirm Receive Transfer (receive goods in destination warehouse).
     */
    public function receiveTransfer(Request $request, StockTransfer $stockTransfer): JsonResponse
    {
        $input = $request->all();
        $formattedItems = [];

        // Support array of items [{id: 1, quantity_received: 5}] as well as key-value map
        if (isset($input['items']) && is_array($input['items'])) {
            foreach ($input['items'] as $key => $item) {
                if (is_array($item) && isset($item['id'])) {
                    $formattedItems[$item['id']] = [
                        'quantity_received' => $item['quantity_received'] ?? null,
                    ];
                } elseif (is_array($item)) {
                    $formattedItems[$key] = $item;
                }
            }
            $input['items'] = $formattedItems;
        }

        $validator = Validator::make($input, [
            'items' => 'nullable|array',
            'items.*.quantity_received' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi gagal', $validator->errors()->toArray(), 422);
        }

        try {
            $this->transferService->receiveTransfer($stockTransfer, $validator->validated());
            $stockTransfer->load(['fromWarehouse', 'toWarehouse', 'sender', 'receiver', 'items.product.baseUnit', 'items.unit']);

            return $this->sendResponse($stockTransfer, "Transfer {$stockTransfer->transfer_number} telah berhasil diterima. Stok gudang tujuan telah ditambahkan.");
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengonfirmasi penerimaan: '.$e->getMessage(), [], 422);
        }
    }

    /**
     * Cancel or Delete Stock Transfer.
     */
    public function destroyTransfer(StockTransfer $stockTransfer): JsonResponse
    {
        try {
            if ($stockTransfer->status === 'draft') {
                $stockTransfer->delete();

                return $this->sendResponse(null, "Draft Transfer {$stockTransfer->transfer_number} berhasil dihapus.");
            }

            $this->transferService->cancelTransfer($stockTransfer);

            return $this->sendResponse(null, "Transfer {$stockTransfer->transfer_number} berhasil dibatalkan dan stok dikembalikan.");
        } catch (\Exception $e) {
            return $this->sendError('Gagal memproses pembatalan: '.$e->getMessage(), [], 422);
        }
    }

    // =========================================================================
    // PENYESUAIAN STOK (STOCK ADJUSTMENTS)
    // =========================================================================

    /**
     * Get list of Stock Adjustments.
     */
    public function getAdjustments(Request $request): JsonResponse
    {
        $warehouseId = $request->query('warehouse_id');
        $status = $request->query('status');
        $type = $request->query('type');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $search = $request->query('q') ?? $request->query('search');

        $adjustments = StockAdjustment::with(['warehouse', 'creator', 'approver', 'items.product.baseUnit', 'items.unit'])
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->when($status && $status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($type && $type !== 'all', fn ($q) => $q->where('type', $type))
            ->when($startDate, fn ($q) => $q->whereDate('adjustment_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('adjustment_date', '<=', $endDate))
            ->when($search, function ($q, $search) {
                $q->where('adjustment_number', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            })
            ->latest('adjustment_date')
            ->latest('id')
            ->get();

        return $this->sendResponse($adjustments, 'Daftar dokumen Penyesuaian Stok berhasil dimuat.');
    }

    /**
     * Get single Stock Adjustment detail.
     */
    public function getAdjustmentDetail(StockAdjustment $stockAdjustment): JsonResponse
    {
        $stockAdjustment->load(['warehouse', 'creator', 'approver', 'items.product.baseUnit', 'items.unit']);

        return $this->sendResponse($stockAdjustment, 'Detail Penyesuaian Stok berhasil dimuat.');
    }

    /**
     * Store new Stock Adjustment.
     */
    public function storeAdjustment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'warehouse_id' => 'required|exists:warehouses,id',
            'adjustment_date' => 'required|date',
            'type' => 'required|in:addition,reduction',
            'reason' => 'required|string|max:500',
            'notes' => 'nullable|string',
            'action' => 'nullable|in:draft,approve',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_id' => 'nullable|exists:units,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi gagal', $validator->errors()->toArray(), 422);
        }

        try {
            $adjustment = $this->adjustmentService->createStockAdjustment($validator->validated());
            $adjustment->load(['warehouse', 'creator', 'approver', 'items.product.baseUnit', 'items.unit']);

            $msg = $adjustment->status === 'approved'
                ? "Penyesuaian Stok {$adjustment->adjustment_number} berhasil dibuat & langsung disetujui."
                : "Draft Penyesuaian Stok {$adjustment->adjustment_number} berhasil disimpan.";

            return $this->sendResponse($adjustment, $msg, 201);
        } catch (\Exception $e) {
            return $this->sendError('Gagal membuat penyesuaian stok: '.$e->getMessage(), [], 500);
        }
    }

    /**
     * Update existing Stock Adjustment (draft only).
     */
    public function updateAdjustment(Request $request, StockAdjustment $stockAdjustment): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'warehouse_id' => 'sometimes|exists:warehouses,id',
            'adjustment_date' => 'sometimes|date',
            'type' => 'sometimes|in:addition,reduction',
            'reason' => 'sometimes|string|max:500',
            'notes' => 'nullable|string',
            'action' => 'nullable|in:draft,approve',
            'items' => 'sometimes|array|min:1',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.unit_id' => 'nullable|exists:units,id',
            'items.*.quantity' => 'required_with:items|numeric|min:0.0001',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi gagal', $validator->errors()->toArray(), 422);
        }

        try {
            $adjustment = $this->adjustmentService->updateStockAdjustment($stockAdjustment, $validator->validated());
            $adjustment->load(['warehouse', 'creator', 'approver', 'items.product.baseUnit', 'items.unit']);

            return $this->sendResponse($adjustment, "Penyesuaian Stok {$adjustment->adjustment_number} berhasil diperbarui.");
        } catch (\Exception $e) {
            return $this->sendError('Gagal memperbarui penyesuaian stok: '.$e->getMessage(), [], 422);
        }
    }

    /**
     * Approve Stock Adjustment and apply stock mutations.
     */
    public function approveAdjustment(StockAdjustment $stockAdjustment): JsonResponse
    {
        try {
            $this->adjustmentService->approveAdjustment($stockAdjustment);
            $stockAdjustment->load(['warehouse', 'creator', 'approver', 'items.product.baseUnit', 'items.unit']);

            return $this->sendResponse($stockAdjustment, "Penyesuaian Stok {$stockAdjustment->adjustment_number} telah disetujui & stok telah disesuaikan.");
        } catch (\Exception $e) {
            return $this->sendError('Gagal menyetujui penyesuaian stok: '.$e->getMessage(), [], 422);
        }
    }

    /**
     * Delete draft or cancel approved Stock Adjustment.
     */
    public function destroyAdjustment(StockAdjustment $stockAdjustment): JsonResponse
    {
        try {
            if ($stockAdjustment->status === 'draft') {
                $stockAdjustment->delete();

                return $this->sendResponse(null, "Draft Penyesuaian Stok {$stockAdjustment->adjustment_number} berhasil dihapus.");
            }

            $this->adjustmentService->cancelAdjustment($stockAdjustment);

            return $this->sendResponse(null, "Penyesuaian Stok {$stockAdjustment->adjustment_number} berhasil dibatalkan.");
        } catch (\Exception $e) {
            return $this->sendError('Gagal memproses pembatalan: '.$e->getMessage(), [], 422);
        }
    }
}
