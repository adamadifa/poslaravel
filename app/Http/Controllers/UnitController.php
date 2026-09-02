<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    /**
     * Display a listing of units.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $units = Unit::withCount('products')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('short_name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('units.index', [
            'title' => 'Master Satuan',
            'headerTitle' => 'Master Satuan Ukuran',
            'units' => $units,
            'search' => $search,
        ]);
    }

    /**
     * Store a newly created unit.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:units,name'],
            'short_name' => ['required', 'string', 'max:15'],
        ]);

        Unit::create([
            'name' => $validated['name'],
            'short_name' => $validated['short_name'],
            'is_active' => true,
        ]);

        return redirect()->route('units.index')->with('success', 'Satuan berhasil ditambahkan.');
    }

    /**
     * Update the specified unit.
     */
    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:units,name,' . $unit->id],
            'short_name' => ['required', 'string', 'max:15'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $unit->update([
            'name' => $validated['name'],
            'short_name' => $validated['short_name'],
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('units.index')->with('success', 'Satuan berhasil diperbarui.');
    }

    /**
     * Remove the specified unit.
     */
    public function destroy(Unit $unit)
    {
        if ($unit->products()->count() > 0) {
            return back()->with('error', 'Satuan tidak dapat dihapus karena masih digunakan sebagai satuan dasar produk.');
        }

        $unit->delete();
        return redirect()->route('units.index')->with('success', 'Satuan berhasil dihapus.');
    }
}
