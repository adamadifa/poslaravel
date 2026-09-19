<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Account;
use App\Models\CashFlow;
use App\Models\CashierShift;
use App\Models\CashierShiftExpense;
use App\Models\Sale;
use App\Services\FinanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ShiftApiController extends BaseApiController
{
    public function __construct(
        protected FinanceService $financeService
    ) {}

    /**
     * Format shift model with rich financial metrics (PPOB, Agen, Retail).
     */
    protected function formatShiftData(?CashierShift $shift): ?array
    {
        if (! $shift) {
            return null;
        }

        $userId = $shift->user_id;
        $shiftSales = Sale::where('user_id', $userId)
            ->where('created_at', '>=', $shift->opened_at)
            ->get();

        $shiftTotalSales = (float) $shiftSales->sum('grand_total');
        $shiftCashSales = (float) $shiftSales->where('payment_method', 'cash')->sum('grand_total');
        $shiftNonCashSales = $shiftTotalSales - $shiftCashSales;
        $shiftTxCount = $shiftSales->count();

        $agentTxList = $shift->agentTransactions ?? collect();
        $ppobTxs = $agentTxList->where('category', 'ppob');
        $bankTxs = $agentTxList->where('category', 'bank_agent');

        $ppobSales = (float) $ppobTxs->sum('total_customer_paid');
        $ppobProfit = (float) $ppobTxs->sum('net_profit');
        $ppobCount = $ppobTxs->count();

        $bankTransferCount = $bankTxs->where('service_type', 'transfer')->count();
        $bankWithdrawalCount = $bankTxs->where('service_type', 'tarik_tunai')->count();
        $bankAgentProfit = (float) $bankTxs->sum('net_profit');
        $bankAgentFee = (float) $bankTxs->sum('admin_fee');

        $totalAgentProfit = (float) $shift->total_agent_profit;
        $totalAgentCashIn = (float) $shift->total_agent_cash_in;
        $totalAgentCashOut = (float) $shift->total_agent_cash_out;

        return [
            'id' => $shift->id,
            'user_id' => $shift->user_id,
            'warehouse_id' => $shift->warehouse_id,
            'warehouse_name' => $shift->warehouse?->name,
            'user_name' => $shift->user?->name,
            'opened_at' => $shift->opened_at?->format('H:i, d M Y'),
            'closed_at' => $shift->closed_at?->format('H:i, d M Y'),
            'starting_cash' => (float) $shift->starting_cash,
            'total_sales' => $shiftTotalSales,
            'total_cash_sales' => $shiftCashSales,
            'total_non_cash_sales' => $shiftNonCashSales,
            'total_transactions' => $shiftTxCount,
            'total_expenses' => (float) $shift->total_expenses,
            'expected_cash' => (float) $shift->expected_cash,
            'closing_cash' => (float) $shift->closing_cash,
            'cash_difference' => (float) $shift->cash_difference,
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
            'status' => $shift->status,
            'notes' => $shift->notes,
            'expenses' => $shift->expenses->map(fn ($e) => [
                'id' => $e->id,
                'cashier_shift_id' => $e->cashier_shift_id,
                'amount' => (float) $e->amount,
                'category' => $e->category,
                'notes' => $e->notes,
                'expense_date' => $e->expense_date?->format('H:i, d M Y'),
            ]),
        ];
    }

    /**
     * Get active shift for current authenticated cashier.
     */
    public function current(Request $request): JsonResponse
    {
        $userId = auth()->id();
        $shift = CashierShift::with(['warehouse', 'user', 'expenses.user', 'agentTransactions.account'])
            ->where('user_id', $userId)
            ->where('status', 'open')
            ->first();

        return $this->sendResponse($this->formatShiftData($shift), 'Data sesi shift aktif.');
    }

    /**
     * Open a new shift.
     */
    public function open(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'starting_cash' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [
            'starting_cash.required' => 'Modal awal kasir wajib diisi.',
            'starting_cash.numeric' => 'Modal awal harus berupa angka valid.',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        $userId = auth()->id();
        $existingShift = CashierShift::where('user_id', $userId)
            ->where('status', 'open')
            ->first();

        if ($existingShift) {
            return $this->sendError('Anda masih memiliki sesi shift kasir yang aktif.', [
                'active_shift' => $existingShift,
            ], 422);
        }

        $validated = $validator->validated();
        $startingCash = (float) $validated['starting_cash'];

        $shift = CashierShift::create([
            'user_id' => $userId,
            'warehouse_id' => $validated['warehouse_id'],
            'opened_at' => now(),
            'starting_cash' => $startingCash,
            'expected_cash' => $startingCash,
            'status' => 'open',
            'notes' => $validated['notes'] ?? null,
        ]);

        return $this->sendResponse(
            $shift->load(['warehouse', 'user', 'expenses', 'agentTransactions']),
            'Sesi shift kasir berhasil dibuka.'
        );
    }

    /**
     * Add expense to active shift.
     */
    public function addExpense(Request $request, CashierShift $shift): JsonResponse
    {
        if ($shift->status !== 'open') {
            return $this->sendError('Tidak dapat mencatat pengeluaran pada shift yang sudah ditutup.', [], 422);
        }

        $validator = Validator::make($request->all(), [
            'amount' => ['required', 'numeric', 'min:1'],
            'category' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        $validated = $validator->validated();
        $amount = (float) $validated['amount'];

        return DB::transaction(function () use ($shift, $validated, $amount) {
            $account = Account::where('is_default', true)->first()
                ?? Account::where('type', 'cash')->first()
                ?? Account::first();

            $cashFlow = null;
            if ($account) {
                $account->decrement('current_balance', $amount);
                $cashFlow = CashFlow::create([
                    'cash_flow_number' => $this->financeService->generateCashFlowNumber(),
                    'account_id' => $account->id,
                    'type' => 'expense',
                    'category' => $validated['category'],
                    'amount' => $amount,
                    'transaction_date' => now()->toDateString(),
                    'reference_type' => CashierShift::class,
                    'reference_id' => $shift->id,
                    'description' => "Kas Keluar Shift Kasir (#{$shift->id} - ".auth()->user()->name.'): '.($validated['notes'] ?? $validated['category']),
                    'created_by' => auth()->id(),
                ]);
            }

            $expense = CashierShiftExpense::create([
                'cashier_shift_id' => $shift->id,
                'user_id' => auth()->id(),
                'warehouse_id' => $shift->warehouse_id,
                'amount' => $amount,
                'category' => $validated['category'],
                'notes' => $validated['notes'] ?? null,
                'expense_date' => now(),
                'cash_flow_id' => $cashFlow?->id,
            ]);

            $shift->total_expenses += $amount;
            $shift->expected_cash -= $amount;
            $shift->save();

            return $this->sendResponse([
                'shift' => $shift->fresh(['warehouse', 'user', 'expenses.user']),
                'expense' => $expense->load('user'),
            ], 'Pengeluaran kasir berhasil dicatat.');
        });
    }

    /**
     * Delete an expense from active shift.
     */
    public function deleteExpense(CashierShift $shift, CashierShiftExpense $expense): JsonResponse
    {
        if ($shift->status !== 'open') {
            return $this->sendError('Tidak dapat menghapus pengeluaran pada shift yang sudah ditutup.', [], 422);
        }

        if ($expense->cashier_shift_id !== $shift->id) {
            return $this->sendError('Data pengeluaran tidak sesuai dengan sesi shift ini.', [], 422);
        }

        return DB::transaction(function () use ($shift, $expense) {
            $amount = (float) $expense->amount;

            $shift->total_expenses = max(0, $shift->total_expenses - $amount);
            $shift->expected_cash += $amount;
            $shift->save();

            if ($expense->cash_flow_id) {
                $cf = CashFlow::find($expense->cash_flow_id);
                if ($cf) {
                    $account = Account::find($cf->account_id);
                    if ($account) {
                        $account->increment('current_balance', $cf->amount);
                    }
                    $cf->delete();
                }
            }

            $expense->delete();

            return $this->sendResponse(
                $shift->fresh(['warehouse', 'user', 'expenses.user']),
                'Pengeluaran kasir berhasil dihapus.'
            );
        });
    }

    /**
     * Close an active shift.
     */
    public function close(Request $request, CashierShift $shift): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'closing_cash' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        if ($shift->status === 'closed') {
            return $this->sendError('Shift ini sudah ditutup sebelumnya.', [], 422);
        }

        $validated = $validator->validated();
        $closingCash = (float) $validated['closing_cash'];
        $expectedCash = (float) $shift->expected_cash;
        $diff = $closingCash - $expectedCash;

        $shift->update([
            'closed_at' => now(),
            'closing_cash' => $closingCash,
            'cash_difference' => $diff,
            'status' => 'closed',
            'notes' => $validated['notes'] ?? $shift->notes,
        ]);

        return $this->sendResponse(
            $shift->fresh(['warehouse', 'user', 'expenses', 'agentTransactions']),
            'Sesi shift kasir berhasil ditutup.'
        );
    }
}
