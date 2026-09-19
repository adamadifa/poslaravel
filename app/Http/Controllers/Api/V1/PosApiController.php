<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Customer;
use App\Models\HeldTransaction;
use App\Models\Product;
use App\Models\Sale;
use App\Services\DiscountService;
use App\Services\PricingService;
use App\Services\SaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PosApiController extends BaseApiController
{
    public function __construct(
        protected SaleService $saleService,
        protected DiscountService $discountService
    ) {}

    /**
     * Search products for POS catalog.
     */
    public function searchProducts(Request $request): JsonResponse
    {
        $search = $request->query('q');
        $categoryId = $request->query('category_id');
        $warehouseId = $request->query('warehouse_id');

        $products = Product::with([
            'category',
            'baseUnit',
            'conversions.fromUnit',
            'conversions.toUnit',
            'priceLists.unit',
            'tieredPrices.unit',
            'modifierGroups.modifiers',
            'stocks' => function ($q) use ($warehouseId) {
                if ($warehouseId) {
                    $q->where('warehouse_id', $warehouseId);
                }
            },
        ])
            ->where('is_active', true)
            ->where('product_type', '!=', 'raw_material')
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
            ->take(50)
            ->get();

        return $this->sendResponse($products, 'Katalog produk berhasil dimuat.');
    }

    /**
     * Calculate cart discounts and grand total.
     */
    public function calculateCart(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => ['nullable', 'exists:customers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.unit_id' => ['required', 'exists:units,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'promo_code' => ['nullable', 'string'],
            'manual_discount' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        $validated = $validator->validated();
        $customer = ! empty($validated['customer_id']) ? Customer::with('group')->find($validated['customer_id']) : null;
        $items = $validated['items'];
        $promoCode = $validated['promo_code'] ?? null;
        $manualDiscount = (float) ($validated['manual_discount'] ?? 0);

        $discResult = $this->discountService->calculateCartDiscounts($items, $customer, $promoCode);
        $totalDiscount = $discResult['total_discount'] + $manualDiscount;
        $grandTotal = max(0, $discResult['subtotal'] - $totalDiscount);

        return $this->sendResponse(array_merge($discResult, [
            'manual_discount' => $manualDiscount,
            'total_discount' => $totalDiscount,
            'grand_total' => $grandTotal,
        ]), 'Kalkulasi keranjang berhasil.');
    }

    /**
     * Process POS checkout.
     */
    public function checkout(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'service_type' => ['nullable', 'string', 'in:dine_in,takeaway,delivery'],
            'dining_table_id' => ['nullable', 'exists:dining_tables,id'],
            'guest_count' => ['nullable', 'integer', 'min:1'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.unit_id' => ['required', 'exists:units,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
            'items.*.modifiers' => ['nullable', 'array'],
            'items.*.modifiers.*.id' => ['nullable', 'exists:modifiers,id'],
            'items.*.modifiers.*.name' => ['nullable', 'string'],
            'items.*.modifiers.*.price_adjustment' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:cash,transfer,qris,credit,split'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'promo_code' => ['nullable', 'string'],
            'manual_discount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Checkout Gagal', $validator->errors()->all(), 422);
        }

        try {
            $sale = $this->saleService->processSale($validator->validated());

            return $this->sendResponse($sale, 'Transaksi penjualan berhasil diproses.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal memproses transaksi: '.$e->getMessage(), [], 422);
        }
    }

    /**
     * Hold cart transaction.
     */
    public function holdTransaction(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reference_label' => ['required', 'string', 'max:100'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'cart_payload' => ['required', 'array'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        $validated = $validator->validated();
        $held = HeldTransaction::create([
            'reference_label' => $validated['reference_label'],
            'warehouse_id' => $validated['warehouse_id'],
            'user_id' => auth()->id(),
            'customer_id' => $validated['customer_id'] ?? null,
            'cart_payload' => $validated['cart_payload'],
        ]);

        return $this->sendResponse($held, "Pesanan berhasil di-hold sebagai '{$held->reference_label}'.");
    }

    /**
     * Get held transactions list.
     */
    public function getHeldTransactions(Request $request): JsonResponse
    {
        $warehouseId = $request->query('warehouse_id');
        $heldList = HeldTransaction::with('customer')
            ->when($warehouseId, function ($q, $wId) {
                $q->where('warehouse_id', $wId);
            })
            ->latest()
            ->get();

        return $this->sendResponse($heldList, 'Daftar held transaction berhasil dimuat.');
    }

    /**
     * Recall held transaction.
     */
    public function recallHeldTransaction($id): JsonResponse
    {
        $held = HeldTransaction::find($id);
        if (! $held) {
            return $this->sendError('Transaksi tahan tidak ditemukan.', [], 404);
        }

        $payload = $held->cart_payload;
        $held->delete();

        return $this->sendResponse([
            'held_id' => $id,
            'cart_payload' => $payload,
        ], 'Transaksi berhasil di-recall.');
    }

    /**
     * Void a sale.
     */
    public function voidSale(Sale $sale): JsonResponse
    {
        try {
            $voided = $this->saleService->voidSale($sale, auth()->id());

            return $this->sendResponse($voided, 'Transaksi penjualan berhasil di-void/dibatalkan.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal membatalkan transaksi: '.$e->getMessage(), [], 422);
        }
    }

    /**
     * Resolve unit price dynamically based on unit, quantity, and customer.
     */
    public function getProductPrice(Request $request, Product $product): JsonResponse
    {
        $unitId = (int) $request->query('unit_id', $product->base_unit_id);
        $qty = (float) $request->query('quantity', 1);
        $customerId = $request->query('customer_id');

        $customer = $customerId ? Customer::with('group')->find($customerId) : null;
        $priceData = app(PricingService::class)->resolvePrice($product, $unitId, $qty, $customer);

        return $this->sendResponse($priceData, 'Harga satuan berhasil dihitung.');
    }
}
