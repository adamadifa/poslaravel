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
        $staffMembers = User::where('is_active', true)->orderBy('name')->get();
        $isKiosk = $request->has('kiosk') || $request->get('mode') === 'kiosk';

        $today = today()->toDateString();

        $waitingOrders = Sale::with(['items.product', 'items.assignment.staff', 'assignedStaff', 'customer', 'diningTable'])
            ->where('warehouse_id', $warehouseId)
            ->where('service_status', 'waiting')
            ->orderBy('created_at', 'asc')
            ->get();

        $inProgressOrders = Sale::with(['items.product', 'items.assignment.staff', 'assignedStaff', 'customer', 'diningTable'])
            ->where('warehouse_id', $warehouseId)
            ->where('service_status', 'in_progress')
            ->orderBy('service_started_at', 'asc')
            ->get();

        $completedOrders = Sale::with(['items.product', 'items.assignment.staff', 'assignedStaff', 'customer', 'diningTable'])
            ->where('warehouse_id', $warehouseId)
            ->where('service_status', 'completed')
            ->whereDate('service_completed_at', $today)
            ->latest('service_completed_at')
            ->limit(15)
            ->get();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'waiting' => $waitingOrders,
                'inProgress' => $inProgressOrders,
                'completed' => $completedOrders,
                'counts' => [
                    'waiting' => $waitingOrders->count(),
                    'inProgress' => $inProgressOrders->count(),
                    'completed' => $completedOrders->count(),
                ],
            ]);
        }

        return view('service-queue.index', compact(
            'waitingOrders',
            'inProgressOrders',
            'completedOrders',
            'warehouses',
            'warehouseId',
            'staffMembers',
            'isKiosk'
        ));
    }

    /**
     * Start servicing an order.
     */
    public function start(Request $request, Sale $sale)
    {
        $staffId = $request->input('staff_id');
        $result = $this->serviceService->startService($sale->id, $staffId ? (int) $staffId : null);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => $result,
                'message' => $result ? 'Pengerjaan layanan telah dimulai.' : 'Gagal memulai layanan.',
            ]);
        }

        return redirect()->back()->with('success', 'Pengerjaan layanan telah dimulai.');
    }

    /**
     * Complete an order.
     */
    public function complete(Request $request, Sale $sale)
    {
        $result = $this->serviceService->completeService($sale->id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => $result,
                'message' => $result ? 'Pengerjaan layanan selesai dan komisi telah dihitung.' : 'Gagal menyelesaikan layanan.',
            ]);
        }

        return redirect()->back()->with('success', 'Pengerjaan layanan selesai dan komisi telah dihitung.');
    }
}
