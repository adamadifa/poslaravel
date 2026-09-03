<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use App\Services\StockTransferService;
use Illuminate\Http\Request;

class StockTransferController extends Controller
{
    protected StockTransferService $transferService;

    public function __construct(StockTransferService $transferService)
    {
        $this->transferService = $transferService;
    }

    /**
     * Display a listing of Stock Transfers.
     */
    public function index(Request $request)
    {
        $fromWarehouseId = $request->query('from_warehouse_id');
        $toWarehouseId = $request->query('to_warehouse_id');
        $status = $request->query('status');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $search = $request->query('search');

        $transfers = StockTransfer::with(['fromWarehouse', 'toWarehouse', 'sender', 'receiver', 'items.product.baseUnit'])
            ->when($fromWarehouseId, fn($q) => $q->where('from_warehouse_id', $fromWarehouseId))
            ->when($toWarehouseId, fn($q) => $q->where('to_warehouse_id', $toWarehouseId))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($startDate, fn($q) => $q->whereDate('transfer_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('transfer_date', '<=', $endDate))
            ->when($search, function ($q, $search) {
                $q->where('transfer_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            })
            ->latest('transfer_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::with(['baseUnit', 'stocks', 'conversions.fromUnit'])->where('is_active', true)->orderBy('name')->get();

        return view('stocks.transfers.index', [
            'title' => 'Transfer Antar Gudang',
            'headerTitle' => 'Transfer Stok Antar Gudang',
            'headerDescription' => 'Kelola mutasi pemindahan barang antar cabang/gudang, lacak status pengiriman, dan konfirmasi penerimaan barang.',
            'breadcrumbParent' => 'Inventaris & Stok',
            'breadcrumbCurrent' => 'Transfer Antar Gudang',
            'transfers' => $transfers,
            'warehouses' => $warehouses,
            'products' => $products,
            'fromWarehouseId' => $fromWarehouseId,
            'toWarehouseId' => $toWarehouseId,
            'status' => $status,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'search' => $search,
        ]);
    }

    /**
     * Store a newly created Stock Transfer.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
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

        try {
            $transfer = $this->transferService->createStockTransfer($validated);

            $msg = $transfer->status === 'in_transit' 
                ? "Transfer {$transfer->transfer_number} berhasil dibuat & langsung dikirim (In Transit)."
                : "Draft Transfer {$transfer->transfer_number} berhasil disimpan.";

            return redirect()->route('stock-transfers.index')->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal membuat transfer stok: ' . $e->getMessage());
        }
    }

    /**
     * Get Transfer Details via AJAX.
     */
    public function show(StockTransfer $stockTransfer)
    {
        $stockTransfer->load(['fromWarehouse', 'toWarehouse', 'sender', 'receiver', 'items.product.baseUnit', 'items.unit']);
        return response()->json($stockTransfer);
    }

    /**
     * Dispatch draft transfer (send goods).
     */
    public function dispatch(StockTransfer $stockTransfer)
    {
        try {
            $this->transferService->dispatchTransfer($stockTransfer);
            return redirect()->route('stock-transfers.index')->with('success', "Transfer {$stockTransfer->transfer_number} telah dikirim (In Transit). Stok gudang asal telah dikurangi.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengirim transfer: ' . $e->getMessage());
        }
    }

    /**
     * Confirm Receive Transfer (destination warehouse receives goods).
     */
    public function receive(Request $request, StockTransfer $stockTransfer)
    {
        $validated = $request->validate([
            'items' => 'nullable|array',
            'items.*.quantity_received' => 'nullable|numeric|min:0',
        ]);

        try {
            $this->transferService->receiveTransfer($stockTransfer, $validated);
            return redirect()->route('stock-transfers.index')->with('success', "Transfer {$stockTransfer->transfer_number} telah berhasil diterima. Stok gudang tujuan telah ditambahkan.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengonfirmasi penerimaan: ' . $e->getMessage());
        }
    }

    /**
     * Cancel or Delete Stock Transfer.
     */
    public function destroy(StockTransfer $stockTransfer)
    {
        try {
            if ($stockTransfer->status === 'draft') {
                $stockTransfer->delete();
                return redirect()->route('stock-transfers.index')->with('success', "Draft Transfer {$stockTransfer->transfer_number} berhasil dihapus.");
            }

            $this->transferService->cancelTransfer($stockTransfer);
            return redirect()->route('stock-transfers.index')->with('success', "Transfer {$stockTransfer->transfer_number} berhasil dibatalkan dan stok gudang asal dikembalikan.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membatalkan transfer: ' . $e->getMessage());
        }
    }
}
