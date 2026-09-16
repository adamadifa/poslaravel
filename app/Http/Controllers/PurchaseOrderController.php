<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Services\PurchasingService;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    protected PurchasingService $purchasingService;

    public function __construct(PurchasingService $purchasingService)
    {
        $this->purchasingService = $purchasingService;
    }

    /**
     * Display a listing of Purchase Orders.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $supplierId = $request->query('supplier_id');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $orders = PurchaseOrder::with(['supplier', 'warehouse', 'items.product'])
            ->when($search, function ($query, $search) {
                $query->where('po_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($supplierId, function ($query, $supplierId) {
                $query->where('supplier_id', $supplierId);
            })
            ->when($startDate, function ($query, $startDate) {
                $query->whereDate('order_date', '>=', $startDate);
            })
            ->when($endDate, function ($query, $endDate) {
                $query->whereDate('order_date', '<=', $endDate);
            })
            ->latest('order_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        $products = Product::with(['baseUnit', 'conversions.fromUnit'])->where('is_active', true)->orderBy('name')->get();
        $units = Unit::where('is_active', true)->get();

        return view('purchases.orders.index', [
            'title' => 'Purchase Order (PO)',
            'headerTitle' => 'Purchase Order (Pesanan Pembelian)',
            'headerDescription' => 'Buat dan kelola pesanan barang ke supplier, pantau status pengiriman, dan lacak realisasi penerimaan.',
            'breadcrumbParent' => 'Pembelian & Pengadaan',
            'breadcrumbCurrent' => 'Purchase Order',
            'orders' => $orders,
            'suppliers' => $suppliers,
            'warehouses' => $warehouses,
            'products' => $products,
            'units' => $units,
            'search' => $search,
            'status' => $status,
            'supplierId' => $supplierId,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    /**
     * Store a newly created PO.
     */
    public function store(Request $request)
    {
        $this->normalizeNumericInputs($request);

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:order_date',
            'status' => 'required|in:draft,sent',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_id' => 'required|exists:units,id',
            'items.*.quantity_ordered' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $po = $this->purchasingService->createPurchaseOrder($validated);

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Purchase Order {$po->po_number} berhasil dibuat.",
                    'data' => $po,
                ]);
            }

            return redirect()->route('purchase-orders.index')->with('success', "Purchase Order {$po->po_number} berhasil dibuat.");
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
            }

            return redirect()->back()->withInput()->with('error', 'Gagal membuat PO: '.$e->getMessage());
        }
    }

    /**
     * Update an existing PO.
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (in_array($purchaseOrder->status, ['received', 'partial'])) {
            return redirect()->back()->with('error', 'PO yang sudah memiliki penerimaan barang (GRN) tidak dapat diedit.');
        }

        $this->normalizeNumericInputs($request);

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date',
            'status' => 'required|in:draft,sent',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_id' => 'required|exists:units,id',
            'items.*.quantity_ordered' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $po = $this->purchasingService->updatePurchaseOrder($purchaseOrder, $validated);

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Purchase Order {$po->po_number} berhasil diperbarui.",
                    'data' => $po,
                ]);
            }

            return redirect()->route('purchase-orders.index')->with('success', "Purchase Order {$po->po_number} berhasil diperbarui.");
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
            }

            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui PO: '.$e->getMessage());
        }
    }

    /**
     * Update PO status (e.g., draft -> sent, or cancelled).
     */
    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,sent,cancelled',
        ]);

        if (in_array($purchaseOrder->status, ['received', 'partial']) && $validated['status'] === 'cancelled') {
            return redirect()->back()->with('error', 'PO yang sudah memiliki penerimaan barang tidak dapat dibatalkan.');
        }

        $purchaseOrder->update(['status' => $validated['status']]);

        return redirect()->route('purchase-orders.index')->with('success', "Status PO {$purchaseOrder->po_number} diubah menjadi {$purchaseOrder->status}.");
    }

    /**
     * Remove or cancel the specified PO from storage.
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if (in_array($purchaseOrder->status, ['received', 'partial'])) {
            return redirect()->back()->with('error', 'PO yang sudah memiliki penerimaan barang (GRN) tidak dapat dihapus / dibatalkan.');
        }

        $poNumber = $purchaseOrder->po_number;
        $purchaseOrder->delete();

        return redirect()->route('purchase-orders.index')->with('success', "Purchase Order {$poNumber} berhasil dihapus / dibatalkan.");
    }

    /**
     * Get PO details via AJAX for Goods Receipt autoloading.
     */
    public function getDetails(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'warehouse', 'items.product.baseUnit', 'items.unit']);

        return response()->json([
            'status' => 'success',
            'data' => $purchaseOrder,
        ]);
    }

    /**
     * Print Purchase Order document in standard A4 format.
     */
    public function print(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'supplier',
            'warehouse',
            'user',
            'items.product.baseUnit',
            'items.unit',
        ]);

        $company = [
            'name' => Setting::get('company_name', 'WarungPro'),
            'tagline' => Setting::get('company_tagline', 'Solusi Kasir & Manajemen Ritel Modern'),
            'address' => Setting::get('company_address', 'Jl. Sudirman No. 45, Jakarta Pusat'),
            'phone' => Setting::get('company_phone', '0812-3456-7890'),
            'email' => Setting::get('company_email', 'support@pospro.com'),
            'npwp' => Setting::get('company_npwp', '-'),
            'logo' => Setting::get('company_logo', null),
        ];

        return view('purchases.orders.print', compact('purchaseOrder', 'company'));
    }

    /**
     * Normalize comma-separated decimal inputs to standard dot notation.
     */
    private function normalizeNumericInputs(Request $request): void
    {
        if ($request->has('items') && is_array($request->input('items'))) {
            $items = $request->input('items');
            foreach ($items as $i => $item) {
                if (isset($item['unit_price']) && is_string($item['unit_price'])) {
                    $items[$i]['unit_price'] = str_replace(',', '.', trim($item['unit_price']));
                }
                if (isset($item['quantity_ordered']) && is_string($item['quantity_ordered'])) {
                    $items[$i]['quantity_ordered'] = str_replace(',', '.', trim($item['quantity_ordered']));
                }
                if (isset($item['discount_percent']) && is_string($item['discount_percent'])) {
                    $items[$i]['discount_percent'] = str_replace(',', '.', trim($item['discount_percent']));
                }
            }
            $request->merge(['items' => $items]);
        }

        foreach (['discount_amount', 'tax_amount', 'shipping_cost'] as $field) {
            if ($request->has($field) && is_string($request->input($field))) {
                $request->merge([$field => str_replace(',', '.', trim($request->input($field)))]);
            }
        }
    }
}
