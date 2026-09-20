<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\ProductStock;
use App\Models\TieredPrice;
use App\Models\UnitConversion;
use App\Models\Warehouse;
use App\Services\PricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductApiController extends BaseApiController
{
    /**
     * Store a newly created product.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:200'],
            'product_type' => ['nullable', 'in:standard,food,beverage,service,combo,raw_material'],
            'code' => ['nullable', 'string', 'max:50', 'unique:products,code'],
            'barcode' => ['nullable', 'string', 'max:50', 'unique:products,barcode'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'base_unit_id' => ['required', 'exists:units,id'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'min_stock' => ['nullable', 'numeric', 'min:0'],
            'max_stock' => ['nullable', 'numeric', 'min:0'],
            'brand' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'tax_type' => ['nullable', 'in:none,inclusive,exclusive'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'has_expiry' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'name.required' => 'Nama produk wajib diisi.',
            'code.unique' => 'Kode SKU produk sudah digunakan.',
            'barcode.unique' => 'Barcode sudah digunakan.',
            'base_unit_id.required' => 'Satuan dasar wajib dipilih.',
            'base_unit_id.exists' => 'Satuan dasar tidak valid.',
            'purchase_price.required' => 'Harga beli/HPP wajib diisi.',
            'selling_price.required' => 'Harga jual wajib diisi.',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        $validated = $validator->validated();

        DB::beginTransaction();
        try {
            $isRaw = ($validated['product_type'] ?? 'standard') === 'raw_material';

            // 1. Generate code if empty
            if (empty($validated['code'])) {
                if ($isRaw) {
                    $count = Product::where('product_type', 'raw_material')->withTrashed()->count() + 1;
                    $validated['code'] = 'RAW-'.str_pad((string) $count, 5, '0', STR_PAD_LEFT);
                } else {
                    $count = Product::withTrashed()->count() + 1;
                    $validated['code'] = 'PRD-'.str_pad((string) $count, 5, '0', STR_PAD_LEFT);
                }
            }

            // 2. Generate slug
            $validated['slug'] = Str::slug($validated['name']).'-'.strtolower(Str::random(5));

            // 3. Handle image upload if present
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('products', 'public');
                $validated['image_path'] = $path;
            }

            $validated['product_type'] = $validated['product_type'] ?? 'standard';
            $validated['is_active'] = $request->boolean('is_active', true);
            $validated['has_expiry'] = $request->boolean('has_expiry', false);

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

            // 6. Unit Conversions (Multi-Satuan)
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

            // 7. Tiered Prices (Harga Grosir / Multi-Harga)
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

            // 8. Auto-Sync Price Lists from Conversions
            app(PricingService::class)->syncPriceListsFromConversions($product);

            // 9. Inisialisasi stok awal di gudang aktif
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

            DB::commit();

            $product->load(['category', 'baseUnit', 'barcodes.unit', 'conversions.fromUnit', 'conversions.toUnit', 'tieredPrices.unit', 'priceLists.unit']);

            return $this->sendResponse($product, 'Produk berhasil ditambahkan.', 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->sendError('Gagal menambahkan produk: '.$e->getMessage(), [], 500);
        }
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:200'],
            'product_type' => ['nullable', 'in:standard,food,beverage,service,combo,raw_material'],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('products', 'code')->ignore($product->id)],
            'barcode' => ['nullable', 'string', 'max:50', Rule::unique('products', 'barcode')->ignore($product->id)],
            'category_id' => ['nullable', 'exists:categories,id'],
            'base_unit_id' => ['required', 'exists:units,id'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'min_stock' => ['nullable', 'numeric', 'min:0'],
            'max_stock' => ['nullable', 'numeric', 'min:0'],
            'brand' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'tax_type' => ['nullable', 'in:none,inclusive,exclusive'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'has_expiry' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'name.required' => 'Nama produk wajib diisi.',
            'code.unique' => 'Kode SKU produk sudah digunakan.',
            'barcode.unique' => 'Barcode sudah digunakan.',
            'base_unit_id.required' => 'Satuan dasar wajib dipilih.',
            'base_unit_id.exists' => 'Satuan dasar tidak valid.',
            'purchase_price.required' => 'Harga beli/HPP wajib diisi.',
            'selling_price.required' => 'Harga jual wajib diisi.',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        $validated = $validator->validated();

        DB::beginTransaction();
        try {
            if ($product->name !== $validated['name']) {
                $validated['slug'] = Str::slug($validated['name']).'-'.strtolower(Str::random(5));
            }

            if ($request->hasFile('image')) {
                if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                    Storage::disk('public')->delete($product->image_path);
                }
                $path = $request->file('image')->store('products', 'public');
                $validated['image_path'] = $path;
            }

            if ($request->has('is_active')) {
                $validated['is_active'] = $request->boolean('is_active');
            }
            if ($request->has('has_expiry')) {
                $validated['has_expiry'] = $request->boolean('has_expiry');
            }

            $product->update($validated);

            // Sync Multi-Barcode
            if ($request->has('barcodes')) {
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
            }

            // Sync Unit Conversions (Multi-Satuan)
            if ($request->has('conversions')) {
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
            }

            // Sync Tiered Prices (Harga Grosir / Multi-Harga)
            if ($request->has('tiered_prices')) {
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
            }

            // Auto-Sync Price Lists from Conversions
            app(PricingService::class)->syncPriceListsFromConversions($product);

            DB::commit();

            $product->load(['category', 'baseUnit', 'barcodes.unit', 'conversions.fromUnit', 'conversions.toUnit', 'tieredPrices.unit', 'priceLists.unit']);

            return $this->sendResponse($product, 'Produk berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->sendError('Gagal memperbarui produk: '.$e->getMessage(), [], 500);
        }
    }

    /**
     * Remove the specified product from storage (Soft Delete).
     */
    public function destroy(Product $product): JsonResponse
    {
        try {
            if ($product->usedInRecipes()->exists()) {
                return $this->sendError("Bahan baku '{$product->name}' sedang digunakan pada resep menu aktif dan tidak dapat dihapus.", [], 422);
            }

            $product->delete();

            return $this->sendResponse(null, 'Data berhasil dihapus.');
        } catch (\Throwable $e) {
            return $this->sendError('Gagal menghapus data: '.$e->getMessage(), [], 500);
        }
    }
}
