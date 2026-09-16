<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\ProductStock;
use App\Models\Setting;
use App\Models\TieredPrice;
use App\Models\Unit;
use App\Models\UnitConversion;
use App\Models\Warehouse;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $categoryId = $request->query('category_id');
        $type = $request->query('type');

        $products = Product::with([
            'category',
            'baseUnit',
            'stocks',
            'barcodes.unit',
            'conversions.fromUnit',
            'conversions.toUnit',
            'priceLists.unit',
            'tieredPrices.unit',
            'tieredPrices.customerGroup',
            'recipes.ingredient',
        ])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%");
                });
            })
            ->when($categoryId, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($type, function ($query, $type) {
                if ($type === 'fnb') {
                    $query->whereIn('product_type', ['food', 'beverage']);
                } else {
                    $query->where('product_type', $type);
                }
            }, function ($query) {
                $query->where('product_type', '!=', 'raw_material');
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $units = Unit::where('is_active', true)->orderBy('name')->get();
        $customerGroups = CustomerGroup::orderBy('name')->get();
        $storeName = Setting::get('store_name', config('app.name', 'POS Retail Pro'));

        return view('products.index', [
            'title' => 'Master Produk',
            'headerTitle' => 'Master Produk & Katalog',
            'headerDescription' => 'Kelola katalog produk, multi-barcode scanner kasir, rasio konversi satuan, dan harga berjenjang.',
            'breadcrumbParent' => 'Master Data',
            'breadcrumbCurrent' => 'Master Produk',
            'products' => $products,
            'categories' => $categories,
            'units' => $units,
            'customerGroups' => $customerGroups,
            'search' => $search,
            'categoryId' => $categoryId,
            'storeName' => $storeName,
        ]);
    }

    /**
     * Search products with barcodes and multi-units for barcode printing queue.
     */
    public function searchForBarcode(Request $request)
    {
        $q = $request->query('q');
        $pricingService = app(PricingService::class);

        $products = Product::with(['baseUnit', 'barcodes.unit', 'conversions.toUnit', 'conversions.fromUnit', 'priceLists.unit'])
            ->where('is_active', true)
            ->when($q, function ($query, $q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('code', 'like', "%{$q}%")
                        ->orWhere('barcode', 'like', "%{$q}%");
                });
            })
            ->limit(25)
            ->get();

        $results = [];
        foreach ($products as $prod) {
            $baseBarcode = $prod->barcode ?: $prod->code;
            $baseUnitName = $prod->baseUnit ? $prod->baseUnit->display_name : 'Pcs';
            $basePrice = (float) $prod->selling_price;

            // 1. Base unit item
            $results[] = [
                'id' => $prod->id.'_base',
                'product_id' => $prod->id,
                'name' => $prod->name,
                'code' => $prod->code,
                'barcode' => $baseBarcode,
                'unit_id' => $prod->base_unit_id,
                'unit_name' => $baseUnitName,
                'price' => $basePrice,
                'is_base' => true,
                'label' => "{$prod->name} ({$baseUnitName}) - {$baseBarcode} - Rp ".number_format($basePrice, 0, ',', '.'),
            ];

            $processedUnits = [$prod->base_unit_id];

            // 2. Barcodes per unit
            if ($prod->barcodes) {
                foreach ($prod->barcodes as $mb) {
                    if ($mb->unit_id && ! in_array($mb->unit_id, $processedUnits)) {
                        $mUnitName = $mb->unit ? $mb->unit->display_name : 'Satuan';
                        $unitPriceData = $pricingService->resolvePrice($prod, $mb->unit_id, 1);
                        $resolvedPrice = (float) ($unitPriceData['regular_unit_price'] ?? $prod->selling_price);
                        $processedUnits[] = $mb->unit_id;

                        $results[] = [
                            'id' => $prod->id.'_barcode_'.$mb->id,
                            'product_id' => $prod->id,
                            'name' => $prod->name,
                            'code' => $prod->code,
                            'barcode' => $mb->barcode ?: $baseBarcode,
                            'unit_id' => $mb->unit_id,
                            'unit_name' => $mUnitName,
                            'price' => $resolvedPrice,
                            'is_base' => false,
                            'label' => "{$prod->name} [{$mUnitName}] - ".($mb->barcode ?: $baseBarcode).' - Rp '.number_format($resolvedPrice, 0, ',', '.'),
                        ];
                    }
                }
            }

            // 3. Conversions without explicit barcode row
            if ($prod->conversions) {
                foreach ($prod->conversions as $conv) {
                    if ($conv->from_unit_id && ! in_array($conv->from_unit_id, $processedUnits)) {
                        $fromUnitName = $conv->fromUnit ? $conv->fromUnit->display_name : 'Satuan';
                        $unitPriceData = $pricingService->resolvePrice($prod, $conv->from_unit_id, 1);
                        $resolvedPrice = (float) ($unitPriceData['regular_unit_price'] ?? ($prod->selling_price * $conv->conversion_value));
                        $processedUnits[] = $conv->from_unit_id;

                        $results[] = [
                            'id' => $prod->id.'_conv_'.$conv->id,
                            'product_id' => $prod->id,
                            'name' => $prod->name,
                            'code' => $prod->code,
                            'barcode' => $baseBarcode,
                            'unit_id' => $conv->from_unit_id,
                            'unit_name' => $fromUnitName,
                            'price' => $resolvedPrice,
                            'is_base' => false,
                            'label' => "{$prod->name} [{$fromUnitName}] - {$baseBarcode} - Rp ".number_format($resolvedPrice, 0, ',', '.'),
                        ];
                    }
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'results' => $results,
        ]);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            // 1. Generate code if empty
            if (empty($validated['code'])) {
                $count = Product::withTrashed()->count() + 1;
                $validated['code'] = 'PRD-'.str_pad($count, 5, '0', STR_PAD_LEFT);
            }

            // 2. Generate slug
            $validated['slug'] = Str::slug($validated['name']).'-'.strtolower(Str::random(5));

            // 3. Handle image upload
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('products', 'public');
                $validated['image_path'] = $path;
            }

            $validated['is_active'] = $request->has('is_active') ? true : false;
            $validated['has_expiry'] = $request->has('has_expiry') ? true : false;
            $validated['is_bookable'] = $request->has('is_bookable') ? true : false;
            $validated['require_staff_assignment'] = $request->has('require_staff_assignment') ? true : false;

            // 4. Create Product
            $product = Product::create($validated);

            // 5. Multi-Barcode
            if (! empty($request->input('barcodes'))) {
                foreach ($request->input('barcodes') as $item) {
                    if (! empty($item['barcode']) && ! empty($item['unit_id'])) {
                        ProductBarcode::create([
                            'product_id' => $product->id,
                            'unit_id' => $item['unit_id'],
                            'barcode' => $item['barcode'],
                            'is_primary' => false,
                        ]);
                    }
                }
            }

            // 6. Unit Conversions
            if (! empty($request->input('conversions'))) {
                foreach ($request->input('conversions') as $item) {
                    if (! empty($item['from_unit_id']) && ! empty($item['to_unit_id']) && ! empty($item['conversion_value'])) {
                        UnitConversion::create([
                            'product_id' => $product->id,
                            'from_unit_id' => $item['from_unit_id'],
                            'to_unit_id' => $item['to_unit_id'],
                            'conversion_value' => $item['conversion_value'],
                        ]);
                    }
                }
            }

            // 7. Inisialisasi Stok Awal (Task 1.10) ke setiap gudang aktif
            $warehouses = Warehouse::where('is_active', true)->get();
            foreach ($warehouses as $wh) {
                ProductStock::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'warehouse_id' => $wh->id,
                    ],
                    [
                        'quantity' => 0,
                        'reserved_qty' => 0,
                    ]
                );
            }

            // 8. Auto-Sync Price Lists from Conversions (Task 2.1)
            app(PricingService::class)->syncPriceListsFromConversions($product);

            DB::commit();

            return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan produk: '.$e->getMessage());
        }
    }

    /**
     * Update the specified product in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            // 1. Slug update if name changed
            if ($product->name !== $validated['name']) {
                $validated['slug'] = Str::slug($validated['name']).'-'.strtolower(Str::random(5));
            }

            // 2. Handle image upload
            if ($request->hasFile('image')) {
                if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                    Storage::disk('public')->delete($product->image_path);
                }
                $path = $request->file('image')->store('products', 'public');
                $validated['image_path'] = $path;
            }

            $validated['is_active'] = $request->has('is_active') ? true : false;
            $validated['has_expiry'] = $request->has('has_expiry') ? true : false;
            $validated['is_bookable'] = $request->has('is_bookable') ? true : false;
            $validated['require_staff_assignment'] = $request->has('require_staff_assignment') ? true : false;

            // 3. Update Product
            $product->update($validated);

            // 4. Sync Multi-Barcode
            $product->barcodes()->delete();
            if (! empty($request->input('barcodes'))) {
                foreach ($request->input('barcodes') as $item) {
                    if (! empty($item['barcode']) && ! empty($item['unit_id'])) {
                        ProductBarcode::create([
                            'product_id' => $product->id,
                            'unit_id' => $item['unit_id'],
                            'barcode' => $item['barcode'],
                            'is_primary' => false,
                        ]);
                    }
                }
            }

            // 5. Sync Unit Conversions
            $product->conversions()->delete();
            if (! empty($request->input('conversions'))) {
                foreach ($request->input('conversions') as $item) {
                    if (! empty($item['from_unit_id']) && ! empty($item['to_unit_id']) && ! empty($item['conversion_value'])) {
                        UnitConversion::create([
                            'product_id' => $product->id,
                            'from_unit_id' => $item['from_unit_id'],
                            'to_unit_id' => $item['to_unit_id'],
                            'conversion_value' => $item['conversion_value'],
                        ]);
                    }
                }
            }

            // 6. Sync Tiered Prices (Harga Berjenjang)
            $product->tieredPrices()->delete();
            if (! empty($request->input('tiered_prices'))) {
                foreach ($request->input('tiered_prices') as $tp) {
                    if (! empty($tp['unit_id']) && ! empty($tp['min_qty']) && ! empty($tp['price'])) {
                        TieredPrice::create([
                            'product_id' => $product->id,
                            'unit_id' => $tp['unit_id'],
                            'customer_group_id' => ! empty($tp['customer_group_id']) ? $tp['customer_group_id'] : null,
                            'min_qty' => $tp['min_qty'],
                            'max_qty' => ! empty($tp['max_qty']) ? $tp['max_qty'] : null,
                            'price' => $tp['price'],
                            'is_active' => true,
                        ]);
                    }
                }
            }

            // 7. Auto-Sync Price Lists from Conversions (Task 2.1)
            app(PricingService::class)->syncPriceListsFromConversions($product);

            DB::commit();

            return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui produk: '.$e->getMessage());
        }
    }

    /**
     * AJAX endpoint: Resolve unit price based on selected unit, quantity, and customer.
     */
    public function getPrice(Request $request, Product $product)
    {
        $unitId = (int) $request->query('unit_id', $product->base_unit_id);
        $qty = (float) $request->query('quantity', 1);
        $customerId = $request->query('customer_id');

        $customer = $customerId ? Customer::with('group')->find($customerId) : null;

        $pricingService = app(PricingService::class);
        $priceData = $pricingService->resolvePrice($product, $unitId, $qty, $customer);

        return response()->json([
            'status' => 'success',
            'data' => $priceData,
        ]);
    }

    /**
     * Remove the specified product from storage (Soft Delete).
     */
    public function destroy(Product $product)
    {
        try {
            $product->delete();

            return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('products.index')->with('error', 'Gagal menghapus produk: '.$e->getMessage());
        }
    }
}
