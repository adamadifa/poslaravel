<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    /**
     * Display a listing of sales transactions history.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->toDateString());
        $warehouseId = $request->query('warehouse_id');
        $userId = $request->query('user_id');
        $paymentMethod = $request->query('payment_method');
        $paymentStatus = $request->query('payment_status');
        $status = $request->query('status');

        $query = Sale::with(['user', 'customer', 'warehouse', 'items.product', 'items.unit'])
            ->when($search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('invoice_number', 'like', "%{$search}%")
                        ->orWhere('reference_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($c) use ($search) {
                            $c->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($startDate, function ($q, $startDate) {
                $q->whereDate('sale_date', '>=', $startDate);
            })
            ->when($endDate, function ($q, $endDate) {
                $q->whereDate('sale_date', '<=', $endDate);
            })
            ->when($warehouseId, function ($q, $warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            })
            ->when($userId, function ($q, $userId) {
                $q->where('user_id', $userId);
            })
            ->when($paymentMethod, function ($q, $paymentMethod) {
                $q->where('payment_method', $paymentMethod);
            })
            ->when($paymentStatus, function ($q, $paymentStatus) {
                $q->where('payment_status', $paymentStatus);
            })
            ->when($status, function ($q, $status) {
                $q->where('status', $status);
            });

        // Summary totals for current filter
        $totalSalesCount = (clone $query)->where('status', '!=', 'void')->count();
        $totalGrandTotal = (clone $query)->where('status', '!=', 'void')->sum('grand_total');
        $totalDiscountAmount = (clone $query)->where('status', '!=', 'void')->sum('discount_amount');
        $totalPaidAmount = (clone $query)->where('status', '!=', 'void')->sum('paid_amount');

        $sales = $query->latest('sale_date')->latest('id')->paginate(15)->withQueryString();

        $warehouses = Warehouse::where('is_active', true)->get();
        $users = User::orderBy('name')->get();
        $customers = Customer::orderBy('name')->get();

        return view('sales.index', [
            'title' => 'Riwayat Penjualan',
            'headerTitle' => 'Riwayat Transaksi Penjualan Kasir',
            'sales' => $sales,
            'warehouses' => $warehouses,
            'users' => $users,
            'customers' => $customers,
            'totalSalesCount' => $totalSalesCount,
            'totalGrandTotal' => $totalGrandTotal,
            'totalDiscountAmount' => $totalDiscountAmount,
            'totalPaidAmount' => $totalPaidAmount,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'warehouseId' => $warehouseId,
            'userId' => $userId,
            'paymentMethod' => $paymentMethod,
            'paymentStatus' => $paymentStatus,
            'status' => $status,
            'search' => $search,
        ]);
    }

    /**
     * Display the specified sale transaction details.
     */
    public function show(Sale $sale)
    {
        $sale->load(['user', 'customer', 'warehouse', 'items.product', 'items.unit']);

        if (request()->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $sale,
            ]);
        }

        return view('sales.show', [
            'title' => "Faktur {$sale->invoice_number}",
            'headerTitle' => "Detail Faktur {$sale->invoice_number}",
            'sale' => $sale,
        ]);
    }
}
