<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Payment;
use App\Models\PurchaseReceipt;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\Customer;
use App\Services\FinanceService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected FinanceService $financeService;

    public function __construct(FinanceService $financeService)
    {
        $this->financeService = $financeService;
    }

    /**
     * Display list of Account Payables (Hutang Pembelian).
     */
    public function payables(Request $request)
    {
        $supplierId = $request->query('supplier_id');
        $paymentStatus = $request->query('payment_status'); // unpaid, partial, paid
        $search = $request->query('search');

        $receipts = PurchaseReceipt::with(['supplier', 'warehouse'])
            ->where('status', 'completed')
            ->when($supplierId, fn($q) => $q->where('supplier_id', $supplierId))
            ->when($paymentStatus, function ($q, $paymentStatus) {
                $q->where('payment_status', $paymentStatus);
            }, function ($q) {
                // Default: show outstanding (unpaid or partial)
                if (!request()->has('payment_status')) {
                    $q->whereIn('payment_status', ['unpaid', 'partial']);
                }
            })
            ->when($search, function ($q, $search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('receipt_number', 'like', "%{$search}%")
                      ->orWhere('invoice_number', 'like', "%{$search}%")
                      ->orWhereHas('supplier', fn($sp) => $sp->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('receipt_date')
            ->paginate(15)
            ->withQueryString();

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $accounts = Account::where('is_active', true)->orderBy('name')->get();

        // Total Outstanding Payables
        $totalOutstanding = PurchaseReceipt::where('status', 'completed')
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->selectRaw('SUM(grand_total - paid_amount) as total')
            ->value('total') ?? 0;

        return view('finance.payables.index', [
            'title' => 'Pembayaran Hutang Usaha',
            'headerTitle' => 'Pembayaran Hutang Usaha (Account Payables)',
            'headerDescription' => 'Kelola pelunasan faktur pembelian supplier, pembayaran cicilan/parsial, dan histori pengeluaran kas.',
            'breadcrumbParent' => 'Keuangan & Kas',
            'breadcrumbCurrent' => 'Hutang Usaha',
            'receipts' => $receipts,
            'suppliers' => $suppliers,
            'accounts' => $accounts,
            'totalOutstanding' => $totalOutstanding,
            'supplierId' => $supplierId,
            'paymentStatus' => $paymentStatus,
            'search' => $search,
        ]);
    }

    /**
     * Store AP Payment (Bayar Hutang Supplier).
     */
    public function storePayable(Request $request)
    {
        $validated = $request->validate([
            'purchase_receipt_id' => 'required|exists:purchase_receipts,id',
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,transfer,check,other',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $payment = $this->financeService->processPayablePayment($validated);
            return redirect()->route('payables.index')->with('success', "Pembayaran hutang {$payment->payment_number} sebesar Rp " . number_format($payment->amount, 0, ',', '.') . " berhasil dicatat.");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Display list of Account Receivables (Piutang Penjualan).
     */
    public function receivables(Request $request)
    {
        $customerId = $request->query('customer_id');
        $paymentStatus = $request->query('payment_status');
        $search = $request->query('search');

        $sales = Sale::with(['customer', 'warehouse', 'user'])
            ->where('status', 'completed')
            ->when($customerId, fn($q) => $q->where('customer_id', $customerId))
            ->when($paymentStatus, function ($q, $paymentStatus) {
                $q->where('payment_status', $paymentStatus);
            }, function ($q) {
                // Default: show outstanding (unpaid or partial)
                if (!request()->has('payment_status')) {
                    $q->whereIn('payment_status', ['unpaid', 'partial']);
                }
            })
            ->when($search, function ($q, $search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('invoice_number', 'like', "%{$search}%")
                      ->orWhereHas('customer', fn($cs) => $cs->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('sale_date')
            ->paginate(15)
            ->withQueryString();

        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $accounts = Account::where('is_active', true)->orderBy('name')->get();

        // Total Outstanding Receivables
        $totalOutstanding = Sale::where('status', 'completed')
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->selectRaw('SUM(grand_total - paid_amount) as total')
            ->value('total') ?? 0;

        return view('finance.receivables.index', [
            'title' => 'Penerimaan Piutang Usaha',
            'headerTitle' => 'Penerimaan Piutang Usaha (Account Receivables)',
            'headerDescription' => 'Pantau tagihan invoice penjualan ke pelanggan, terima pelunasan piutang, dan tambah saldo kas/bank.',
            'breadcrumbParent' => 'Keuangan & Kas',
            'breadcrumbCurrent' => 'Piutang Usaha',
            'sales' => $sales,
            'customers' => $customers,
            'accounts' => $accounts,
            'totalOutstanding' => $totalOutstanding,
            'customerId' => $customerId,
            'paymentStatus' => $paymentStatus,
            'search' => $search,
        ]);
    }

    /**
     * Store AR Collection (Terima Piutang Pelanggan).
     */
    public function storeReceivable(Request $request)
    {
        $validated = $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,transfer,check,other',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $payment = $this->financeService->processReceivableCollection($validated);
            return redirect()->route('receivables.index')->with('success', "Penerimaan piutang {$payment->payment_number} sebesar Rp " . number_format($payment->amount, 0, ',', '.') . " berhasil dicatat.");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memproses penerimaan piutang: ' . $e->getMessage());
        }
    }
}
