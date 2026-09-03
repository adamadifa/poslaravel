<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReceipt;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Services\PurchasingService;
use Illuminate\Http\Request;

class PurchaseReceiptController extends Controller
{
    protected PurchasingService $purchasingService;

    public function __construct(PurchasingService $purchasingService)
    {
        $this->purchasingService = $purchasingService;
    }

    /**
     * Display a listing of Goods Receipts (GRN).
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $supplierId = $request->query('supplier_id');
        $warehouseId = $request->query('warehouse_id');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $receipts = PurchaseReceipt::with(['supplier', 'warehouse', 'purchaseOrder', 'items.product', 'items.unit'])
            ->when($search, function ($query, $search) {
                $query->where('grn_number', 'like', "%{$search}%")
                    ->orWhere('supplier_invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->when($supplierId, function ($query, $supplierId) {
                $query->where('supplier_id', $supplierId);
            })
            ->when($warehouseId, function ($query, $warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            })
            ->when($startDate, function ($query, $startDate) {
                $query->whereDate('receipt_date', '>=', $startDate);
            })
            ->when($endDate, function ($query, $endDate) {
                $query->whereDate('receipt_date', '<=', $endDate);
            })
            ->latest('receipt_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        $products = Product::with(['baseUnit', 'conversions.fromUnit'])->where('is_active', true)->orderBy('name')->get();
        $units = Unit::where('is_active', true)->get();
        $openPOs = PurchaseOrder::with(['supplier', 'warehouse', 'items.product', 'items.unit'])
            ->whereIn('status', ['sent', 'partial'])
            ->latest('id')
            ->get();

        return view('purchases.receipts.index', [
            'title' => 'Penerimaan Barang (GRN)',
            'headerTitle' => 'Penerimaan Barang / Surat Jalan (GRN)',
            'headerDescription' => 'Catat barang masuk dari supplier/PO, kalkulasi HPP FIFO otomatis, perbarui stok gudang, dan buat jadwal jatuh tempo hutang.',
            'breadcrumbParent' => 'Pembelian & Pengadaan',
            'breadcrumbCurrent' => 'Penerimaan Barang',
            'receipts' => $receipts,
            'suppliers' => $suppliers,
            'warehouses' => $warehouses,
            'products' => $products,
            'units' => $units,
            'openPOs' => $openPOs,
            'search' => $search,
            'supplierId' => $supplierId,
            'warehouseId' => $warehouseId,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    /**
     * Store a newly created Goods Receipt (GRN).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'receipt_date' => 'required|date',
            'supplier_invoice_number' => 'nullable|string',
            'tax_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_id' => 'required|exists:units,id',
            'items.*.purchase_order_item_id' => 'nullable|exists:purchase_order_items,id',
            'items.*.quantity_received' => 'required|numeric|min:0.0001',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.batch_number' => 'nullable|string',
            'items.*.expiry_date' => 'nullable|date',
        ]);

        try {
            $receipt = $this->purchasingService->processGoodsReceipt($validated);

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Penerimaan Barang {$receipt->grn_number} berhasil dicatat & stok bertambah.",
                    'data' => $receipt
                ]);
            }

            return redirect()->route('purchase-receipts.index')->with('success', "Penerimaan Barang {$receipt->grn_number} berhasil dicatat & stok bertambah.");
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->withInput()->with('error', 'Gagal mencatat penerimaan: ' . $e->getMessage());
        }
    }
}
