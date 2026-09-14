<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\UnitConversion;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RawMaterialController extends Controller
{
    /**
     * Display a listing of raw materials.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $categoryId = $request->query('category_id');

        $rawMaterials = Product::with([
            'category',
            'baseUnit',
            'stocks.warehouse',
            'conversions.fromUnit',
            'conversions.toUnit',
            'usedInRecipes.product',
        ])
            ->where('product_type', 'raw_material')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%");
                });
            })
            ->when($categoryId, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $units = Unit::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->get();

        return view('raw_materials.index', [
            'title' => 'Master Bahan Baku',
            'headerTitle' => 'Master Bahan Baku & Racikan (Raw Materials)',
            'headerDescription' => 'Kelola stok bahan baku, satuan takaran (gram/ml/pcs), konversi pengadaan, dan biaya HPP modal untuk resep F&B.',
            'breadcrumbParent' => 'Master Data',
            'breadcrumbCurrent' => 'Bahan Baku',
            'rawMaterials' => $rawMaterials,
            'categories' => $categories,
            'units' => $units,
            'warehouses' => $warehouses,
            'search' => $search,
            'categoryId' => $categoryId,
        ]);
    }

    /**
     * Store a newly created raw material.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'code' => ['nullable', 'string', 'max:50', 'unique:products,code'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'base_unit_id' => ['required', 'exists:units,id'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'min_stock' => ['nullable', 'numeric', 'min:0'],
            'max_stock' => ['nullable', 'numeric', 'min:0'],
            'brand' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],

            // Conversions
            'conversions' => ['nullable', 'array'],
            'conversions.*.from_unit_id' => ['required_with:conversions', 'exists:units,id'],
            'conversions.*.to_unit_id' => ['required_with:conversions', 'exists:units,id'],
            'conversions.*.conversion_value' => ['required_with:conversions', 'numeric', 'gt:0'],
        ]);

        return DB::transaction(function () use ($request, $validated) {
            if (empty($validated['code'])) {
                $count = Product::where('product_type', 'raw_material')->withTrashed()->count() + 1;
                $validated['code'] = 'RAW-'.str_pad($count, 5, '0', STR_PAD_LEFT);
            }

            $validated['slug'] = Str::slug($validated['name']).'-'.strtolower(Str::random(5));
            $validated['product_type'] = 'raw_material';
            $validated['selling_price'] = 0;
            $validated['is_active'] = $request->has('is_active') ? (bool) $request->input('is_active') : true;

            $product = Product::create($validated);

            // Handle conversions
            if (! empty($validated['conversions'])) {
                foreach ($validated['conversions'] as $conv) {
                    if (! empty($conv['from_unit_id']) && ! empty($conv['to_unit_id']) && ! empty($conv['conversion_value'])) {
                        UnitConversion::create([
                            'product_id' => $product->id,
                            'from_unit_id' => $conv['from_unit_id'],
                            'to_unit_id' => $conv['to_unit_id'],
                            'conversion_value' => $conv['conversion_value'],
                        ]);
                    }
                }
            }

            return redirect()->route('raw-materials.index')->with('success', "Bahan baku '{$product->name}' berhasil ditambahkan.");
        });
    }

    /**
     * Update the specified raw material.
     */
    public function update(Request $request, Product $rawMaterial)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('products', 'code')->ignore($rawMaterial->id)],
            'category_id' => ['nullable', 'exists:categories,id'],
            'base_unit_id' => ['required', 'exists:units,id'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'min_stock' => ['nullable', 'numeric', 'min:0'],
            'max_stock' => ['nullable', 'numeric', 'min:0'],
            'brand' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],

            // Conversions
            'conversions' => ['nullable', 'array'],
            'conversions.*.from_unit_id' => ['required_with:conversions', 'exists:units,id'],
            'conversions.*.to_unit_id' => ['required_with:conversions', 'exists:units,id'],
            'conversions.*.conversion_value' => ['required_with:conversions', 'numeric', 'gt:0'],
        ]);

        return DB::transaction(function () use ($request, $validated, $rawMaterial) {
            $validated['is_active'] = $request->has('is_active');
            $rawMaterial->update($validated);

            // Sync conversions
            $rawMaterial->conversions()->delete();
            if (! empty($validated['conversions'])) {
                foreach ($validated['conversions'] as $conv) {
                    if (! empty($conv['from_unit_id']) && ! empty($conv['to_unit_id']) && ! empty($conv['conversion_value'])) {
                        UnitConversion::create([
                            'product_id' => $rawMaterial->id,
                            'from_unit_id' => $conv['from_unit_id'],
                            'to_unit_id' => $conv['to_unit_id'],
                            'conversion_value' => $conv['conversion_value'],
                        ]);
                    }
                }
            }

            return redirect()->route('raw-materials.index')->with('success', "Bahan baku '{$rawMaterial->name}' berhasil diperbarui.");
        });
    }

    /**
     * Remove the specified raw material from storage.
     */
    public function destroy(Product $rawMaterial)
    {
        // Check if used in recipes
        if ($rawMaterial->usedInRecipes()->exists()) {
            return redirect()->route('raw-materials.index')->with('error', "Bahan baku '{$rawMaterial->name}' sedang digunakan pada resep menu aktif dan tidak dapat dihapus.");
        }

        $rawMaterial->delete();

        return redirect()->route('raw-materials.index')->with('success', "Bahan baku '{$rawMaterial->name}' berhasil dihapus.");
    }
}
