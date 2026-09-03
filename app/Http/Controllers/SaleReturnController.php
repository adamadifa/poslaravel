<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Warehouse;
use App\Services\SaleReturnService;
use Illuminate\Http\Request;

class SaleReturnController extends Controller
{
    protected SaleReturnService $returnService;

    public function __construct(SaleReturnService $returnService)
    {
        $this->returnService = $returnService;
    }

    /**
     * Display listing of Sale Returns.
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $search = $request->query('search');

        $returns = SaleReturn::with(['sale', 'customer', 'warehouse', 'account', 'creator', 'items.product.baseUnit'])
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($startDate, fn($q) => $q->whereDate('return_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('return_date', '<=', $endDate))
            ->when($search, function ($q, $search) {
                $q->where('return_number', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%")
                  ->orWhereHas('sale', fn($sq) => $sq->where('invoice_number', 'like', "%{$search}%"))
                  ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$search}%"));
            })
            ->latest('return_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $accounts = Account::where('is_active', true)->orderBy('name')->get();
        $products = \App\Models\Product::with('baseUnit')->where('is_active', true)->orderBy('name')->get();
        $totalRefund = SaleReturn::where('status', 'completed')->sum('refund_amount');

        return view('sales.returns.index', [
            'title' => 'Retur Penjualan',
            'headerTitle' => 'Retur Penjualan (Sales Return)',
            'headerDescription' => 'Kelola retur barang dari pelanggan, pengembalian dana (refund), dan pemulihan stok inventaris.',
            'breadcrumbParent' => 'Penjualan & Kasir',
            'breadcrumbCurrent' => 'Retur Penjualan',
            'returns' => $returns,
            'accounts' => $accounts,
            'products' => $products,
            'totalRefund' => $totalRefund,
            'status' => $status,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'search' => $search,
        ]);
    }

    /**
     * Search Invoice details via AJAX for creating Sale Return.
     */
    public function searchInvoice(Request $request)
    {
        $invoiceNumber = $request->query('invoice_number');
        $sale = Sale::with(['items.product.baseUnit', 'customer', 'warehouse'])
            ->where('invoice_number', $invoiceNumber)
            ->orWhere('id', $invoiceNumber)
            ->first();

        if (!$sale) {
            return response()->json(['error' => 'Invoice penjualan tidak ditemukan.'], 404);
        }

        return response()->json($sale);
    }

    /**
     * List completed sales with search for the Invoice Picker Modal.
     */
    public function listInvoices(Request $request)
    {
        $search = $request->query('search');

        $sales = Sale::with(['customer', 'warehouse', 'items.product.baseUnit', 'user'])
            ->where('status', 'completed')
            ->when($search, function ($q, $search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('invoice_number', 'like', "%{$search}%")
                       ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$search}%"))
                       ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('sale_date')
            ->latest('id')
            ->limit(20)
            ->get();

        return response()->json($sales);
    }

    /**
     * Store a newly created Sale Return.
     */
    public function store(Request $request)
    {
        // Filter out return items that have 0 or empty quantity
        if ($request->has('items') && is_array($request->input('items'))) {
            $filteredItems = array_values(array_filter($request->input('items'), function ($item) {
                return isset($item['quantity']) && (float)$item['quantity'] > 0;
            }));
            $request->merge(['items' => $filteredItems]);
        }

        // Filter out replacement items that have 0 or empty quantity
        if ($request->has('replacement_items') && is_array($request->input('replacement_items'))) {
            $filteredRepItems = array_values(array_filter($request->input('replacement_items'), function ($item) {
                return isset($item['quantity']) && (float)$item['quantity'] > 0;
            }));
            $request->merge(['replacement_items' => $filteredRepItems]);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'sale_id' => 'required|exists:sales,id',
            'return_date' => 'required|date',
            'refund_method' => 'required|in:cash,credit_deduction,exchange',
            'account_id' => 'nullable|exists:accounts,id',
            'reason' => 'required|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_id' => 'nullable|exists:units,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.batch_number' => 'nullable|string',
            'replacement_items' => 'nullable|array',
            'replacement_items.*.product_id' => 'required_with:replacement_items|exists:products,id',
            'replacement_items.*.unit_id' => 'nullable|exists:units,id',
            'replacement_items.*.quantity' => 'required_with:replacement_items|numeric|min:0.0001',
            'replacement_items.*.unit_price' => 'required_with:replacement_items|numeric|min:0',
        ], [
            'sale_id.required' => 'Faktur invoice penjualan wajib dipilih.',
            'sale_id.exists' => 'Faktur invoice penjualan tidak valid.',
            'reason.required' => 'Alasan retur wajib diisi.',
            'items.required' => 'Minimal 1 produk harus diretur dengan kuantiti lebih dari 0.',
            'items.min' => 'Minimal 1 produk harus diretur dengan kuantiti lebih dari 0.',
            'items.*.quantity.min' => 'Kuantiti retur produk harus lebih dari 0.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', $validator->errors()->first());
        }

        $validated = $validator->validated();

        try {
            $ret = $this->returnService->processSaleReturn($validated);
            return redirect()->route('sale-returns.index')->with('success', "Retur penjualan {$ret->return_number} berhasil diproses dan stok barang telah dikembalikan ke gudang.");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memproses retur penjualan: ' . $e->getMessage());
        }
    }

    /**
     * Cancel or Delete Sale Return.
     */
    public function destroy(SaleReturn $saleReturn)
    {
        try {
            $this->returnService->cancelSaleReturn($saleReturn);
            return redirect()->route('sale-returns.index')->with('success', "Retur penjualan {$saleReturn->return_number} berhasil dibatalkan dan mutasi persediaan dikembalikan.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membatalkan retur: ' . $e->getMessage());
        }
    }
}
