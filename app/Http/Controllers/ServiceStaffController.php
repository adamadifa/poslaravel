<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ServiceStaff;
use App\Models\User;
use Illuminate\Http\Request;

class ServiceStaffController extends Controller
{
    /**
     * Display service staff matrix & commission settings.
     */
    public function index()
    {
        $staffMembers = User::orderBy('name')->get();
        $serviceProducts = Product::where('product_type', 'service')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $assignments = ServiceStaff::with(['user', 'product'])->get();

        return view('service-staff.index', compact('staffMembers', 'serviceProducts', 'assignments'));
    }

    /**
     * Store a service staff capability & commission.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'commission_type' => 'required|in:none,fixed,percent',
            'commission_value' => 'required|numeric|min:0',
        ]);

        $validated['is_active'] = true;

        ServiceStaff::updateOrCreate(
            ['user_id' => $validated['user_id'], 'product_id' => $validated['product_id']],
            $validated
        );

        return redirect()->back()->with('success', 'Penugasan staff layanan & komisi berhasil disimpan.');
    }

    /**
     * Delete service staff assignment.
     */
    public function destroy(ServiceStaff $serviceStaff)
    {
        $serviceStaff->delete();

        return redirect()->back()->with('success', 'Penugasan staff layanan berhasil dihapus.');
    }
}
