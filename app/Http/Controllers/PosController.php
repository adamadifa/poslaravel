<?php

namespace App\Http\Controllers;

use App\Models\CashierShift;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\HeldTransaction;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Services\DiscountService;
use App\Services\PricingService;
use App\Services\SaleService;
use Illuminate\Http\Request;

class PosController extends Controller
{
    protected SaleService $saleService;
    protected PricingService $pricingService;
    protected DiscountService $discountService;

    public function __construct(
        SaleService $saleService,
        PricingService $pricingService,
        DiscountService $discountService
    ) {
        $this->saleService = $saleService;
        $this->pricingService = $pricingService;
        $this->discountService = $discountService;
    }

    /**
     * Display POS full-screen cashier workstation.
     */
    public function index(Request $request)
    {
        $userId = auth()->id();
        $warehouses = Warehouse::where('is_active', true)->get();
        $defaultWarehouse = Warehouse::where('is_default', true)->first() ?? $warehouses->first();

        // Check active shift
        $activeShift = CashierShift::with('warehouse')
            ->where('user_id', $userId)
            ->where('status', 'open')
            ->first();

        $categories = Category::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $customers = Customer::with('group')->where('is_active', true)->orderBy('name')->get();
        $units = Unit::where('is_active', true)->get();

        return view('pos.index', [
            'title' => 'Kasir POS Modern',
            'warehouses' => $warehouses,
            'defaultWarehouse' => $defaultWarehouse,
            'activeShift' => $activeShift,
            'categories' => $categories,
            'customers' => $customers,
            'units' => $units,
        ]);
    }

    /**
     * AJAX search products for POS grid with live stocks and pricing.
     */
    public function searchProducts(Request $request)
    {
        $search = $request->query('q');
        $categoryId = $request->query('category_id');
        $warehouseId = $request->query('warehouse_id');

        $products = Product::with([
            'category',
            'baseUnit',
            'barcodes.unit',
            'conversions.fromUnit',
            'conversions.toUnit',
            'priceLists.unit',
            'tieredPrices',
            'stocks' => function ($q) use ($warehouseId) {
                if ($warehouseId) {
                    $q->where('warehouse_id', $warehouseId);
                }
            }
        ])
        ->where('is_active', true)
        ->when($search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhereHas('barcodes', function ($b) use ($search) {
                      $b->where('barcode', 'like', "%{$search}%");
                  });
            });
        })
        ->when($categoryId, function ($query, $categoryId) {
            $query->where('category_id', $categoryId);
        })
        ->take(40)
        ->get();

        return response()->json([
            'status' => 'success',
            'data' => $products,
        ]);
    }

    /**
     * AJAX process checkout.
     */
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.unit_id' => ['required', 'exists:units,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:cash,transfer,qris,credit,split'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'promo_code' => ['nullable', 'string'],
            'manual_discount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $sale = $this->saleService->processSale($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi penjualan berhasil diselesaikan.',
                'data' => $sale,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memproses transaksi: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Hold current cart transaction.
     */
    public function holdTransaction(Request $request)
    {
        $validated = $request->validate([
            'reference_label' => ['required', 'string', 'max:100'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'cart_payload' => ['required', 'array'],
        ]);

        $held = HeldTransaction::create([
            'reference_label' => $validated['reference_label'],
            'warehouse_id' => $validated['warehouse_id'],
            'user_id' => auth()->id(),
            'customer_id' => $validated['customer_id'] ?? null,
            'cart_payload' => $validated['cart_payload'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Keranjang berhasil disimpan sementara sebagai '{$held->reference_label}'.",
            'data' => $held,
        ]);
    }

    /**
     * Get list of held transactions.
     */
    public function getHeldTransactions(Request $request)
    {
        $warehouseId = $request->query('warehouse_id');
        $heldList = HeldTransaction::with('customer')
            ->when($warehouseId, function ($q, $wId) {
                $q->where('warehouse_id', $wId);
            })
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $heldList,
        ]);
    }

    /**
     * Recall and delete a held transaction.
     */
    public function recallHeldTransaction(HeldTransaction $heldTransaction)
    {
        $payload = $heldTransaction->cart_payload;
        $label = $heldTransaction->reference_label;
        $heldTransaction->delete();

        return response()->json([
            'status' => 'success',
            'message' => "Keranjang '{$label}' berhasil dimuat kembali.",
            'data' => $payload,
        ]);
    }

    /**
     * Void a completed sale transaction.
     */
    public function voidSale(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ], [
            'reason.required' => 'Alasan pembatalan / void wajib diisi.',
        ]);

        try {
            $voidedSale = $this->saleService->voidSale($sale, $validated['reason'], auth()->id());

            return response()->json([
                'status' => 'success',
                'message' => "Faktur {$sale->invoice_number} berhasil di-VOID. Stok telah dikembalikan ke gudang.",
                'data' => $voidedSale,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal melakukan void: ' . $e->getMessage(),
            ], 422);
        }
    }
}
