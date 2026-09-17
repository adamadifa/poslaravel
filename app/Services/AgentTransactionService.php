<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AgentTransaction;
use App\Models\CashFlow;
use App\Models\CashierShift;
use Illuminate\Support\Facades\DB;

class AgentTransactionService
{
    public function __construct(
        protected AccountBalanceService $accountBalanceService,
        protected FinanceService $financeService
    ) {}

    /**
     * Generate unique transaction number (e.g. AGT-2026-09-0001)
     */
    public function generateTransactionNumber(): string
    {
        $prefix = 'AGT-'.now()->format('Y-m-');
        $last = AgentTransaction::where('transaction_number', 'like', "{$prefix}%")
            ->orderBy('transaction_number', 'desc')
            ->first();

        $lastSeq = $last ? (int) substr($last->transaction_number, -4) : 0;

        return $prefix.str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Process Bank Transfer Service (Kirim Uang Nasabah via EDC/Rekening Agen).
     *
     * Alur Keuangan:
     * - Uang Tunai Kasir Laci (+) : Pokok Transfer + Admin Fee Toko
     * - Saldo Rekening Bank Agen (-) : Pokok Transfer + Biaya Bank Provider
     * - Laba Jasa Toko (+) : Admin Fee Toko - Biaya Bank Provider
     */
    public function processBankTransfer(array $payload): AgentTransaction
    {
        return DB::transaction(function () use ($payload) {
            $account = Account::lockForUpdate()->findOrFail($payload['account_id']);
            $userId = $payload['user_id'] ?? auth()->id();
            $shift = ! empty($payload['cashier_shift_id']) ? CashierShift::lockForUpdate()->find($payload['cashier_shift_id']) : null;

            $principalAmount = (float) $payload['principal_amount'];
            $adminFee = (float) ($payload['admin_fee'] ?? 0);
            $costPrice = (float) ($payload['cost_price'] ?? 0); // Biaya admin bank yang potong saldo toko
            $totalCustomerPaid = $principalAmount + $adminFee;
            $netProfit = max(0, $adminFee - $costPrice);

            // Total saldo rekening toko yang akan terpotong
            $totalAccountDeduction = $principalAmount + $costPrice;

            // Validasi kecukupan saldo akun agen
            if ((float) $account->current_balance < $totalAccountDeduction) {
                throw new \Exception("Saldo rekening '{$account->name}' tidak mencukupi untuk transfer. (Sisa Saldo: Rp ".number_format($account->current_balance, 0, ',', '.').', Dibutuhkan: Rp '.number_format($totalAccountDeduction, 0, ',', '.').')');
            }

            $txNumber = $this->generateTransactionNumber();

            // 1. Simpan Transaksi Agen
            $transaction = AgentTransaction::create([
                'transaction_number' => $txNumber,
                'cashier_shift_id' => $shift?->id,
                'user_id' => $userId,
                'account_id' => $account->id,
                'category' => 'bank_agent',
                'service_type' => 'transfer',
                'destination_target' => $payload['destination_target'],
                'destination_holder' => $payload['destination_holder'] ?? null,
                'principal_amount' => $principalAmount,
                'cost_price' => $costPrice,
                'admin_fee' => $adminFee,
                'total_customer_paid' => $totalCustomerPaid,
                'net_profit' => $netProfit,
                'payment_method' => $payload['payment_method'] ?? 'cash',
                'reference_number' => $payload['reference_number'] ?? null,
                'status' => 'success',
                'notes' => $payload['notes'] ?? null,
            ]);

            // 2. Potong Saldo Rekening Bank Agen & Catat Mutasi
            $this->accountBalanceService->debit(
                $account,
                $totalAccountDeduction,
                AgentTransaction::class,
                $transaction->id,
                "Transfer Kirim Uang ke {$payload['destination_target']} an. ".($payload['destination_holder'] ?? '-')." (Ref: {$txNumber})",
                $userId
            );

            // 3. Catat Kas Keluar di Buku Arus Kas (Potong Saldo Rekening Agen)
            CashFlow::create([
                'cash_flow_number' => $this->financeService->generateCashFlowNumber(),
                'account_id' => $account->id,
                'type' => 'expense',
                'category' => 'Transfer Bank Agen (Keluar)',
                'amount' => $totalAccountDeduction,
                'transaction_date' => now()->toDateString(),
                'reference_type' => AgentTransaction::class,
                'reference_id' => $transaction->id,
                'description' => "Transfer Kirim Uang ke {$payload['destination_target']} an. ".($payload['destination_holder'] ?? '-')." (Ref: {$txNumber})",
                'created_by' => $userId,
            ]);

            // 4. Update Kasir Shift & Catat Kas Masuk Kasir jika pembayaran tunai
            if (($payload['payment_method'] ?? 'cash') === 'cash') {
                if ($shift) {
                    $shift->expected_cash += $totalCustomerPaid;
                    $shift->total_agent_cash_in += $totalCustomerPaid;
                    $shift->total_agent_profit += $netProfit;
                    $shift->save();
                }

                $cashAccount = Account::where('is_default', true)->first()
                    ?? Account::where('type', 'cash')->first();

                if ($cashAccount) {
                    CashFlow::create([
                        'cash_flow_number' => $this->financeService->generateCashFlowNumber(),
                        'account_id' => $cashAccount->id,
                        'type' => 'income',
                        'category' => 'Transfer Bank Agen (Masuk Kasir)',
                        'amount' => $totalCustomerPaid,
                        'transaction_date' => now()->toDateString(),
                        'reference_type' => AgentTransaction::class,
                        'reference_id' => $transaction->id,
                        'description' => "Terima Tunai Transfer dari pelanggan ke {$payload['destination_target']} (Ref: {$txNumber})",
                        'created_by' => $userId,
                    ]);
                }
            }

            return $transaction;
        });
    }

    /**
     * Process Cash Withdrawal Service (Tarik Tunai Nasabah).
     *
     * Alur Keuangan:
     * - Uang Tunai Kasir Laci (-) : Diberikan ke nasabah (Pokok Tarik Tunai)
     * - Saldo Rekening Bank Agen (+) : Bertambah dari gesek kartu EDC / transfer nasabah
     * - Laba Jasa Toko (+) : Admin Fee Toko
     */
    public function processCashWithdrawal(array $payload): AgentTransaction
    {
        return DB::transaction(function () use ($payload) {
            $account = Account::lockForUpdate()->findOrFail($payload['account_id']);
            $userId = $payload['user_id'] ?? auth()->id();
            $shift = ! empty($payload['cashier_shift_id']) ? CashierShift::lockForUpdate()->find($payload['cashier_shift_id']) : null;

            $principalAmount = (float) $payload['principal_amount'];
            $adminFee = (float) ($payload['admin_fee'] ?? 0);
            $costPrice = (float) ($payload['cost_price'] ?? 0);
            $netProfit = max(0, $adminFee - $costPrice);

            // Metode pembayaran admin:
            // 'cash' => nasabah bayar admin cash di luar tarik tunai
            // 'deduct_balance' => nasabah gesek pokok + admin di EDC
            $paymentMethod = $payload['payment_method'] ?? 'cash';

            // Validasi uang kas di laci kasir (jika ada sesi shift aktif)
            if ($shift && (float) $shift->expected_cash < $principalAmount) {
                throw new \Exception('Uang kas fisik di laci kasir tidak mencukupi untuk tarik tunai. (Sisa Kas Laci: Rp '.number_format($shift->expected_cash, 0, ',', '.').', Diminta: Rp '.number_format($principalAmount, 0, ',', '.').')');
            }

            // Total saldo bank yang bertambah ke rekening toko
            $totalBankReceived = $paymentMethod === 'deduct_balance'
                ? ($principalAmount + $adminFee)
                : $principalAmount;

            $totalCustomerPaid = $paymentMethod === 'deduct_balance'
                ? ($principalAmount + $adminFee)
                : $adminFee;

            $txNumber = $this->generateTransactionNumber();

            // 1. Simpan Transaksi Agen
            $transaction = AgentTransaction::create([
                'transaction_number' => $txNumber,
                'cashier_shift_id' => $shift?->id,
                'user_id' => $userId,
                'account_id' => $account->id,
                'category' => 'bank_agent',
                'service_type' => 'tarik_tunai',
                'destination_target' => $payload['destination_target'] ?? 'EDC / Nasabah',
                'destination_holder' => $payload['destination_holder'] ?? null,
                'principal_amount' => $principalAmount,
                'cost_price' => $costPrice,
                'admin_fee' => $adminFee,
                'total_customer_paid' => $totalCustomerPaid,
                'net_profit' => $netProfit,
                'payment_method' => $paymentMethod,
                'reference_number' => $payload['reference_number'] ?? null,
                'status' => 'success',
                'notes' => $payload['notes'] ?? null,
            ]);

            // 2. Tambahkan Saldo Rekening Bank Agen & Catat Mutasi
            $holderText = ! empty($payload['destination_holder']) ? " an. {$payload['destination_holder']}" : '';
            $this->accountBalanceService->credit(
                $account,
                $totalBankReceived,
                AgentTransaction::class,
                $transaction->id,
                "Tarik Tunai Nasabah (Ref: {$txNumber}){$holderText}",
                $userId
            );

            // 3. Catat Kas Masuk di Buku Arus Kas (Saldo Bank Agen Bertambah dari gesek/transfer nasabah)
            CashFlow::create([
                'cash_flow_number' => $this->financeService->generateCashFlowNumber(),
                'account_id' => $account->id,
                'type' => 'income',
                'category' => 'Tarik Tunai Agen (Masuk Bank)',
                'amount' => $totalBankReceived,
                'transaction_date' => now()->toDateString(),
                'reference_type' => AgentTransaction::class,
                'reference_id' => $transaction->id,
                'description' => "Tarik Tunai Nasabah via EDC/Rekening (Ref: {$txNumber}){$holderText}",
                'created_by' => $userId,
            ]);

            // 4. Catat Kas Keluar dari Kas Toko (Uang Tunai Diserahkan ke Nasabah)
            $cashAccount = Account::where('is_default', true)->first()
                ?? Account::where('type', 'cash')->first();

            if ($cashAccount) {
                CashFlow::create([
                    'cash_flow_number' => $this->financeService->generateCashFlowNumber(),
                    'account_id' => $cashAccount->id,
                    'type' => 'expense',
                    'category' => 'Tarik Tunai Agen (Keluar Kasir)',
                    'amount' => $principalAmount,
                    'transaction_date' => now()->toDateString(),
                    'reference_type' => AgentTransaction::class,
                    'reference_id' => $transaction->id,
                    'description' => "Uang Fisik Kasir Diserahkan ke Nasabah Tarik Tunai (Ref: {$txNumber})",
                    'created_by' => $userId,
                ]);
            }

            // 5. Update Kasir Shift (Kas fisik laci berkurang pokok yang diserahkan ke nasabah)
            if ($shift) {
                $shift->expected_cash -= $principalAmount;
                $shift->total_agent_cash_out += $principalAmount;

                // Jika admin dibayar tunai oleh pelanggan:
                if ($paymentMethod === 'cash') {
                    $shift->expected_cash += $adminFee;
                    $shift->total_agent_cash_in += $adminFee;
                }

                $shift->total_agent_profit += $netProfit;
                $shift->save();
            }

            return $transaction;
        });
    }

    /**
     * Process PPOB / Pulsa / Token Listrik Transaction.
     *
     * Alur Keuangan:
     * - Saldo Deposit PPOB (-) : Sebesar HPP / Modal
     * - Uang Tunai / Non-Tunai Kasir (+) : Sebesar Harga Jual ke Pelanggan
     * - Laba Kotor (+) : Harga Jual - Modal (HPP)
     */
    public function processPpobTransaction(array $payload): AgentTransaction
    {
        return DB::transaction(function () use ($payload) {
            $account = Account::lockForUpdate()->findOrFail($payload['account_id']);
            $userId = $payload['user_id'] ?? auth()->id();
            $shift = ! empty($payload['cashier_shift_id']) ? CashierShift::lockForUpdate()->find($payload['cashier_shift_id']) : null;

            $costPrice = (float) $payload['cost_price']; // Modal saldo PPOB
            $sellingPrice = (float) $payload['selling_price']; // Harga jual ke pelanggan
            $netProfit = max(0, $sellingPrice - $costPrice);
            $paymentMethod = $payload['payment_method'] ?? 'cash';

            // Cek saldo deposit PPOB
            if ((float) $account->current_balance < $costPrice) {
                throw new \Exception("Saldo deposit PPOB '{$account->name}' tidak mencukupi. (Sisa Saldo: Rp ".number_format($account->current_balance, 0, ',', '.').', Modal: Rp '.number_format($costPrice, 0, ',', '.').')');
            }

            $txNumber = $this->generateTransactionNumber();

            // 1. Simpan Transaksi Agen
            $transaction = AgentTransaction::create([
                'transaction_number' => $txNumber,
                'cashier_shift_id' => $shift?->id,
                'user_id' => $userId,
                'account_id' => $account->id,
                'category' => 'ppob',
                'service_type' => $payload['service_type'] ?? 'pulsa',
                'destination_target' => $payload['destination_target'],
                'destination_holder' => $payload['destination_holder'] ?? null,
                'principal_amount' => $costPrice,
                'cost_price' => $costPrice,
                'admin_fee' => $sellingPrice,
                'total_customer_paid' => $sellingPrice,
                'net_profit' => $netProfit,
                'payment_method' => $paymentMethod,
                'reference_number' => $payload['reference_number'] ?? null, // No SN / Token
                'status' => 'success',
                'notes' => $payload['notes'] ?? null,
            ]);

            // 2. Potong Saldo Deposit PPOB
            $this->accountBalanceService->debit(
                $account,
                $costPrice,
                AgentTransaction::class,
                $transaction->id,
                "Penjualan PPOB {$transaction->service_type} ke {$payload['destination_target']} (Ref: {$txNumber})",
                $userId
            );

            // 3. Catat Kas Keluar di Buku Arus Kas (Potong Saldo Akun PPOB)
            CashFlow::create([
                'cash_flow_number' => $this->financeService->generateCashFlowNumber(),
                'account_id' => $account->id,
                'type' => 'expense',
                'category' => 'Modal Saldo PPOB (Keluar)',
                'amount' => $costPrice,
                'transaction_date' => now()->toDateString(),
                'reference_type' => AgentTransaction::class,
                'reference_id' => $transaction->id,
                'description' => "Potong Saldo PPOB {$transaction->service_type} ke {$payload['destination_target']} (Ref: {$txNumber})",
                'created_by' => $userId,
            ]);

            // 4. Update Kasir Shift & Catat Kas Masuk Kasir jika pembayaran tunai
            if ($paymentMethod === 'cash') {
                if ($shift) {
                    $shift->expected_cash += $sellingPrice;
                    $shift->total_agent_cash_in += $sellingPrice;
                    $shift->total_agent_profit += $netProfit;
                    $shift->save();
                }

                // Catat Kas Masuk Pelanggan ke Akun Kasir Utama
                $cashAccount = Account::where('is_default', true)->first()
                    ?? Account::where('type', 'cash')->first();

                if ($cashAccount) {
                    CashFlow::create([
                        'cash_flow_number' => $this->financeService->generateCashFlowNumber(),
                        'account_id' => $cashAccount->id,
                        'type' => 'income',
                        'category' => 'Penjualan PPOB & Agen (Masuk)',
                        'amount' => $sellingPrice,
                        'transaction_date' => now()->toDateString(),
                        'reference_type' => AgentTransaction::class,
                        'reference_id' => $transaction->id,
                        'description' => "Terima Tunai PPOB {$transaction->service_type} dari pelanggan (Ref: {$txNumber})",
                        'created_by' => $userId,
                    ]);
                }
            }

            return $transaction;
        });
    }
}
