<?php

namespace App\Http\Controllers;

use App\Models\ConsignmentReceipt;
use App\Models\ConsignmentReturn;
use App\Models\ConsignmentSettlement;
use App\Models\Product;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsignmentReportController extends Controller
{
    /**
     * Display Consignment Analytics & Summary Dashboard.
     */
    public function index(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());
        $supplierId = $request->query('supplier_id');

        // 1. Total Received
        $totalReceivedQty = ConsignmentReceipt::whereDate('receipt_date', '>=', $startDate)
            ->whereDate('receipt_date', '<=', $endDate)
            ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
            ->sum('total_quantity');

        // 2. Total Returned
        $totalReturnedQty = ConsignmentReturn::whereDate('return_date', '>=', $startDate)
            ->whereDate('return_date', '<=', $endDate)
            ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
            ->sum('total_quantity');

        // 3. Total Sold
        $saleItemsQuery = SaleItem::with(['product', 'unit', 'consignmentSupplier', 'sale'])
            ->where('is_consignment', true)
            ->whereHas('sale', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'completed')
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
            })
            ->when($supplierId, fn ($q) => $q->where('consignment_supplier_id', $supplierId));

        $saleItems = $saleItemsQuery->get();

        $totalSoldQty = $saleItems->sum('quantity');
        $totalGrossSales = $saleItems->sum('subtotal');
        $totalSupplierPayable = $saleItems->sum(function ($item) {
            return (float) $item->quantity * (float) $item->consignment_cost;
        });
        $totalStoreCommission = max(0, $totalGrossSales - $totalSupplierPayable);

        // 4. Settlements Metrics
        $totalSettledAmount = ConsignmentSettlement::whereDate('start_date', '>=', $startDate)
            ->whereDate('end_date', '<=', $endDate)
            ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
            ->sum('total_supplier_amount');

        $totalUnpaidSettlement = ConsignmentSettlement::where('payment_status', 'unpaid')
            ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
            ->sum('total_supplier_amount');

        // 5. Breakdown by Supplier
        $supplierBreakdown = SaleItem::with('consignmentSupplier')
            ->where('is_consignment', true)
            ->whereHas('sale', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'completed')
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
            })
            ->select('consignment_supplier_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_gross'), DB::raw('SUM(quantity * consignment_cost) as total_payable'))
            ->groupBy('consignment_supplier_id')
            ->get();

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        // 6. Current Consignment Products & Stock
        $consignmentProducts = Product::with(['baseUnit', 'stocks', 'consignmentSupplier'])
            ->where('is_consignment', true)
            ->when($supplierId, fn ($q) => $q->where('consignment_supplier_id', $supplierId))
            ->get();

        return view('consignments.reports.index', [
            'title' => 'Laporan Konsinyasi',
            'headerTitle' => 'Laporan & Analitik Konsinyasi',
            'headerDescription' => 'Monitoring pergerakan barang titip jual, rekonsiliasi omzet, sisa stok penitipan, dan margin laba komisi toko.',
            'breadcrumbParent' => 'Konsinyasi',
            'breadcrumbCurrent' => 'Laporan Konsinyasi',
            'suppliers' => $suppliers,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'supplierId' => $supplierId,
            'totalReceivedQty' => $totalReceivedQty,
            'totalReturnedQty' => $totalReturnedQty,
            'totalSoldQty' => $totalSoldQty,
            'totalGrossSales' => $totalGrossSales,
            'totalSupplierPayable' => $totalSupplierPayable,
            'totalStoreCommission' => $totalStoreCommission,
            'totalSettledAmount' => $totalSettledAmount,
            'totalUnpaidSettlement' => $totalUnpaidSettlement,
            'supplierBreakdown' => $supplierBreakdown,
            'consignmentProducts' => $consignmentProducts,
            'saleItems' => $saleItems,
        ]);
    }

    /**
     * Export Consignment Report to PDF.
     */
    public function exportPdf(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());
        $supplierId = $request->query('supplier_id');

        $supplier = $supplierId ? Supplier::find($supplierId) : null;

        $saleItems = SaleItem::with(['product', 'unit', 'consignmentSupplier', 'sale'])
            ->where('is_consignment', true)
            ->whereHas('sale', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'completed')
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
            })
            ->when($supplierId, fn ($q) => $q->where('consignment_supplier_id', $supplierId))
            ->get();

        $storeName = Setting::get('store_name', config('app.name', 'POS Retail Pro'));
        $storeAddress = Setting::get('store_address', 'Jl. Jenderal Sudirman No. 123');

        $pdf = Pdf::loadView('consignments.reports.pdf', [
            'saleItems' => $saleItems,
            'supplier' => $supplier,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'storeName' => $storeName,
            'storeAddress' => $storeAddress,
        ])->setPaper('a4', 'landscape');

        return $pdf->download("Laporan-Konsinyasi-{$startDate}-sd-{$endDate}.pdf");
    }
}
