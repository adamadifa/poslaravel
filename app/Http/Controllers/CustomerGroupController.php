<?php

namespace App\Http\Controllers;

use App\Models\CustomerGroup;
use Illuminate\Http\Request;

class CustomerGroupController extends Controller
{
    /**
     * Display a listing of customer groups.
     */
    public function index()
    {
        $groups = CustomerGroup::withCount('customers')->get();

        return response()->json([
            'status' => 'success',
            'data' => $groups,
        ]);
    }

    /**
     * Store a newly created customer group.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:customer_groups,name'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'Nama grup member wajib diisi.',
            'name.unique' => 'Nama grup member sudah ada.',
            'discount_percent.numeric' => 'Persentase diskon harus berupa angka.',
            'discount_percent.max' => 'Diskon maksimal 100%.',
        ]);

        $group = CustomerGroup::create([
            'name' => $validated['name'],
            'discount_percent' => $validated['discount_percent'] ?? 0,
            'description' => $validated['description'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'data' => $group, 'message' => 'Grup berhasil ditambahkan.']);
        }

        return redirect()->route('customers.index')->with('success', 'Grup member baru berhasil ditambahkan.');
    }

    /**
     * Update the specified customer group.
     */
    public function update(Request $request, CustomerGroup $customerGroup)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:customer_groups,name,' . $customerGroup->id],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'Nama grup member wajib diisi.',
            'name.unique' => 'Nama grup member sudah ada.',
            'discount_percent.numeric' => 'Persentase diskon harus berupa angka.',
        ]);

        $customerGroup->update([
            'name' => $validated['name'],
            'discount_percent' => $validated['discount_percent'] ?? 0,
            'description' => $validated['description'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'data' => $customerGroup, 'message' => 'Grup berhasil diperbarui.']);
        }

        return redirect()->route('customers.index')->with('success', 'Grup member berhasil diperbarui.');
    }

    /**
     * Remove the specified customer group.
     */
    public function destroy(CustomerGroup $customerGroup)
    {
        if ($customerGroup->customers()->count() > 0) {
            return redirect()->route('customers.index')->with('error', 'Grup tidak dapat dihapus karena memiliki member aktif.');
        }

        $customerGroup->delete();
        return redirect()->route('customers.index')->with('success', 'Grup member berhasil dihapus.');
    }
}
