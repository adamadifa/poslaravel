<?php

namespace App\Http\Controllers;

use App\Models\ConsignmentReceipt;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Services\ConsignmentService;
use Illuminate\Http\Request;

class ConsignmentReceiptController extends Controller
{
    protected ConsignmentService $consignmentService;

    public function __construct(ConsignmentService $consignmentService)
    {
        $this->consignmentService = $consignmentService;
    }

    /**
     * Display a listing of Consignment Receipts.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $supplierId = $request->query('supplier_id');
        $warehouseId = $request->query('warehouse_id');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $receipts = ConsignmentReceipt::with(['supplier', 'warehouse', 'items.product', 'items.unit'])
            ->when($search, function ($query, $search) {
                $query->where('receipt_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->when($startDate, fn ($q) => $q->whereDate('receipt_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('receipt_date', '<=', $endDate))
            ->latest('receipt_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        $products = Product::with(['baseUnit', 'conversions.fromUnit', 'consignmentSupplier'])
            ->where('is_active', true)
            ->where('is_consignment', true)
            ->orderBy('name')
            ->get();
        $units = Unit::where('is_active', true)->get();

        return view('consignments.receipts.index', [
            'title' => 'Penerimaan Konsinyasi',
            'headerTitle' => 'Penerimaan Barang Konsinyasi (Titip Jual)',
            'headerDescription' => 'Catat barang titip jual yang masuk dari supplier/mitra UMKM, menambah stok toko tanpa mencatat hutang dagang.',
            'breadcrumbParent' => 'Konsinyasi',
            'breadcrumbCurrent' => 'Penerimaan Titipan',
            'receipts' => $receipts,
            'suppliers' => $suppliers,
            'warehouses' => $warehouses,
            'products' => $products,
            'units' => $units,
            'search' => $search,
            'supplierId' => $supplierId,
            'warehouseId' => $warehouseId,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    /**
     * Store a newly created Consignment Receipt.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'receipt_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_id' => 'required|exists:units,id',
            'items.*.quantity' => 'required|numeric|gt:0',
            'items.*.consignment_cost' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        try {
            $receipt = $this->consignmentService->processConsignmentReceipt($validated);

            return redirect()->route('consignments.receipts.index')->with('success', "Penerimaan konsinyasi {$receipt->receipt_number} berhasil disimpan dan stok gudang telah diperbarui.");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memproses penerimaan konsinyasi: '.$e->getMessage());
        }
    }

    /**
     * Show details of a Consignment Receipt in JSON.
     */
    public function show(ConsignmentReceipt $receipt)
    {
        $receipt->load(['supplier', 'warehouse', 'user', 'items.product', 'items.unit']);

        return response()->json([
            'status' => 'success',
            'data' => $receipt,
        ]);
    }

    /**
     * Print Consignment Receipt Note.
     */
    public function print(ConsignmentReceipt $receipt)
    {
        $receipt->load(['supplier', 'warehouse', 'user', 'items.product', 'items.unit']);
        $storeName = Setting::get('store_name', config('app.name', 'POS Retail Pro'));
        $storeAddress = Setting::get('store_address', 'Jl. Jenderal Sudirman No. 123');
        $storePhone = Setting::get('store_phone', '0812-3456-7890');

        return view('consignments.receipts.print', [
            'receipt' => $receipt,
            'storeName' => $storeName,
            'storeAddress' => $storeAddress,
            'storePhone' => $storePhone,
        ]);
    }
}
