<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWarehouseRequest;
use App\Http\Requests\UpdateWarehouseRequest;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseController extends Controller
{
    /**
     * Display a listing of warehouses.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $warehouses = Warehouse::withCount('stocks')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%");
            })
            ->orderByDesc('is_default')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('warehouses.index', [
            'title' => 'Master Gudang & Cabang',
            'headerTitle' => 'Master Gudang & Cabang (Outlets)',
            'headerDescription' => 'Kelola lokasi multi-gudang, cabang toko outlet, gudang default transaksi kasir, dan penampung stok.',
            'breadcrumbParent' => 'Master Data',
            'breadcrumbCurrent' => 'Gudang & Cabang',
            'warehouses' => $warehouses,
            'search' => $search,
        ]);
    }

    /**
     * Store a newly created warehouse in storage.
     */
    public function store(StoreWarehouseRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            // Auto-generate warehouse code if empty
            if (empty($validated['code'])) {
                $count = Warehouse::count() + 1;
                $validated['code'] = 'WH-' . str_pad($count, 3, '0', STR_PAD_LEFT);
            }

            $validated['is_active'] = $request->has('is_active') ? true : false;
            $isDefault = $request->has('is_default') ? true : false;

            // If this is set as default or first warehouse, unset previous defaults
            if ($isDefault || Warehouse::count() === 0) {
                Warehouse::where('is_default', true)->update(['is_default' => false]);
                $validated['is_default'] = true;
            } else {
                $validated['is_default'] = false;
            }

            $warehouse = Warehouse::create($validated);

            // Inisialisasi stok awal untuk semua produk ke gudang baru ini
            $products = Product::all();
            foreach ($products as $product) {
                ProductStock::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'warehouse_id' => $warehouse->id,
                    ],
                    [
                        'quantity' => 0,
                        'reserved_qty' => 0,
                    ]
                );
            }

            DB::commit();

            return redirect()->route('warehouses.index')->with('success', 'Gudang / Cabang baru berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan gudang: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified warehouse in storage.
     */
    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $validated['is_active'] = $request->has('is_active') ? true : false;
            $isDefault = $request->has('is_default') ? true : false;

            if ($isDefault) {
                Warehouse::where('id', '!=', $warehouse->id)->update(['is_default' => false]);
                $validated['is_default'] = true;
            } else {
                // If this warehouse was default, don't allow unsetting unless another default exists
                if ($warehouse->is_default && Warehouse::count() > 1) {
                    $validated['is_default'] = true; // keep as default
                } else {
                    $validated['is_default'] = false;
                }
            }

            $warehouse->update($validated);

            DB::commit();

            return redirect()->route('warehouses.index')->with('success', 'Data gudang / cabang berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui gudang: ' . $e->getMessage());
        }
    }

    /**
     * Set warehouse as default.
     */
    public function setDefault(Warehouse $warehouse)
    {
        DB::transaction(function () use ($warehouse) {
            Warehouse::where('is_default', true)->update(['is_default' => false]);
            $warehouse->update(['is_default' => true, 'is_active' => true]);
        });

        return redirect()->route('warehouses.index')->with('success', "Gudang {$warehouse->name} berhasil dijadikan gudang utama default.");
    }

    /**
     * Remove the specified warehouse from storage.
     */
    public function destroy(Warehouse $warehouse)
    {
        if ($warehouse->is_default) {
            return redirect()->route('warehouses.index')->with('error', 'Gudang default utama tidak dapat dihapus.');
        }

        $hasStock = $warehouse->stocks()->where('quantity', '>', 0)->exists();
        if ($hasStock) {
            return redirect()->route('warehouses.index')->with('error', 'Gudang tidak dapat dihapus karena masih memiliki persediaan stok produk.');
        }

        try {
            $warehouse->delete();
            return redirect()->route('warehouses.index')->with('success', 'Gudang berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('warehouses.index')->with('error', 'Gagal menghapus gudang: ' . $e->getMessage());
        }
    }
}
