<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockOpname;
use App\Models\Warehouse;
use App\Services\StockOpnameService;
use Illuminate\Http\Request;

class StockOpnameController extends Controller
{
    protected StockOpnameService $opnameService;

    public function __construct(StockOpnameService $opnameService)
    {
        $this->opnameService = $opnameService;
    }

    /**
     * Display a listing of Stock Opnames.
     */
    public function index(Request $request)
    {
        $warehouseId = $request->query('warehouse_id');
        $status = $request->query('status');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $search = $request->query('search');

        $opnames = StockOpname::with(['warehouse', 'conductor', 'approver', 'items.product'])
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($startDate, fn($q) => $q->whereDate('opname_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('opname_date', '<=', $endDate))
            ->when($search, function ($q, $search) {
                $q->where('opname_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            })
            ->latest('opname_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::with(['baseUnit', 'stocks'])->where('is_active', true)->orderBy('name')->get();

        return view('stocks.opnames.index', [
            'title' => 'Stok Opname',
            'headerTitle' => 'Audit & Stok Opname',
            'headerDescription' => 'Audit fisik berkala inventaris gudang, rekonsiliasi selisih sistem vs fisik, dan penyesuaian stok otomatis.',
            'breadcrumbParent' => 'Inventaris & Stok',
            'breadcrumbCurrent' => 'Stok Opname',
            'opnames' => $opnames,
            'warehouses' => $warehouses,
            'products' => $products,
            'warehouseId' => $warehouseId,
            'status' => $status,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'search' => $search,
        ]);
    }

    /**
     * Store a newly created Stock Opname.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'opname_date' => 'required|date',
            'status' => 'required|in:draft,in_progress',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.physical_qty' => 'required|numeric|min:0',
            'items.*.reason' => 'nullable|string',
        ]);

        try {
            $opname = $this->opnameService->createStockOpname($validated);

            return redirect()->route('stock-opnames.index')->with('success', "Dokumen Stok Opname {$opname->opname_number} berhasil dibuat.");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal membuat stok opname: ' . $e->getMessage());
        }
    }

    /**
     * Get Opname Details (Blade View for regular requests, JSON for AJAX)
     */
    public function show(Request $request, StockOpname $stockOpname)
    {
        $stockOpname->load(['warehouse', 'conductor', 'approver', 'items.product.baseUnit']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($stockOpname);
        }

        return view('stocks.opnames.show', compact('stockOpname'));
    }

    /**
     * Update existing Stock Opname.
     */
    public function update(Request $request, StockOpname $stockOpname)
    {
        $validated = $request->validate([
            'opname_date' => 'required|date',
            'status' => 'required|in:draft,in_progress',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.physical_qty' => 'required|numeric|min:0',
            'items.*.reason' => 'nullable|string',
        ]);

        try {
            $this->opnameService->updateStockOpname($stockOpname, $validated);

            return redirect()->route('stock-opnames.index')->with('success', "Stok Opname {$stockOpname->opname_number} berhasil diperbarui.");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui stok opname: ' . $e->getMessage());
        }
    }

    /**
     * Approve and Execute Stock Opname Adjustment.
     */
    public function approve(StockOpname $stockOpname)
    {
        try {
            $this->opnameService->approveStockOpname($stockOpname);

            return redirect()->route('stock-opnames.index')->with('success', "Stok Opname {$stockOpname->opname_number} telah disetujui. Mutasi stok dan batch persediaan telah diperbarui otomatis.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyetujui stok opname: ' . $e->getMessage());
        }
    }

    /**
     * Cancel or Delete Stock Opname.
     */
    public function destroy(StockOpname $stockOpname)
    {
        try {
            if ($stockOpname->status === 'draft') {
                $stockOpname->delete();
                return redirect()->route('stock-opnames.index')->with('success', "Draft Stok Opname {$stockOpname->opname_number} berhasil dihapus.");
            }

            $this->opnameService->cancelStockOpname($stockOpname);
            return redirect()->route('stock-opnames.index')->with('success', "Stok Opname {$stockOpname->opname_number} berhasil dibatalkan.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses penghapusan: ' . $e->getMessage());
        }
    }
}
