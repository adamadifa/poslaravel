<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\PpobProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PpobProductController extends Controller
{
    /**
     * Display listing of PPOB products in Back Office.
     */
    public function index(Request $request): View
    {
        $category = $request->query('category');
        $provider = $request->query('provider');
        $search = $request->query('search');

        $products = PpobProduct::with('defaultAccount')
            ->when($category, fn ($q) => $q->where('category', $category))
            ->when($provider, fn ($q) => $q->where('provider', $provider))
            ->when($search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('provider', 'like', "%{$search}%");
                });
            })
            ->orderBy('category')
            ->orderBy('provider')
            ->orderBy('selling_price')
            ->paginate(20)
            ->withQueryString();

        $accounts = Account::where('is_active', true)->orderBy('name')->get();
        $providers = PpobProduct::select('provider')->whereNotNull('provider')->distinct()->pluck('provider');

        $totalActive = PpobProduct::where('is_active', true)->count();
        $totalPulsa = PpobProduct::where('category', 'pulsa')->count();
        $totalTokenPln = PpobProduct::where('category', 'token_pln')->count();
        $totalEwallet = PpobProduct::where('category', 'ewallet')->count();

        return view('finance.ppob_products.index', [
            'title' => 'Katalog Produk PPOB & Pulsa',
            'headerTitle' => 'Katalog Produk PPOB & Pulsa',
            'headerDescription' => 'Kelola harga modal (HPP), harga jual kasir, dan margin keuntungan produk digital & pulsa toko.',
            'breadcrumbParent' => 'Keuangan & Finansial',
            'breadcrumbCurrent' => 'Katalog Produk PPOB',
            'products' => $products,
            'accounts' => $accounts,
            'providers' => $providers,
            'totalActive' => $totalActive,
            'totalPulsa' => $totalPulsa,
            'totalTokenPln' => $totalTokenPln,
            'totalEwallet' => $totalEwallet,
            'category' => $category,
            'provider' => $provider,
            'search' => $search,
        ]);
    }

    /**
     * Store newly created PPOB product.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'category' => 'required|string|in:pulsa,paket_data,token_pln,ewallet,tagihan,other',
            'provider' => 'nullable|string|max:100',
            'code' => 'nullable|string|max:50|unique:ppob_products,code',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'default_account_id' => 'nullable|exists:accounts,id',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');

        if (empty($validated['code'])) {
            $validated['code'] = 'PPOB-'.strtoupper(substr($validated['category'], 0, 3)).'-'.rand(1000, 9999);
        }

        PpobProduct::create($validated);

        return redirect()->route('ppob-products.index')->with('success', "Produk PPOB '{$validated['name']}' berhasil ditambahkan.");
    }

    /**
     * Show PPOB product detail via AJAX.
     */
    public function show(PpobProduct $ppobProduct): JsonResponse
    {
        return response()->json($ppobProduct);
    }

    /**
     * Update existing PPOB product.
     */
    public function update(Request $request, PpobProduct $ppobProduct): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'category' => 'required|string|in:pulsa,paket_data,token_pln,ewallet,tagihan,other',
            'provider' => 'nullable|string|max:100',
            'code' => "nullable|string|max:50|unique:ppob_products,code,{$ppobProduct->id}",
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'default_account_id' => 'nullable|exists:accounts,id',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $ppobProduct->update($validated);

        return redirect()->route('ppob-products.index')->with('success', "Produk PPOB '{$ppobProduct->name}' berhasil diperbarui.");
    }

    /**
     * Delete PPOB product.
     */
    public function destroy(PpobProduct $ppobProduct): RedirectResponse
    {
        $name = $ppobProduct->name;
        $ppobProduct->delete();

        return redirect()->route('ppob-products.index')->with('success', "Produk PPOB '{$name}' berhasil dihapus.");
    }

    /**
     * JSON API for POS cashier auto-fill picker.
     */
    public function apiList(): JsonResponse
    {
        $products = PpobProduct::where('is_active', true)
            ->orderBy('category')
            ->orderBy('provider')
            ->orderBy('selling_price')
            ->get([
                'id',
                'category',
                'provider',
                'code',
                'name',
                'cost_price',
                'selling_price',
                'default_account_id',
            ]);

        return response()->json([
            'success' => true,
            'products' => $products,
        ]);
    }
}
