<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display a listing of the suppliers.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $suppliers = Supplier::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('contact_person', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('city', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('suppliers.index', [
            'title' => 'Master Pemasok',
            'headerTitle' => 'Master Pemasok (Supplier)',
            'headerDescription' => 'Kelola daftar rekanan supplier, kontak PIC, termin tempo pembayaran, dan data alamat vendor.',
            'breadcrumbParent' => 'Master Data',
            'breadcrumbCurrent' => 'Pemasok (Supplier)',
            'suppliers' => $suppliers,
            'search' => $search,
        ]);
    }

    /**
     * Store a newly created supplier in storage.
     */
    public function store(StoreSupplierRequest $request)
    {
        $validated = $request->validated();

        try {
            // Generate auto code if empty
            if (empty($validated['code'])) {
                $count = Supplier::count() + 1;
                $validated['code'] = 'SUP-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }

            $validated['is_active'] = $request->has('is_active') ? true : false;
            $validated['payment_term_days'] = $validated['payment_term_days'] ?? 0;

            Supplier::create($validated);

            return redirect()->route('suppliers.index')->with('success', 'Pemasok berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan pemasok: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified supplier in storage.
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        $validated = $request->validated();

        try {
            $validated['is_active'] = $request->has('is_active') ? true : false;
            $validated['payment_term_days'] = $validated['payment_term_days'] ?? 0;

            $supplier->update($validated);

            return redirect()->route('suppliers.index')->with('success', 'Pemasok berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui pemasok: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified supplier from storage.
     */
    public function destroy(Supplier $supplier)
    {
        try {
            $supplier->delete();
            return redirect()->route('suppliers.index')->with('success', 'Pemasok berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('suppliers.index')->with('error', 'Gagal menghapus pemasok: ' . $e->getMessage());
        }
    }
}
