<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\Warehouse;
use App\Services\StockAdjustmentService;
use Illuminate\Http\Request;

class StockAdjustmentController extends Controller
{
    protected StockAdjustmentService $adjustmentService;

    public function __construct(StockAdjustmentService $adjustmentService)
    {
        $this->adjustmentService = $adjustmentService;
    }

    /**
     * Display a listing of Stock Adjustments.
     */
    public function index(Request $request)
    {
        $warehouseId = $request->query('warehouse_id');
        $type = $request->query('type');
        $status = $request->query('status');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $search = $request->query('search');

        $adjustments = StockAdjustment::with(['warehouse', 'creator', 'approver', 'items.product.baseUnit'])
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->when($type, fn($q) => $q->where('type', $type))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($startDate, fn($q) => $q->whereDate('adjustment_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('adjustment_date', '<=', $endDate))
            ->when($search, function ($q, $search) {
                $q->where('adjustment_number', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            })
            ->latest('adjustment_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::with(['baseUnit', 'stocks', 'conversions.fromUnit'])->where('is_active', true)->orderBy('name')->get();

        return view('stocks.adjustments.index', [
            'title' => 'Penyesuaian Stok',
            'headerTitle' => 'Penyesuaian Stok (Stock Adjustment)',
            'headerDescription' => 'Catat koreksi inventaris manual untuk barang rusak, kedaluwarsa, sample/bonus, atau penyesuaian khusus.',
            'breadcrumbParent' => 'Inventaris & Stok',
            'breadcrumbCurrent' => 'Penyesuaian Stok',
            'adjustments' => $adjustments,
            'warehouses' => $warehouses,
            'products' => $products,
            'warehouseId' => $warehouseId,
            'type' => $type,
            'status' => $status,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'search' => $search,
        ]);
    }

    /**
     * Store a newly created Stock Adjustment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'adjustment_date' => 'required|date',
            'type' => 'required|in:addition,reduction',
            'reason' => 'required|string',
            'notes' => 'nullable|string',
            'action' => 'nullable|in:draft,approve',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_id' => 'nullable|exists:units,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
            'items.*.batch_number' => 'nullable|string',
        ]);

        try {
            $adjustment = $this->adjustmentService->createStockAdjustment($validated);

            $msg = $adjustment->status === 'approved'
                ? "Penyesuaian stok {$adjustment->adjustment_number} berhasil dibuat & langsung diposting ke kartu stok."
                : "Draft penyesuaian stok {$adjustment->adjustment_number} berhasil disimpan.";

            return redirect()->route('stock-adjustments.index')->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal membuat penyesuaian stok: ' . $e->getMessage());
        }
    }

    /**
     * Show Adjustment Details via AJAX.
     */
    public function show(StockAdjustment $stockAdjustment)
    {
        $stockAdjustment->load(['warehouse', 'creator', 'approver', 'items.product.baseUnit', 'items.unit']);
        return response()->json($stockAdjustment);
    }

    /**
     * Update existing Stock Adjustment (Draft).
     */
    public function update(Request $request, StockAdjustment $stockAdjustment)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'adjustment_date' => 'required|date',
            'type' => 'required|in:addition,reduction',
            'reason' => 'required|string',
            'notes' => 'nullable|string',
            'action' => 'nullable|in:draft,approve',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_id' => 'nullable|exists:units,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
            'items.*.batch_number' => 'nullable|string',
        ]);

        try {
            $this->adjustmentService->updateStockAdjustment($stockAdjustment, $validated);

            $msg = $stockAdjustment->status === 'approved'
                ? "Penyesuaian stok {$stockAdjustment->adjustment_number} berhasil diperbarui & disetujui."
                : "Draft penyesuaian stok {$stockAdjustment->adjustment_number} berhasil diperbarui.";

            return redirect()->route('stock-adjustments.index')->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui penyesuaian stok: ' . $e->getMessage());
        }
    }

    /**
     * Approve Adjustment.
     */
    public function approve(StockAdjustment $stockAdjustment)
    {
        try {
            $this->adjustmentService->approveAdjustment($stockAdjustment);
            return redirect()->route('stock-adjustments.index')->with('success', "Penyesuaian stok {$stockAdjustment->adjustment_number} telah disetujui dan kartu mutasi stok telah diperbarui.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyetujui penyesuaian stok: ' . $e->getMessage());
        }
    }

    /**
     * Cancel or Delete Adjustment.
     */
    public function destroy(StockAdjustment $stockAdjustment)
    {
        try {
            if ($stockAdjustment->status === 'draft') {
                $stockAdjustment->delete();
                return redirect()->route('stock-adjustments.index')->with('success', "Draft penyesuaian stok {$stockAdjustment->adjustment_number} berhasil dihapus.");
            }

            $this->adjustmentService->cancelAdjustment($stockAdjustment);
            return redirect()->route('stock-adjustments.index')->with('success', "Penyesuaian stok {$stockAdjustment->adjustment_number} berhasil dibatalkan.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses penyesuaian stok: ' . $e->getMessage());
        }
    }
}
