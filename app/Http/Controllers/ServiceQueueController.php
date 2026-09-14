<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\ServiceService;
use Illuminate\Http\Request;

class ServiceQueueController extends Controller
{
    public function __construct(
        protected ServiceService $serviceService
    ) {}

    /**
     * Display service queue dashboard (Waiting, In Progress, Completed).
     */
    public function index(Request $request)
    {
        $warehouseId = $request->get('warehouse_id', Warehouse::first()?->id);
        $warehouses = Warehouse::where('is_active', true)->get();
        $staffMembers = User::orderBy('name')->get();

        $today = today()->toDateString();

        $waitingOrders = Sale::with(['items.product', 'items.assignment.staff', 'assignedStaff', 'customer'])
            ->where('warehouse_id', $warehouseId)
            ->whereDate('sale_date', $today)
            ->where('service_status', 'waiting')
            ->orderBy('created_at', 'asc')
            ->get();

        $inProgressOrders = Sale::with(['items.product', 'items.assignment.staff', 'assignedStaff', 'customer'])
            ->where('warehouse_id', $warehouseId)
            ->whereDate('sale_date', $today)
            ->where('service_status', 'in_progress')
            ->orderBy('service_started_at', 'asc')
            ->get();

        $completedOrders = Sale::with(['items.product', 'items.assignment.staff', 'assignedStaff', 'customer'])
            ->where('warehouse_id', $warehouseId)
            ->whereDate('sale_date', $today)
            ->where('service_status', 'completed')
            ->latest('service_completed_at')
            ->limit(10)
            ->get();

        return view('service-queue.index', compact(
            'waitingOrders',
            'inProgressOrders',
            'completedOrders',
            'warehouses',
            'warehouseId',
            'staffMembers'
        ));
    }

    /**
     * Start servicing an order.
     */
    public function start(Request $request, Sale $sale)
    {
        $staffId = $request->input('staff_id');
        $this->serviceService->startService($sale->id, $staffId);

        return redirect()->back()->with('success', 'Pengerjaan layanan telah dimulai.');
    }

    /**
     * Complete an order.
     */
    public function complete(Sale $sale)
    {
        $this->serviceService->completeService($sale->id);

        return redirect()->back()->with('success', 'Pengerjaan layanan selesai dan komisi telah dihitung.');
    }
}
