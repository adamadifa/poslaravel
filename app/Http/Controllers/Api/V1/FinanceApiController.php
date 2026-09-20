<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Account;
use App\Models\AccountTransfer;
use App\Models\CashFlow;
use App\Services\FinanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FinanceApiController extends BaseApiController
{
    public function __construct(
        protected FinanceService $financeService
    ) {}

    /**
     * Get list of Cash Flow (Buku Kas Masuk & Kas Keluar) with filters & metrics.
     */
    public function getCashFlows(Request $request): JsonResponse
    {
        $accountId = $request->query('account_id');
        $type = $request->query('type'); // income / expense
        $category = $request->query('category');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $search = $request->query('search');
        $perPage = (int) ($request->query('per_page', 20));

        $query = CashFlow::with(['account', 'creator'])
            ->when($accountId, fn ($q) => $q->where('account_id', $accountId))
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($category, fn ($q) => $q->where('category', $category))
            ->when($startDate, fn ($q) => $q->whereDate('transaction_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('transaction_date', '<=', $endDate))
            ->when($search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('cash_flow_number', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            });

        // Calculate summary metrics for the given filters
        $metricsQuery = CashFlow::query()
            ->when($accountId, fn ($q) => $q->where('account_id', $accountId))
            ->when($category, fn ($q) => $q->where('category', $category))
            ->when($startDate, fn ($q) => $q->whereDate('transaction_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('transaction_date', '<=', $endDate))
            ->when($search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('cash_flow_number', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            });

        $totalIncome = (float) (clone $metricsQuery)->where('type', 'income')->sum('amount');
        $totalExpense = (float) (clone $metricsQuery)->where('type', 'expense')->sum('amount');
        $netFlow = $totalIncome - $totalExpense;

        $cashFlows = $query->latest('transaction_date')
            ->latest('id')
            ->paginate($perPage);

        return $this->sendResponse([
            'cash_flows' => $cashFlows->items(),
            'pagination' => [
                'current_page' => $cashFlows->currentPage(),
                'last_page' => $cashFlows->lastPage(),
                'per_page' => $cashFlows->perPage(),
                'total' => $cashFlows->total(),
            ],
            'summary' => [
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'net_flow' => $netFlow,
            ],
        ], 'Data arus kas berhasil dimuat');
    }

    /**
     * Show detail of a Cash Flow record.
     */
    public function showCashFlow(CashFlow $cashFlow): JsonResponse
    {
        $cashFlow->load(['account', 'creator']);

        return $this->sendResponse($cashFlow, 'Detail arus kas berhasil dimuat');
    }

    /**
     * Store new manual Cash Flow (Income or Expense).
     */
    public function storeCashFlow(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'account_id' => 'required|exists:accounts,id',
            'type' => 'required|in:income,expense',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:1',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi gagal', $validator->errors()->all(), 422);
        }

        try {
            $cashFlow = $this->financeService->recordCashFlow($validator->validated());
            $cashFlow->load(['account', 'creator']);

            $msg = $cashFlow->type === 'income'
                ? 'Kas masuk berhasil dicatat'
                : 'Kas keluar berhasil dicatat';

            return $this->sendResponse($cashFlow, $msg, 201);
        } catch (\Exception $e) {
            return $this->sendError('Gagal mencatat arus kas: '.$e->getMessage(), [], 500);
        }
    }

    /**
     * Update manual Cash Flow.
     */
    public function updateCashFlow(Request $request, CashFlow $cashFlow): JsonResponse
    {
        if ($cashFlow->reference_type !== null) {
            return $this->sendError('Transaksi arus kas otomatis sistem tidak dapat diubah langsung.', [], 422);
        }

        $validator = Validator::make($request->all(), [
            'account_id' => 'required|exists:accounts,id',
            'type' => 'required|in:income,expense',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:1',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi gagal', $validator->errors()->all(), 422);
        }

        try {
            $updated = $this->financeService->updateCashFlow($cashFlow, $validator->validated());
            $updated->load(['account', 'creator']);

            return $this->sendResponse($updated, "Arus kas {$updated->cash_flow_number} berhasil diperbarui");
        } catch (\Exception $e) {
            return $this->sendError('Gagal memperbarui arus kas: '.$e->getMessage(), [], 500);
        }
    }

    /**
     * Delete manual Cash Flow.
     */
    public function destroyCashFlow(CashFlow $cashFlow): JsonResponse
    {
        if ($cashFlow->reference_type !== null) {
            return $this->sendError('Transaksi arus kas otomatis sistem tidak dapat dihapus.', [], 422);
        }

        try {
            $number = $cashFlow->cash_flow_number;
            $this->financeService->deleteCashFlow($cashFlow);

            return $this->sendResponse(null, "Transaksi arus kas {$number} berhasil dihapus dan saldo kas/bank telah disesuaikan");
        } catch (\Exception $e) {
            return $this->sendError('Gagal menghapus arus kas: '.$e->getMessage(), [], 500);
        }
    }

    /**
     * Get default recommendations for income and expense categories.
     */
    public function getCategories(): JsonResponse
    {
        $incomeCategories = [
            'Pendapatan Penjualan Tambahan',
            'Pemasukan Modal Pemilik',
            'Pendapatan Jasa / Servis',
            'Pendapatan Bunga / Bonus',
            'Pemasukan Lain-lain',
        ];

        $expenseCategories = [
            'Beban Gaji & Upah Karyawan',
            'Beban Listrik, Air & Internet',
            'Beban Sewa Tempat / Gedung',
            'Beban Operasional & Kebersihan',
            'Beban Transportasi & Logistik',
            'Beban Pemasaran & Iklan',
            'Beban Pemeliharaan & Perbaikan',
            'Pengambilan Pribadi (Prive)',
            'Beban Pajak & Retribusi',
            'Pengeluaran Lain-lain',
        ];

        // Also merge any existing distinct categories from DB
        $dbIncomeCategories = CashFlow::where('type', 'income')->distinct()->pluck('category')->toArray();
        $dbExpenseCategories = CashFlow::where('type', 'expense')->distinct()->pluck('category')->toArray();

        return $this->sendResponse([
            'income_categories' => array_values(array_unique(array_merge($incomeCategories, $dbIncomeCategories))),
            'expense_categories' => array_values(array_unique(array_merge($expenseCategories, $dbExpenseCategories))),
        ], 'Kategori arus kas berhasil dimuat');
    }

    /**
     * Get list of Account Transfers (Transfer Antar Kas & Bank) with filters & metrics.
     */
    public function getAccountTransfers(Request $request): JsonResponse
    {
        $fromAccountId = $request->query('from_account_id');
        $toAccountId = $request->query('to_account_id');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $search = $request->query('search');
        $perPage = (int) ($request->query('per_page', 20));

        $query = AccountTransfer::with(['fromAccount', 'toAccount', 'creator'])
            ->when($fromAccountId, fn ($q) => $q->where('from_account_id', $fromAccountId))
            ->when($toAccountId, fn ($q) => $q->where('to_account_id', $toAccountId))
            ->when($startDate, fn ($q) => $q->whereDate('transfer_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('transfer_date', '<=', $endDate))
            ->when($search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('transfer_number', 'like', "%{$search}%")
                        ->orWhere('reference_number', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            });

        // Summary metrics
        $metricsQuery = AccountTransfer::query()
            ->when($fromAccountId, fn ($q) => $q->where('from_account_id', $fromAccountId))
            ->when($toAccountId, fn ($q) => $q->where('to_account_id', $toAccountId))
            ->when($startDate, fn ($q) => $q->whereDate('transfer_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('transfer_date', '<=', $endDate))
            ->when($search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('transfer_number', 'like', "%{$search}%")
                        ->orWhere('reference_number', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            });

        $totalTransferred = (float) (clone $metricsQuery)->sum('amount');
        $totalFee = (float) (clone $metricsQuery)->sum('transfer_fee');

        $transfers = $query->latest('transfer_date')
            ->latest('id')
            ->paginate($perPage);

        return $this->sendResponse([
            'transfers' => $transfers->items(),
            'pagination' => [
                'current_page' => $transfers->currentPage(),
                'last_page' => $transfers->lastPage(),
                'per_page' => $transfers->perPage(),
                'total' => $transfers->total(),
            ],
            'summary' => [
                'total_transferred' => $totalTransferred,
                'total_fee' => $totalFee,
            ],
        ], 'Data transfer kas & bank berhasil dimuat');
    }

    /**
     * Show detail of an Account Transfer.
     */
    public function showAccountTransfer(AccountTransfer $accountTransfer): JsonResponse
    {
        $accountTransfer->load(['fromAccount', 'toAccount', 'creator']);

        return $this->sendResponse($accountTransfer, 'Detail transfer kas & bank berhasil dimuat');
    }

    /**
     * Store new Account Transfer (Transfer Kas/Bank).
     */
    public function storeAccountTransfer(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'from_account_id' => 'required|exists:accounts,id|different:to_account_id',
            'to_account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:1',
            'transfer_fee' => 'nullable|numeric|min:0',
            'transfer_date' => 'required|date',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ], [
            'from_account_id.different' => 'Akun asal dan akun tujuan transfer tidak boleh sama.',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi gagal', $validator->errors()->all(), 422);
        }

        try {
            $fromAccount = Account::findOrFail($request->input('from_account_id'));
            $totalDeduction = (float) $request->input('amount') + (float) ($request->input('transfer_fee') ?? 0);

            if ((float) $fromAccount->current_balance < $totalDeduction) {
                return $this->sendError("Saldo akun asal '{$fromAccount->name}' tidak mencukupi (Saldo: Rp ".number_format($fromAccount->current_balance, 0, ',', '.').', Dibutuhkan: Rp '.number_format($totalDeduction, 0, ',', '.').')', [], 422);
            }

            $transfer = $this->financeService->transferAccount($validator->validated());
            $transfer->load(['fromAccount', 'toAccount', 'creator']);

            return $this->sendResponse(
                $transfer,
                "Transfer {$transfer->transfer_number} sebesar Rp ".number_format($transfer->amount, 0, ',', '.').' berhasil diproses',
                201
            );
        } catch (\Exception $e) {
            return $this->sendError('Gagal memproses transfer kas: '.$e->getMessage(), [], 500);
        }
    }

    /**
     * Delete / Cancel an Account Transfer.
     */
    public function destroyAccountTransfer(AccountTransfer $accountTransfer): JsonResponse
    {
        try {
            $transferNumber = $accountTransfer->transfer_number;
            $this->financeService->cancelTransfer($accountTransfer);

            return $this->sendResponse(
                null,
                "Transfer {$transferNumber} berhasil dibatalkan dan saldo telah dikembalikan."
            );
        } catch (\Exception $e) {
            return $this->sendError('Gagal membatalkan transfer: '.$e->getMessage(), [], 400);
        }
    }
}
