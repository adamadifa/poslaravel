<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseReceipt;
use App\Models\PurchaseReturn;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\PurchasingService;
use Illuminate\Http\Request;

class PurchaseReturnController extends Controller
{
    protected PurchasingService $purchasingService;

    public function __construct(PurchasingService $purchasingService)
    {
        $this->purchasingService = $purchasingService;
    }

    /**
     * Display a listing of the purchase returns.
     */
    public function index(Request $request)
    {
        $query = PurchaseReturn::with(['supplier', 'warehouse', 'receipt', 'user', 'items.product', 'items.unit']);

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('return_date', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        $returns = $query->latest('return_date')->latest('id')->paginate(15)->withQueryString();

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::with(['baseUnit', 'conversions.fromUnit', 'conversions.toUnit'])->where('is_active', true)->get();
        $receipts = PurchaseReceipt::with(['supplier', 'items.product.baseUnit', 'items.unit'])->where('status', 'confirmed')->latest('id')->limit(50)->get();

        return view('purchases.returns.index', [
            'title' => 'Retur Pembelian',
            'headerTitle' => 'Retur Pembelian (Purchase Return)',
            'headerDescription' => 'Kelola pengembalian barang rusak atau cacat ke supplier, penyesuaian stok gudang, dan pemotongan hutang usaha.',
            'breadcrumbParent' => 'Pembelian & Pengadaan',
            'breadcrumbCurrent' => 'Retur Pembelian',
            'returns' => $returns,
            'suppliers' => $suppliers,
            'warehouses' => $warehouses,
            'products' => $products,
            'receipts' => $receipts,
        ]);
    }

    /**
     * Store a newly created purchase return in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_receipt_id' => 'nullable|exists:purchase_receipts,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'return_date' => 'required|date',
            'reason' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_id' => 'required|exists:units,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.batch_number' => 'nullable|string',
            'items.*.purchase_receipt_item_id' => 'nullable|exists:purchase_receipt_items,id',
        ]);

        try {
            $return = $this->purchasingService->processPurchaseReturn($validated);

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Retur Pembelian {$return->return_number} berhasil diproses.",
                    'data' => $return
                ]);
            }

            return redirect()->route('purchase-returns.index')->with('success', "Retur Pembelian {$return->return_number} berhasil diproses.");
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->withInput()->with('error', 'Gagal memproses retur: ' . $e->getMessage());
        }
    }

    /**
     * Cancel/Delete a purchase return.
     */
    public function destroy(PurchaseReturn $purchaseReturn)
    {
        try {
            $this->purchasingService->cancelPurchaseReturn($purchaseReturn);
            return redirect()->route('purchase-returns.index')->with('success', "Retur {$purchaseReturn->return_number} berhasil dibatalkan dan stok dikembalikan.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membatalkan retur: ' . $e->getMessage());
        }
    }
}
