<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\ConsignmentSettlement;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\ConsignmentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ConsignmentSettlementController extends Controller
{
    protected ConsignmentService $consignmentService;

    public function __construct(ConsignmentService $consignmentService)
    {
        $this->consignmentService = $consignmentService;
    }

    /**
     * Display a listing of Consignment Settlements.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $supplierId = $request->query('supplier_id');
        $paymentStatus = $request->query('payment_status');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $settlements = ConsignmentSettlement::with(['supplier', 'warehouse', 'paymentAccount', 'items.product', 'items.unit'])
            ->when($search, function ($query, $search) {
                $query->where('settlement_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
            ->when($paymentStatus, fn ($q) => $q->where('payment_status', $paymentStatus))
            ->when($startDate, fn ($q) => $q->whereDate('start_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('end_date', '<=', $endDate))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        $accounts = Account::where('is_active', true)->orderBy('name')->get();

        // Summary KPI Metrics
        $totalUnpaidAmount = ConsignmentSettlement::where('payment_status', 'unpaid')->sum('total_supplier_amount');
        $totalPaidAmount = ConsignmentSettlement::where('payment_status', 'paid')->sum('paid_amount');
        $totalCommissionEarned = ConsignmentSettlement::sum('total_store_commission');

        return view('consignments.settlements.index', [
            'title' => 'Settlement Konsinyasi',
            'headerTitle' => 'Rekonsiliasi & Settlement Konsinyasi',
            'headerDescription' => 'Hitung bagi hasil penjualan barang titipan, buat faktur pembayaran supplier, dan lacak keuntungan komisi toko.',
            'breadcrumbParent' => 'Konsinyasi',
            'breadcrumbCurrent' => 'Settlement & Bagi Hasil',
            'settlements' => $settlements,
            'suppliers' => $suppliers,
            'warehouses' => $warehouses,
            'accounts' => $accounts,
            'totalUnpaidAmount' => $totalUnpaidAmount,
            'totalPaidAmount' => $totalPaidAmount,
            'totalCommissionEarned' => $totalCommissionEarned,
            'search' => $search,
            'supplierId' => $supplierId,
            'paymentStatus' => $paymentStatus,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    /**
     * AJAX Preview / Calculate unsettled sales for a supplier.
     */
    public function calculate(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'warehouse_id' => 'nullable|exists:warehouses,id',
        ]);

        try {
            $calculation = $this->consignmentService->calculateUnsettledSales(
                (int) $request->input('supplier_id'),
                $request->input('start_date'),
                $request->input('end_date'),
                $request->input('warehouse_id') ? (int) $request->input('warehouse_id') : null
            );

            return response()->json([
                'status' => 'success',
                'data' => $calculation,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Store a newly created Consignment Settlement.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'pay_now' => 'nullable|boolean',
            'payment_account_id' => 'nullable|required_if:pay_now,1,true|exists:accounts,id',
            'payment_method' => 'nullable|in:cash,transfer,qris,check',
            'payment_date' => 'nullable|date',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $settlement = $this->consignmentService->createSettlement($validated);

            return redirect()->route('consignments.settlements.index')->with('success', "Settlement {$settlement->settlement_number} berhasil dibuat.");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal membuat settlement: '.$e->getMessage());
        }
    }

    /**
     * Show settlement details in JSON.
     */
    public function show(ConsignmentSettlement $settlement)
    {
        $settlement->load(['supplier', 'warehouse', 'user', 'paymentAccount', 'items.product', 'items.unit']);

        return response()->json([
            'status' => 'success',
            'data' => $settlement,
        ]);
    }

    /**
     * Process payment for an unpaid settlement.
     */
    public function pay(Request $request, ConsignmentSettlement $settlement)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'payment_method' => 'required|in:cash,transfer,qris,check',
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $this->consignmentService->paySettlement($settlement, $validated);

            return redirect()->route('consignments.settlements.index')->with('success', "Pembayaran settlement {$settlement->settlement_number} sebesar Rp ".number_format($settlement->total_supplier_amount, 0, ',', '.').' berhasil diproses.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses pembayaran: '.$e->getMessage());
        }
    }

    /**
     * Print settlement statement / invoice.
     */
    public function print(ConsignmentSettlement $settlement)
    {
        $settlement->load(['supplier', 'warehouse', 'user', 'paymentAccount', 'items.product', 'items.unit']);
        $storeName = Setting::get('store_name', config('app.name', 'POS Retail Pro'));
        $storeAddress = Setting::get('store_address', 'Jl. Jenderal Sudirman No. 123');
        $storePhone = Setting::get('store_phone', '0812-3456-7890');

        return view('consignments.settlements.print', [
            'settlement' => $settlement,
            'storeName' => $storeName,
            'storeAddress' => $storeAddress,
            'storePhone' => $storePhone,
        ]);
    }

    /**
     * Export Settlement Statement to PDF.
     */
    public function exportPdf(ConsignmentSettlement $settlement)
    {
        $settlement->load(['supplier', 'warehouse', 'user', 'paymentAccount', 'items.product', 'items.unit']);
        $storeName = Setting::get('store_name', config('app.name', 'POS Retail Pro'));
        $storeAddress = Setting::get('store_address', 'Jl. Jenderal Sudirman No. 123');
        $storePhone = Setting::get('store_phone', '0812-3456-7890');

        $pdf = Pdf::loadView('consignments.settlements.pdf', [
            'settlement' => $settlement,
            'storeName' => $storeName,
            'storeAddress' => $storeAddress,
            'storePhone' => $storePhone,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("Settlement-{$settlement->settlement_number}.pdf");
    }
}
