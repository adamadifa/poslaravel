<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Warehouse;
use App\Services\OrderService;
use Illuminate\Http\Request;

class KitchenController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Display Kitchen Display System (KDS) full-screen dashboard.
     */
    public function index(Request $request)
    {
        $warehouseId = $request->get('warehouse_id', Warehouse::first()?->id);
        $warehouses = Warehouse::where('is_active', true)->get();
        $isKiosk = $request->has('kiosk') || $request->get('mode') === 'kiosk';

        $activeOrders = Sale::with([
            'items' => function ($q) {
                $q->whereHas('product', function ($pq) {
                    $pq->where(function ($sq) {
                        $sq->where('send_to_kitchen', true)
                            ->orWhere(function ($cq) {
                                $cq->whereNull('send_to_kitchen')
                                    ->whereHas('category', function ($catq) {
                                        $catq->where('send_to_kitchen', true);
                                    });
                            });
                    });
                })->with(['product.category', 'modifiers']);
            },
            'diningTable',
            'waiter',
            'customer',
        ])
            ->where('warehouse_id', $warehouseId)
            ->whereIn('order_status', ['new_order', 'preparing', 'ready'])
            ->whereHas('items.product', function ($pq) {
                $pq->where(function ($sq) {
                    $sq->where('send_to_kitchen', true)
                        ->orWhere(function ($cq) {
                            $cq->whereNull('send_to_kitchen')
                                ->whereHas('category', function ($catq) {
                                    $catq->where('send_to_kitchen', true);
                                });
                        });
                });
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return view('kitchen.index', compact('activeOrders', 'warehouses', 'warehouseId', 'isKiosk'));
    }

    /**
     * JSON endpoint for real-time KDS auto-polling.
     */
    public function getActiveOrders(Request $request)
    {
        $warehouseId = $request->get('warehouse_id', Warehouse::first()?->id);

        $orders = Sale::with([
            'items' => function ($q) {
                $q->whereHas('product', function ($pq) {
                    $pq->where(function ($sq) {
                        $sq->where('send_to_kitchen', true)
                            ->orWhere(function ($cq) {
                                $cq->whereNull('send_to_kitchen')
                                    ->whereHas('category', function ($catq) {
                                        $catq->where('send_to_kitchen', true);
                                    });
                            });
                    });
                })->with(['product.category', 'modifiers']);
            },
            'diningTable',
            'customer',
        ])
            ->where('warehouse_id', $warehouseId)
            ->whereIn('order_status', ['new_order', 'preparing', 'ready'])
            ->whereHas('items.product', function ($pq) {
                $pq->where(function ($sq) {
                    $sq->where('send_to_kitchen', true)
                        ->orWhere(function ($cq) {
                            $cq->whereNull('send_to_kitchen')
                                ->whereHas('category', function ($catq) {
                                    $catq->where('send_to_kitchen', true);
                                });
                        });
                });
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'count' => $orders->count(),
            'orders' => $orders,
        ]);
    }

    /**
     * Update individual item status (pending -> preparing -> ready -> served).
     */
    public function updateItemStatus(Request $request, SaleItem $saleItem)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,preparing,ready,served',
        ]);

        $this->orderService->updateItemStatus($saleItem->id, $validated['status']);

        return response()->json(['success' => true, 'message' => 'Status item berhasil diubah']);
    }

    /**
     * Mark entire order ready.
     */
    public function markOrderReady(Sale $sale)
    {
        $this->orderService->markOrderReady($sale->id);

        return response()->json(['success' => true, 'message' => 'Order berhasil ditandai Siap Saji']);
    }

    /**
     * Bump order (mark served).
     */
    public function bumpOrder(Sale $sale)
    {
        $this->orderService->bumpOrder($sale->id);

        return response()->json(['success' => true, 'message' => 'Order selesai / disajikan']);
    }
}
