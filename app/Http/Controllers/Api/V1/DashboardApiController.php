<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\CashierShift;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardApiController extends BaseApiController
{
    /**
     * Get dashboard overview summary for mobile home screen.
     */
    public function summary(Request $request): JsonResponse
    {
        $userId = auth()->id();
        $today = Carbon::today();

        // Today's Sales Stats
        $todaySalesQuery = Sale::whereDate('sale_date', $today);
        $totalSalesAmount = (float) $todaySalesQuery->sum('grand_total');
        $totalTransactions = $todaySalesQuery->count();
        $totalCashSales = (float) Sale::whereDate('sale_date', $today)->where('payment_method', 'cash')->sum('grand_total');
        $totalNonCashSales = $totalSalesAmount - $totalCashSales;
        $averageTransaction = $totalTransactions > 0 ? ($totalSalesAmount / $totalTransactions) : 0.0;

        // Active Shift with Expenses & Agent Transactions
        $activeShift = CashierShift::with(['warehouse', 'user', 'expenses.user', 'agentTransactions.account'])
            ->where('user_id', $userId)
            ->where('status', 'open')
            ->first();

        $shiftDetails = null;
        if ($activeShift) {
            $shiftSales = Sale::where('user_id', $userId)
                ->where('created_at', '>=', $activeShift->opened_at)
                ->get();

            $shiftTotalSales = (float) $shiftSales->sum('grand_total');
            $shiftCashSales = (float) $shiftSales->where('payment_method', 'cash')->sum('grand_total');
            $shiftNonCashSales = $shiftTotalSales - $shiftCashSales;
            $shiftTxCount = $shiftSales->count();

            // PPOB & Agent Breakdown during this active shift
            $agentTxList = $activeShift->agentTransactions ?? collect();
            $ppobTxs = $agentTxList->where('category', 'ppob');
            $bankTxs = $agentTxList->where('category', 'bank_agent');

            $ppobSales = (float) $ppobTxs->sum('total_customer_paid');
            $ppobProfit = (float) $ppobTxs->sum('net_profit');
            $ppobCount = $ppobTxs->count();

            $bankTransferCount = $bankTxs->where('service_type', 'transfer')->count();
            $bankWithdrawalCount = $bankTxs->where('service_type', 'tarik_tunai')->count();
            $bankAgentProfit = (float) $bankTxs->sum('net_profit');
            $bankAgentFee = (float) $bankTxs->sum('admin_fee');

            $totalAgentProfit = (float) $activeShift->total_agent_profit;
            $totalAgentCashIn = (float) $activeShift->total_agent_cash_in;
            $totalAgentCashOut = (float) $activeShift->total_agent_cash_out;

            $shiftDetails = [
                'id' => $activeShift->id,
                'user_id' => $activeShift->user_id,
                'warehouse_id' => $activeShift->warehouse_id,
                'warehouse_name' => $activeShift->warehouse?->name,
                'user_name' => $activeShift->user?->name,
                'opened_at' => $activeShift->opened_at?->format('H:i, d M Y'),
                'starting_cash' => (float) $activeShift->starting_cash,
                'total_sales' => $shiftTotalSales,
                'total_cash_sales' => $shiftCashSales,
                'total_non_cash_sales' => $shiftNonCashSales,
                'total_transactions' => $shiftTxCount,
                'total_expenses' => (float) $activeShift->total_expenses,
                'expected_cash' => (float) $activeShift->expected_cash,
                'total_agent_cash_in' => $totalAgentCashIn,
                'total_agent_cash_out' => $totalAgentCashOut,
                'total_agent_profit' => $totalAgentProfit,
                'ppob_summary' => [
                    'count' => $ppobCount,
                    'total_sales' => $ppobSales,
                    'total_profit' => $ppobProfit,
                ],
                'agent_summary' => [
                    'transfer_count' => $bankTransferCount,
                    'withdrawal_count' => $bankWithdrawalCount,
                    'total_fee' => $bankAgentFee,
                    'total_profit' => $bankAgentProfit,
                    'cash_in' => $totalAgentCashIn,
                    'cash_out' => $totalAgentCashOut,
                ],
                'status' => $activeShift->status,
                'notes' => $activeShift->notes,
                'expenses' => $activeShift->expenses->map(fn ($e) => [
                    'id' => $e->id,
                    'cashier_shift_id' => $e->cashier_shift_id,
                    'amount' => (float) $e->amount,
                    'category' => $e->category,
                    'notes' => $e->notes,
                    'expense_date' => $e->expense_date?->format('H:i, d M Y'),
                ]),
            ];
        }

        // Recent Transactions (Last 5)
        $recentSales = Sale::with(['customer', 'warehouse'])
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'invoice_number' => $sale->invoice_number,
                    'customer_name' => $sale->customer?->name ?? 'Pelanggan Umum',
                    'grand_total' => (float) $sale->grand_total,
                    'payment_method' => $sale->payment_method ?? 'cash',
                    'payment_status' => $sale->payment_status ?? 'paid',
                    'created_at' => $sale->created_at?->format('H:i, d M Y'),
                ];
            });

        return $this->sendResponse([
            'today' => [
                'date' => $today->translatedFormat('l, d F Y'),
                'total_sales' => $totalSalesAmount,
                'total_transactions' => $totalTransactions,
                'total_cash_sales' => $totalCashSales,
                'total_non_cash_sales' => $totalNonCashSales,
                'average_transaction' => $averageTransaction,
            ],
            'active_shift' => $shiftDetails ?? $activeShift,
            'recent_sales' => $recentSales,
        ], 'Ringkasan dashboard berhasil dimuat.');
    }

    /**
     * Get sales transaction list.
     */
    public function sales(Request $request): JsonResponse
    {
        $query = Sale::with(['customer', 'warehouse', 'items.product'])->latest('id');

        if ($request->has('search') && ! empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        $sales = $query->paginate($request->get('per_page', 15));

        return $this->sendResponse($sales, 'Daftar transaksi penjualan.');
    }
}
