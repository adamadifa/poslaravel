<?php

namespace App\Http\Controllers;

use App\Models\Modifier;
use App\Models\ModifierGroup;
use App\Models\Product;
use Illuminate\Http\Request;

class ModifierController extends Controller
{
    /**
     * Display modifier groups and their options.
     */
    public function index()
    {
        $groups = ModifierGroup::with(['modifiers', 'products'])->orderBy('sort_order')->get();
        $products = Product::whereIn('product_type', ['food', 'beverage', 'standard'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('modifiers.index', compact('groups', 'products'));
    }

    /**
     * Store a newly created modifier group.
     */
    public function storeGroup(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'selection_type' => 'required|in:single,multiple',
            'is_required' => 'nullable|boolean',
            'min_selections' => 'nullable|integer|min:0',
            'max_selections' => 'nullable|integer|min:1',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['is_required'] = $request->has('is_required');
        $validated['is_active'] = true;

        ModifierGroup::create($validated);

        return redirect()->back()->with('success', 'Grup modifier berhasil dibuat.');
    }

    /**
     * Update modifier group.
     */
    public function updateGroup(Request $request, ModifierGroup $modifierGroup)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'selection_type' => 'required|in:single,multiple',
            'is_required' => 'nullable|boolean',
            'min_selections' => 'nullable|integer|min:0',
            'max_selections' => 'nullable|integer|min:1',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_required'] = $request->has('is_required');
        $validated['is_active'] = $request->has('is_active');

        $modifierGroup->update($validated);

        return redirect()->back()->with('success', 'Grup modifier berhasil diperbarui.');
    }

    /**
     * Delete modifier group.
     */
    public function destroyGroup(ModifierGroup $modifierGroup)
    {
        $modifierGroup->delete();

        return redirect()->back()->with('success', 'Grup modifier berhasil dihapus.');
    }

    /**
     * Store a modifier option under a group.
     */
    public function storeModifier(Request $request)
    {
        $validated = $request->validate([
            'modifier_group_id' => 'required|exists:modifier_groups,id',
            'name' => 'required|string|max:100',
            'price_adjustment' => 'required|numeric|min:0',
            'is_default' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['is_default'] = $request->has('is_default');
        $validated['is_active'] = true;

        Modifier::create($validated);

        return redirect()->back()->with('success', 'Opsi modifier berhasil ditambahkan.');
    }

    /**
     * Update modifier option.
     */
    public function updateModifier(Request $request, Modifier $modifier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'price_adjustment' => 'required|numeric|min:0',
            'is_default' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_default'] = $request->has('is_default');
        $validated['is_active'] = $request->has('is_active');

        $modifier->update($validated);

        return redirect()->back()->with('success', 'Opsi modifier berhasil diperbarui.');
    }

    /**
     * Delete modifier option.
     */
    public function destroyModifier(Modifier $modifier)
    {
        $modifier->delete();

        return redirect()->back()->with('success', 'Opsi modifier berhasil dihapus.');
    }

    /**
     * Attach/Sync products to modifier group.
     */
    public function syncProducts(Request $request, ModifierGroup $modifierGroup)
    {
        $validated = $request->validate([
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
        ]);

        $modifierGroup->products()->sync($validated['product_ids'] ?? []);

        return redirect()->back()->with('success', 'Produk terkait modifier berhasil disinkronkan.');
    }
}
