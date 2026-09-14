<?php

namespace App\Http\Controllers;

use App\Models\DiningTable;
use App\Models\TableReservation;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class DiningTableController extends Controller
{
    /**
     * Display dining tables list and floor plan layout.
     */
    public function index(Request $request)
    {
        $warehouseId = $request->get('warehouse_id', Warehouse::first()?->id);
        $selectedArea = $request->get('area');

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        $query = DiningTable::with(['warehouse', 'currentSale.customer', 'reservations' => function ($q) {
            $q->where('reservation_date', today())->whereIn('status', ['pending', 'confirmed']);
        }])
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->when($selectedArea, fn ($q) => $q->where('area', $selectedArea))
            ->orderBy('sort_order')
            ->orderBy('table_number');

        $tables = $query->get();

        // Get unique areas for filter
        $areas = DiningTable::when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->distinct()
            ->pluck('area')
            ->filter()
            ->values();

        // Statistics
        $totalTables = $tables->count();
        $availableCount = $tables->where('status', 'available')->count();
        $occupiedCount = $tables->where('status', 'occupied')->count();
        $reservedCount = $tables->where('status', 'reserved')->count();

        return view('dining-tables.index', compact(
            'tables',
            'warehouses',
            'warehouseId',
            'areas',
            'selectedArea',
            'totalTables',
            'availableCount',
            'occupiedCount',
            'reservedCount'
        ));
    }

    /**
     * Store a newly created dining table.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'table_number' => 'required|string|max:50',
            'area' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1|max:50',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['status'] = 'available';
        $validated['is_active'] = true;

        DiningTable::create($validated);

        return redirect()->back()->with('success', 'Meja baru berhasil ditambahkan.');
    }

    /**
     * Update dining table details.
     */
    public function update(Request $request, DiningTable $table)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'table_number' => 'required|string|max:50',
            'area' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1|max:50',
            'status' => 'required|in:available,occupied,reserved,maintenance',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $table->update($validated);

        return redirect()->back()->with('success', 'Data meja berhasil diperbarui.');
    }

    /**
     * Delete dining table.
     */
    public function destroy(DiningTable $table)
    {
        if ($table->status === 'occupied') {
            return redirect()->back()->with('error', 'Meja sedang terisi transaksi aktif dan tidak dapat dihapus.');
        }

        $table->delete();

        return redirect()->back()->with('success', 'Meja berhasil dihapus.');
    }

    /**
     * Update table status manually (e.g. set maintenance or free up).
     */
    public function updateStatus(Request $request, DiningTable $table)
    {
        $validated = $request->validate([
            'status' => 'required|in:available,occupied,reserved,maintenance',
        ]);

        if ($validated['status'] === 'available') {
            $table->current_sale_id = null;
        }

        $table->status = $validated['status'];
        $table->save();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'table' => $table]);
        }

        return redirect()->back()->with('success', 'Status meja berhasil diubah.');
    }

    /**
     * List Table Reservations.
     */
    public function reservations(Request $request)
    {
        $warehouseId = $request->get('warehouse_id', Warehouse::first()?->id);
        $date = $request->get('date', today()->toDateString());
        $status = $request->get('status');

        $warehouses = Warehouse::where('is_active', true)->get();
        $tables = DiningTable::when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->where('is_active', true)
            ->get();

        $reservations = TableReservation::with(['diningTable', 'customer', 'creator'])
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->when($date, fn ($q) => $q->whereDate('reservation_date', $date))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderBy('reservation_time')
            ->paginate(15)
            ->withQueryString();

        return view('dining-tables.reservations', compact('reservations', 'warehouses', 'warehouseId', 'tables', 'date', 'status'));
    }

    /**
     * Store a new reservation.
     */
    public function storeReservation(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'dining_table_id' => 'required|exists:dining_tables,id',
            'guest_name' => 'required|string|max:150',
            'guest_phone' => 'nullable|string|max:30',
            'guest_count' => 'required|integer|min:1',
            'reservation_date' => 'required|date',
            'reservation_time' => 'required',
            'duration_minutes' => 'nullable|integer|min:30',
            'notes' => 'nullable|string|max:500',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['status'] = 'confirmed';

        $reservation = TableReservation::create($validated);

        // Optionally update table status if date is today
        if ($reservation->reservation_date->isToday()) {
            $table = DiningTable::find($validated['dining_table_id']);
            if ($table && $table->status === 'available') {
                $table->update(['status' => 'reserved']);
            }
        }

        return redirect()->back()->with('success', 'Reservasi meja berhasil dicatat.');
    }

    /**
     * Update reservation status.
     */
    public function updateReservationStatus(Request $request, TableReservation $reservation)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,seated,completed,cancelled,no_show',
        ]);

        $reservation->update(['status' => $validated['status']]);

        // If seated, mark table as occupied
        if ($validated['status'] === 'seated') {
            $reservation->diningTable?->update(['status' => 'occupied']);
        } elseif (in_array($validated['status'], ['completed', 'cancelled', 'no_show'])) {
            $table = $reservation->diningTable;
            if ($table && $table->status === 'reserved') {
                $table->update(['status' => 'available']);
            }
        }

        return redirect()->back()->with('success', 'Status reservasi berhasil diperbarui.');
    }
}
