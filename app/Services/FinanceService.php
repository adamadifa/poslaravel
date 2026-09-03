<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountTransfer;
use App\Models\CashFlow;
use App\Models\Payment;
use App\Models\PurchaseReceipt;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class FinanceService
{
    /**
     * Generate Payment Number (PAY-YYYY-MM-0001)
     */
    public function generatePaymentNumber(): string
    {
        $prefix = 'PAY-' . now()->format('Y-m-');
        $last = Payment::where('payment_number', 'like', "{$prefix}%")
            ->orderBy('payment_number', 'desc')
            ->first();

        if (!$last) {
            return $prefix . '0001';
        }

        $lastSeq = (int) substr($last->payment_number, -4);
        return $prefix . str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate Cash Flow Number (CF-YYYY-MM-0001)
     */
    public function generateCashFlowNumber(): string
    {
        $prefix = 'CF-' . now()->format('Y-m-');
        $last = CashFlow::where('cash_flow_number', 'like', "{$prefix}%")
            ->orderBy('cash_flow_number', 'desc')
            ->first();

        if (!$last) {
            return $prefix . '0001';
        }

        $lastSeq = (int) substr($last->cash_flow_number, -4);
        return $prefix . str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate Account Transfer Number (TRF-ACC-YYYY-MM-0001)
     */
    public function generateTransferNumber(): string
    {
        $prefix = 'TRF-ACC-' . now()->format('Y-m-');
        $last = AccountTransfer::where('transfer_number', 'like', "{$prefix}%")
            ->orderBy('transfer_number', 'desc')
            ->first();

        if (!$last) {
            return $prefix . '0001';
        }

        $lastSeq = (int) substr($last->transfer_number, -4);
        return $prefix . str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Process Account Payable (AP) Payment to Supplier
     */
    public function processPayablePayment(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            $receipt = PurchaseReceipt::findOrFail($data['purchase_receipt_id']);
            $account = Account::findOrFail($data['account_id']);
            $amount = (float) $data['amount'];

            if ($amount <= 0) {
                throw new \Exception('Nominal pembayaran harus lebih dari 0.');
            }

            // Deduct account balance
            $account->decrement('current_balance', $amount);

            $paymentNumber = $this->generatePaymentNumber();

            // Create Payment Record
            $payment = Payment::create([
                'payment_number' => $paymentNumber,
                'payment_type' => 'payable',
                'account_id' => $account->id,
                'payable_type' => PurchaseReceipt::class,
                'payable_id' => $receipt->id,
                'supplier_id' => $receipt->supplier_id,
                'customer_id' => null,
                'payment_date' => $data['payment_date'] ?? now()->toDateString(),
                'amount' => $amount,
                'payment_method' => $data['payment_method'] ?? 'cash',
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Update Purchase Receipt Paid Amount & Payment Status
            $newPaid = (float) $receipt->paid_amount + $amount;
            $receipt->paid_amount = $newPaid;

            if ($newPaid >= (float) $receipt->grand_total) {
                $receipt->payment_status = 'paid';
            } elseif ($newPaid > 0) {
                $receipt->payment_status = 'partial';
            }
            $receipt->save();

            // Record Cash Flow (Expense)
            CashFlow::create([
                'cash_flow_number' => $this->generateCashFlowNumber(),
                'account_id' => $account->id,
                'type' => 'expense',
                'category' => 'Pembayaran Hutang Supplier',
                'amount' => $amount,
                'transaction_date' => $payment->payment_date,
                'reference_type' => Payment::class,
                'reference_id' => $payment->id,
                'description' => "Bayar Hutang {$receipt->receipt_number} ({$receipt->supplier?->name})",
                'created_by' => auth()->id(),
            ]);

            return $payment;
        });
    }

    /**
     * Process Account Receivable (AR) Collection from Customer
     */
    public function processReceivableCollection(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            $sale = Sale::findOrFail($data['sale_id']);
            $account = Account::findOrFail($data['account_id']);
            $amount = (float) $data['amount'];

            if ($amount <= 0) {
                throw new \Exception('Nominal penerimaan harus lebih dari 0.');
            }

            // Increase account balance
            $account->increment('current_balance', $amount);

            $paymentNumber = $this->generatePaymentNumber();

            // Create Payment Record
            $payment = Payment::create([
                'payment_number' => $paymentNumber,
                'payment_type' => 'receivable',
                'account_id' => $account->id,
                'payable_type' => Sale::class,
                'payable_id' => $sale->id,
                'supplier_id' => null,
                'customer_id' => $sale->customer_id,
                'payment_date' => $data['payment_date'] ?? now()->toDateString(),
                'amount' => $amount,
                'payment_method' => $data['payment_method'] ?? 'cash',
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Update Sale Paid Amount & Payment Status
            $newPaid = (float) $sale->paid_amount + $amount;
            $sale->paid_amount = $newPaid;

            if ($newPaid >= (float) $sale->grand_total) {
                $sale->payment_status = 'paid';
            } elseif ($newPaid > 0) {
                $sale->payment_status = 'partial';
            }
            $sale->save();

            // Record Cash Flow (Income)
            CashFlow::create([
                'cash_flow_number' => $this->generateCashFlowNumber(),
                'account_id' => $account->id,
                'type' => 'income',
                'category' => 'Penerimaan Piutang Pelanggan',
                'amount' => $amount,
                'transaction_date' => $payment->payment_date,
                'reference_type' => Payment::class,
                'reference_id' => $payment->id,
                'description' => "Terima Piutang Invoice {$sale->invoice_number} ({$sale->customer?->name})",
                'created_by' => auth()->id(),
            ]);

            return $payment;
        });
    }

    /**
     * Record Direct Cash Flow (Income or Expense)
     */
    public function recordCashFlow(array $data): CashFlow
    {
        return DB::transaction(function () use ($data) {
            $account = Account::findOrFail($data['account_id']);
            $amount = (float) $data['amount'];
            $type = $data['type']; // income / expense

            if ($type === 'income') {
                $account->increment('current_balance', $amount);
            } else {
                $account->decrement('current_balance', $amount);
            }

            return CashFlow::create([
                'cash_flow_number' => $this->generateCashFlowNumber(),
                'account_id' => $account->id,
                'type' => $type,
                'category' => $data['category'],
                'amount' => $amount,
                'transaction_date' => $data['transaction_date'] ?? now()->toDateString(),
                'reference_type' => null,
                'reference_id' => null,
                'description' => $data['description'] ?? null,
                'created_by' => auth()->id(),
            ]);
        });
    }

    /**
     * Transfer Balance between Accounts
     */
    public function transferAccount(array $data): AccountTransfer
    {
        return DB::transaction(function () use ($data) {
            $fromAccount = Account::findOrFail($data['from_account_id']);
            $toAccount = Account::findOrFail($data['to_account_id']);
            $amount = (float) $data['amount'];
            $fee = (float) ($data['transfer_fee'] ?? 0);

            if ($fromAccount->id === $toAccount->id) {
                throw new \Exception('Akun asal dan akun tujuan transfer tidak boleh sama.');
            }

            // Deduct from source (amount + fee)
            $fromAccount->decrement('current_balance', ($amount + $fee));

            // Add to destination
            $toAccount->increment('current_balance', $amount);

            $transfer = AccountTransfer::create([
                'transfer_number' => $this->generateTransferNumber(),
                'from_account_id' => $fromAccount->id,
                'to_account_id' => $toAccount->id,
                'amount' => $amount,
                'transfer_fee' => $fee,
                'transfer_date' => $data['transfer_date'] ?? now()->toDateString(),
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Record Outflow
            CashFlow::create([
                'cash_flow_number' => $this->generateCashFlowNumber(),
                'account_id' => $fromAccount->id,
                'type' => 'expense',
                'category' => 'Transfer Antar Akun (Keluar)',
                'amount' => $amount + $fee,
                'transaction_date' => $transfer->transfer_date,
                'reference_type' => AccountTransfer::class,
                'reference_id' => $transfer->id,
                'description' => "Transfer ke {$toAccount->name}" . ($fee > 0 ? " (Biaya Admin: Rp " . number_format($fee, 0, ',', '.') . ")" : ""),
                'created_by' => auth()->id(),
            ]);

            // Record Inflow
            CashFlow::create([
                'cash_flow_number' => $this->generateCashFlowNumber(),
                'account_id' => $toAccount->id,
                'type' => 'income',
                'category' => 'Transfer Antar Akun (Masuk)',
                'amount' => $amount,
                'transaction_date' => $transfer->transfer_date,
                'reference_type' => AccountTransfer::class,
                'reference_id' => $transfer->id,
                'description' => "Terima transfer dari {$fromAccount->name}",
                'created_by' => auth()->id(),
            ]);

            return $transfer;
        });
    }
}
