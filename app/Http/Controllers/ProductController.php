<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $categoryId = $request->query('category_id');

        $products = Product::with(['category', 'baseUnit', 'stocks'])
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('barcode', 'like', "%{$search}%");
            })
            ->when($categoryId, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $categories = Category::where('is_active', true)->get();
        $units = Unit::where('is_active', true)->get();

        return view('products.index', [
            'title' => 'Master Produk',
            'headerTitle' => 'Master Produk & Katalog',
            'products' => $products,
            'categories' => $categories,
            'units' => $units,
            'search' => $search,
            'categoryId' => $categoryId,
        ]);
    }
}
