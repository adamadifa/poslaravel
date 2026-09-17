<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\CashFlow;
use App\Models\CashierShift;
use App\Models\CashierShiftExpense;
use App\Services\FinanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashierShiftController extends Controller
{
    public function __construct(
        protected FinanceService $financeService
    ) {}

    /**
     * Get the current active shift for the logged-in user.
     */
    public function current(Request $request): JsonResponse
    {
        $userId = auth()->id();
        $shift = CashierShift::with(['warehouse', 'user', 'expenses.user', 'agentTransactions.account'])
            ->where('user_id', $userId)
            ->where('status', 'open')
            ->first();

        return response()->json([
            'status' => 'success',
            'data' => $shift,
        ]);
    }

    /**
     * Open a new cashier shift (Input Modal Awal).
     */
    public function open(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'starting_cash' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [
            'starting_cash.required' => 'Modal awal kasir wajib diisi.',
            'starting_cash.numeric' => 'Modal awal harus berupa angka valid.',
        ]);

        $userId = auth()->id();

        // Check if user already has an active open shift
        $existingShift = CashierShift::where('user_id', $userId)
            ->where('status', 'open')
            ->first();

        if ($existingShift) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda masih memiliki sesi shift kasir yang aktif.',
                'data' => $existingShift,
            ], 422);
        }

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

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Sesi shift kasir berhasil dibuka.',
                'data' => $shift->load(['warehouse', 'user', 'expenses', 'agentTransactions']),
            ]);
        }

        return redirect()->back()->with('success', 'Shift kasir berhasil dibuka.');
    }

    /**
     * Record an expense (kas keluar / petty cash) for an active shift.
     */
    public function addExpense(Request $request, CashierShift $shift): JsonResponse
    {
        if ($shift->status !== 'open') {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak dapat mencatat pengeluaran pada shift yang sudah ditutup.',
            ], 422);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'category' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [
            'amount.required' => 'Nominal pengeluaran wajib diisi.',
            'amount.min' => 'Nominal pengeluaran minimal Rp 1.',
            'category.required' => 'Kategori pengeluaran wajib dipilih/diisi.',
        ]);

        $amount = (float) $validated['amount'];

        return DB::transaction(function () use ($shift, $validated, $amount) {
            // Find default cash account if exists to mirror to cash flow
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

            // Deduct expected_cash and add to total_expenses
            $shift->total_expenses += $amount;
            $shift->expected_cash -= $amount;
            $shift->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Pengeluaran kasir sebesar Rp '.number_format($amount, 0, ',', '.').' berhasil dicatat.',
                'data' => $shift->fresh(['warehouse', 'user', 'expenses.user']),
                'expense' => $expense->load('user'),
            ]);
        });
    }

    /**
     * Delete / Void an expense from an active shift.
     */
    public function deleteExpense(CashierShift $shift, CashierShiftExpense $expense): JsonResponse
    {
        if ($shift->status !== 'open') {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak dapat menghapus pengeluaran pada shift yang sudah ditutup.',
            ], 422);
        }

        if ($expense->cashier_shift_id !== $shift->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data pengeluaran tidak sesuai dengan sesi shift ini.',
            ], 422);
        }

        return DB::transaction(function () use ($shift, $expense) {
            $amount = (float) $expense->amount;

            // Revert shift calculations
            $shift->total_expenses = max(0, $shift->total_expenses - $amount);
            $shift->expected_cash += $amount;
            $shift->save();

            // Revert cash flow and account balance if exists
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

            return response()->json([
                'status' => 'success',
                'message' => 'Pengeluaran kasir berhasil dihapus/dibatalkan.',
                'data' => $shift->fresh(['warehouse', 'user', 'expenses.user']),
            ]);
        });
    }

    /**
     * Close an active shift (Input Kas Fisik & Hitung Selisih).
     */
    public function close(Request $request, CashierShift $shift): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'closing_cash' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [
            'closing_cash.required' => 'Penghitungan fisik kas aktual wajib diisi.',
        ]);

        if ($shift->status === 'closed') {
            return response()->json([
                'status' => 'error',
                'message' => 'Shift ini sudah ditutup sebelumnya.',
            ], 422);
        }

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

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Shift kasir berhasil ditutup.',
                'data' => $shift->fresh(['warehouse', 'user', 'expenses', 'agentTransactions']),
            ]);
        }

        return redirect()->back()->with('success', 'Shift kasir berhasil ditutup.');
    }
}
