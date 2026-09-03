<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDiscountRequest;
use App\Http\Requests\UpdateDiscountRequest;
use App\Models\CustomerGroup;
use App\Models\Discount;
use App\Models\DiscountItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DiscountController extends Controller
{
    /**
     * Display a listing of discounts and promotional campaigns.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $type = $request->query('type');
        $status = $request->query('status');

        $discounts = Discount::with(['customerGroup', 'rewardProduct', 'items.product'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->when($status !== null && $status !== '', function ($query) use ($status) {
                $query->where('is_active', $status == '1');
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $products = Product::where('is_active', true)->orderBy('name')->get();
        $customerGroups = CustomerGroup::orderBy('name')->get();

        return view('discounts.index', [
            'title' => 'Promo & Discount Engine',
            'headerTitle' => 'Promo & Discount Engine',
            'headerDescription' => 'Atur promosi harga bertingkat, diskon item, voucher invoice kasir, dan promo Buy X Get Y.',
            'breadcrumbParent' => 'Operasional',
            'breadcrumbCurrent' => 'Diskon & Promo',
            'discounts' => $discounts,
            'products' => $products,
            'customerGroups' => $customerGroups,
            'search' => $search,
            'type' => $type,
            'status' => $status,
        ]);
    }

    /**
     * Store a newly created discount in storage.
     */
    public function store(StoreDiscountRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $validated['is_active'] = $request->has('is_active') ? true : false;
            $validated['is_combinable'] = $request->has('is_combinable') ? true : false;
            $validated['value'] = $validated['value'] ?? 0;

            $discount = Discount::create($validated);

            // Sync Selected Products for Item-based discounts or Buy X Get Y
            if (!empty($request->input('product_ids'))) {
                foreach ($request->input('product_ids') as $pId) {
                    DiscountItem::create([
                        'discount_id' => $discount->id,
                        'product_id' => $pId,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('discounts.index')->with('success', 'Promo diskon berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan promo: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified discount in storage.
     */
    public function update(UpdateDiscountRequest $request, Discount $discount)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $validated['is_active'] = $request->has('is_active') ? true : false;
            $validated['is_combinable'] = $request->has('is_combinable') ? true : false;
            $validated['value'] = $validated['value'] ?? 0;

            $discount->update($validated);

            // Sync Selected Products
            $discount->items()->delete();
            if (!empty($request->input('product_ids'))) {
                foreach ($request->input('product_ids') as $pId) {
                    DiscountItem::create([
                        'discount_id' => $discount->id,
                        'product_id' => $pId,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('discounts.index')->with('success', 'Promo diskon berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui promo: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified discount from storage.
     */
    public function destroy(Discount $discount)
    {
        try {
            $discount->delete();
            return redirect()->route('discounts.index')->with('success', 'Promo diskon berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('discounts.index')->with('error', 'Gagal menghapus promo: ' . $e->getMessage());
        }
    }
}
