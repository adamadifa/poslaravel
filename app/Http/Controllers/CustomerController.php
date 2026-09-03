<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Models\CustomerGroup;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $groupId = $request->query('customer_group_id');

        $customers = Customer::with('group')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('city', 'like', "%{$search}%");
                });
            })
            ->when($groupId, function ($query, $groupId) {
                $query->where('customer_group_id', $groupId);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $customerGroups = CustomerGroup::orderBy('name')->get();

        return view('customers.index', [
            'title' => 'Master Pelanggan & Member',
            'headerTitle' => 'Master Pelanggan & Member',
            'headerDescription' => 'Kelola database customer, grup diskon member, poin loyalitas, dan batas kredit piutang.',
            'breadcrumbParent' => 'Master Data',
            'breadcrumbCurrent' => 'Pelanggan & Member',
            'customers' => $customers,
            'customerGroups' => $customerGroups,
            'search' => $search,
            'groupId' => $groupId,
        ]);
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        $validated = $request->validated();

        try {
            // Auto-generate Customer Code if blank
            if (empty($validated['code'])) {
                $count = Customer::count() + 1;
                $validated['code'] = 'CUST-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }

            $validated['is_active'] = $request->has('is_active') ? true : false;
            $validated['credit_limit'] = $validated['credit_limit'] ?? 0;
            $validated['loyalty_points'] = 0;

            $customer = Customer::create($validated);

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Pelanggan baru berhasil didaftarkan.',
                    'data' => $customer->load('group'),
                ]);
            }

            return redirect()->route('customers.index')->with('success', 'Pelanggan baru berhasil didaftarkan.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal menambahkan pelanggan: ' . $e->getMessage(),
                ], 422);
            }
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan pelanggan: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $validated = $request->validated();

        try {
            $validated['is_active'] = $request->has('is_active') ? true : false;
            $validated['credit_limit'] = $validated['credit_limit'] ?? 0;

            $customer->update($validated);

            return redirect()->route('customers.index')->with('success', 'Data pelanggan berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui pelanggan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified customer from storage.
     */
    public function destroy(Customer $customer)
    {
        try {
            $customer->delete();
            return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('customers.index')->with('error', 'Gagal menghapus pelanggan: ' . $e->getMessage());
        }
    }
}
