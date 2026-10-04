<?php

namespace App\Http\Controllers;

use App\Models\ConsignmentReturn;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Services\ConsignmentService;
use Illuminate\Http\Request;

class ConsignmentReturnController extends Controller
{
    protected ConsignmentService $consignmentService;

    public function __construct(ConsignmentService $consignmentService)
    {
        $this->consignmentService = $consignmentService;
    }

    /**
     * Display a listing of Consignment Returns.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $supplierId = $request->query('supplier_id');
        $warehouseId = $request->query('warehouse_id');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $returns = ConsignmentReturn::with(['supplier', 'warehouse', 'items.product', 'items.unit'])
            ->when($search, function ($query, $search) {
                $query->where('return_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->when($startDate, fn ($q) => $q->whereDate('return_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('return_date', '<=', $endDate))
            ->latest('return_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        $products = Product::with(['baseUnit', 'conversions.fromUnit', 'stocks', 'consignmentSupplier'])
            ->where('is_active', true)
            ->where('is_consignment', true)
            ->orderBy('name')
            ->get();
        $units = Unit::where('is_active', true)->get();

        return view('consignments.returns.index', [
            'title' => 'Retur Konsinyasi',
            'headerTitle' => 'Retur Barang Konsinyasi (Pengembalian ke Supplier)',
            'headerDescription' => 'Kembalikan barang titip jual yang tidak laku atau kadaluarsa kepada supplier penitip barang.',
            'breadcrumbParent' => 'Konsinyasi',
            'breadcrumbCurrent' => 'Retur Konsinyasi',
            'returns' => $returns,
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
     * Store a newly created Consignment Return.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'return_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_id' => 'required|exists:units,id',
            'items.*.quantity' => 'required|numeric|gt:0',
            'items.*.reason' => 'nullable|string',
        ]);

        try {
            $return = $this->consignmentService->processConsignmentReturn($validated);

            return redirect()->route('consignments.returns.index')->with('success', "Retur konsinyasi {$return->return_number} berhasil diproses dan stok gudang telah dikurangi.");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memproses retur konsinyasi: '.$e->getMessage());
        }
    }

    /**
     * Show details of a Consignment Return in JSON.
     */
    public function show(ConsignmentReturn $return)
    {
        $return->load(['supplier', 'warehouse', 'user', 'items.product', 'items.unit']);

        return response()->json([
            'status' => 'success',
            'data' => $return,
        ]);
    }

    /**
     * Print Consignment Return Note.
     */
    public function print(ConsignmentReturn $return)
    {
        $return->load(['supplier', 'warehouse', 'user', 'items.product', 'items.unit']);
        $storeName = Setting::get('store_name', config('app.name', 'POS Retail Pro'));
        $storeAddress = Setting::get('store_address', 'Jl. Jenderal Sudirman No. 123');
        $storePhone = Setting::get('store_phone', '0812-3456-7890');

        return view('consignments.returns.print', [
            'return' => $return,
            'storeName' => $storeName,
            'storeAddress' => $storeAddress,
            'storePhone' => $storePhone,
        ]);
    }
}
