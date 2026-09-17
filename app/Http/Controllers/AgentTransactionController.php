<?php

namespace App\Http\Controllers;

use App\Models\AgentTransaction;
use App\Models\CashierShift;
use App\Services\AccountBalanceService;
use App\Services\AgentTransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentTransactionController extends Controller
{
    public function __construct(
        protected AgentTransactionService $agentTransactionService,
        protected AccountBalanceService $accountBalanceService
    ) {}

    /**
     * Get real-time balances for POS header widget.
     */
    public function getBalances(): JsonResponse
    {
        $summary = $this->accountBalanceService->getAccountsSummary();

        return response()->json([
            'status' => 'success',
            'data' => $summary,
        ]);
    }

    /**
     * Process Bank Transfer via POS.
     */
    public function storeBankTransfer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'account_id' => ['required', 'exists:accounts,id'],
            'destination_target' => ['required', 'string', 'max:100'],
            'destination_holder' => ['nullable', 'string', 'max:150'],
            'principal_amount' => ['required', 'numeric', 'min:1000'],
            'admin_fee' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [
            'account_id.required' => 'Rekening bank agen pengirim wajib dipilih.',
            'destination_target.required' => 'Nomor rekening tujuan wajib diisi.',
            'principal_amount.min' => 'Nominal transfer minimal Rp 1.000.',
        ]);

        $activeShift = CashierShift::where('user_id', auth()->id())
            ->where('status', 'open')
            ->first();

        $validated['cashier_shift_id'] = $activeShift?->id;
        $validated['user_id'] = auth()->id();
        $validated['payment_method'] = 'cash'; // Pelanggan setor tunai di kasir

        try {
            $transaction = $this->agentTransactionService->processBankTransfer($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi Transfer uang sebesar Rp '.number_format($transaction->principal_amount, 0, ',', '.').' berhasil diproses.',
                'data' => $transaction->load('account'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Process Cash Withdrawal via POS.
     */
    public function storeCashWithdrawal(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'account_id' => ['required', 'exists:accounts,id'],
            'principal_amount' => ['required', 'numeric', 'min:10000'],
            'admin_fee' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:cash,deduct_balance'],
            'destination_holder' => ['nullable', 'string', 'max:150'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [
            'account_id.required' => 'Rekening bank penampung tarik tunai wajib dipilih.',
            'principal_amount.min' => 'Nominal tarik tunai minimal Rp 10.000.',
        ]);

        $activeShift = CashierShift::where('user_id', auth()->id())
            ->where('status', 'open')
            ->first();

        $validated['cashier_shift_id'] = $activeShift?->id;
        $validated['user_id'] = auth()->id();
        $validated['destination_target'] = 'EDC / Tarik Tunai';

        try {
            $transaction = $this->agentTransactionService->processCashWithdrawal($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Tarik tunai nasabah sebesar Rp '.number_format($transaction->principal_amount, 0, ',', '.').' berhasil dicatat.',
                'data' => $transaction->load('account'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Process PPOB (Pulsa, Token PLN, E-Wallet) via POS.
     */
    public function storePpob(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'account_id' => ['required', 'exists:accounts,id'],
            'service_type' => ['required', 'string', 'in:pulsa,paket_data,token_pln,ewallet,tagihan'],
            'destination_target' => ['required', 'string', 'max:100'],
            'destination_holder' => ['nullable', 'string', 'max:150'],
            'cost_price' => ['required', 'numeric', 'min:100'],
            'selling_price' => ['required', 'numeric', 'min:100'],
            'payment_method' => ['required', 'in:cash,non_cash'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [
            'account_id.required' => 'Akun deposit saldo PPOB wajib dipilih.',
            'destination_target.required' => 'Nomor HP / Nomor Meteran pelanggan wajib diisi.',
            'cost_price.required' => 'Modal (HPP saldo terpotong) wajib diisi.',
            'selling_price.required' => 'Harga jual ke pelanggan wajib diisi.',
        ]);

        if ((float) $validated['selling_price'] < (float) $validated['cost_price']) {
            return response()->json([
                'status' => 'error',
                'message' => 'Harga jual tidak boleh lebih kecil dari harga modal (HPP).',
            ], 422);
        }

        $activeShift = CashierShift::where('user_id', auth()->id())
            ->where('status', 'open')
            ->first();

        $validated['cashier_shift_id'] = $activeShift?->id;
        $validated['user_id'] = auth()->id();

        try {
            $transaction = $this->agentTransactionService->processPpobTransaction($validated);

            return response()->json([
                'status' => 'success',
                'message' => "Transaksi PPOB {$transaction->service_type} berhasil dicatat.",
                'data' => $transaction->load('account'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get recent transactions for the active shift or today.
     */
    public function getRecent(Request $request): JsonResponse
    {
        $activeShift = CashierShift::where('user_id', auth()->id())
            ->where('status', 'open')
            ->first();

        $transactions = AgentTransaction::with(['account', 'user'])
            ->when($activeShift, function ($q) use ($activeShift) {
                $q->where('cashier_shift_id', $activeShift->id);
            }, function ($q) {
                $q->whereDate('created_at', now()->toDateString());
            })
            ->latest('id')
            ->take(20)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $transactions,
        ]);
    }
}
