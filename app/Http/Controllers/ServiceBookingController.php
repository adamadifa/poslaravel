<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ServiceBooking;
use App\Models\ServiceBookingItem;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceBookingController extends Controller
{
    /**
     * Display service bookings schedule & list.
     */
    public function index(Request $request)
    {
        $warehouseId = $request->get('warehouse_id', Warehouse::first()?->id);
        $date = $request->get('date', today()->toDateString());
        $status = $request->get('status');

        $warehouses = Warehouse::where('is_active', true)->get();
        $staffMembers = User::orderBy('name')->get();
        $serviceProducts = Product::where('product_type', 'service')->where('is_active', true)->get();
        $customers = Customer::where('is_active', true)->orderBy('name')->get();

        $bookings = ServiceBooking::with(['items.product', 'items.staff', 'customer', 'warehouse'])
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->when($date, fn ($q) => $q->whereDate('booking_date', $date))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderBy('booking_time')
            ->paginate(15)
            ->withQueryString();

        return view('service-bookings.index', compact(
            'bookings',
            'warehouses',
            'warehouseId',
            'staffMembers',
            'serviceProducts',
            'customers',
            'date',
            'status'
        ));
    }

    /**
     * Store a new service booking.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'customer_id' => 'nullable|exists:customers,id',
            'guest_name' => 'required|string|max:150',
            'guest_phone' => 'nullable|string|max:30',
            'booking_date' => 'required|date',
            'booking_time' => 'required',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.staff_user_id' => 'nullable|exists:users,id',
        ]);

        $bookingNumber = 'BK-'.date('Ymd').'-'.strtoupper(Str::random(4));

        $totalDuration = 0;
        foreach ($validated['items'] as $it) {
            $product = Product::find($it['product_id']);
            $totalDuration += ($product?->duration_minutes ?? 30);
        }

        $booking = ServiceBooking::create([
            'booking_number' => $bookingNumber,
            'warehouse_id' => $validated['warehouse_id'],
            'customer_id' => $validated['customer_id'] ?? null,
            'guest_name' => $validated['guest_name'],
            'guest_phone' => $validated['guest_phone'] ?? null,
            'booking_date' => $validated['booking_date'],
            'booking_time' => $validated['booking_time'],
            'estimated_duration_minutes' => $totalDuration,
            'status' => 'confirmed',
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        foreach ($validated['items'] as $item) {
            $prod = Product::find($item['product_id']);
            ServiceBookingItem::create([
                'service_booking_id' => $booking->id,
                'product_id' => $item['product_id'],
                'staff_user_id' => $item['staff_user_id'] ?? null,
                'quantity' => 1,
                'unit_price' => $prod?->selling_price ?? 0,
            ]);
        }

        return redirect()->back()->with('success', 'Booking layanan berhasil dibuat dengan No. '.$bookingNumber);
    }

    /**
     * Update booking status (confirmed, in_progress, completed, cancelled).
     */
    public function updateStatus(Request $request, ServiceBooking $booking)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,in_progress,completed,cancelled,no_show',
        ]);

        $booking->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', 'Status booking berhasil diperbarui.');
    }
}
