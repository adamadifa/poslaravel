<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\CashFlow;
use App\Services\FinanceService;
use Illuminate\Http\Request;

class CashFlowController extends Controller
{
    protected FinanceService $financeService;

    public function __construct(FinanceService $financeService)
    {
        $this->financeService = $financeService;
    }

    /**
     * Display listing of Cash Flow (Buku Kas Masuk & Kas Keluar).
     */
    public function index(Request $request)
    {
        $accountId = $request->query('account_id');
        $type = $request->query('type'); // income / expense
        $category = $request->query('category');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $search = $request->query('search');

        $cashFlows = CashFlow::with(['account', 'creator'])
            ->when($accountId, fn ($q) => $q->where('account_id', $accountId))
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($category, fn ($q) => $q->where('category', $category))
            ->when($startDate, fn ($q) => $q->whereDate('transaction_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('transaction_date', '<=', $endDate))
            ->when($search, function ($q, $search) {
                $q->where('cash_flow_number', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->latest('transaction_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $accounts = Account::where('is_active', true)->orderBy('name')->get();

        // Calculate summary metrics
        $totalIncome = CashFlow::where('type', 'income')
            ->when($startDate, fn ($q) => $q->whereDate('transaction_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('transaction_date', '<=', $endDate))
            ->sum('amount');

        $totalExpense = CashFlow::where('type', 'expense')
            ->when($startDate, fn ($q) => $q->whereDate('transaction_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('transaction_date', '<=', $endDate))
            ->sum('amount');

        $netFlow = $totalIncome - $totalExpense;

        return view('finance.cashflows.index', [
            'title' => 'Arus Kas (Cash Flow)',
            'headerTitle' => 'Buku Kas Masuk & Kas Keluar (Cash Flow)',
            'headerDescription' => 'Catat pemasukan manual, beban biaya operasional, gaji, listrik, sewa, serta seluruh mutasi kas bisnis.',
            'breadcrumbParent' => 'Keuangan & Kas',
            'breadcrumbCurrent' => 'Arus Kas',
            'cashFlows' => $cashFlows,
            'accounts' => $accounts,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'netFlow' => $netFlow,
            'accountId' => $accountId,
            'type' => $type,
            'category' => $category,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'search' => $search,
        ]);
    }

    /**
     * Store new Manual Cash Flow (Income or Expense).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'type' => 'required|in:income,expense',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:1',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        try {
            $cf = $this->financeService->recordCashFlow($validated);
            $msg = $cf->type === 'income'
                ? "Kas masuk {$cf->cash_flow_number} sebesar Rp ".number_format($cf->amount, 0, ',', '.').' berhasil dicatat.'
                : "Kas keluar {$cf->cash_flow_number} sebesar Rp ".number_format($cf->amount, 0, ',', '.').' berhasil dicatat.';

            return redirect()->route('cash-flows.index')->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal mencatat arus kas: '.$e->getMessage());
        }
    }

    /**
     * Show Cash Flow details via AJAX.
     */
    public function show(CashFlow $cashFlow)
    {
        return response()->json([
            'status' => 'success',
            'data' => $cashFlow->load(['account', 'creator']),
        ]);
    }

    /**
     * Update Manual Cash Flow.
     */
    public function update(Request $request, CashFlow $cashFlow)
    {
        // Prevent editing automatic system-generated cash flows (e.g., linked to payments or shift)
        if ($cashFlow->reference_type !== null) {
            return redirect()->back()->with('error', 'Transaksi arus kas otomatis sistem tidak dapat diubah langsung.');
        }

        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'type' => 'required|in:income,expense',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:1',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        try {
            $this->financeService->updateCashFlow($cashFlow, $validated);

            return redirect()->route('cash-flows.index')->with('success', "Arus kas {$cashFlow->cash_flow_number} berhasil diperbarui.");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui arus kas: '.$e->getMessage());
        }
    }

    /**
     * Delete Manual Cash Flow.
     */
    public function destroy(CashFlow $cashFlow)
    {
        // Prevent deleting automatic system-generated cash flows directly
        if ($cashFlow->reference_type !== null) {
            return redirect()->back()->with('error', 'Transaksi arus kas otomatis sistem tidak dapat dihapus dari sini.');
        }

        try {
            $number = $cashFlow->cash_flow_number;
            $this->financeService->deleteCashFlow($cashFlow);

            return redirect()->route('cash-flows.index')->with('success', "Transaksi arus kas {$number} berhasil dihapus dan saldo akun telah disesuaikan.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus arus kas: '.$e->getMessage());
        }
    }
}
