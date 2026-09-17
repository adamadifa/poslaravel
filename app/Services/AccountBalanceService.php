<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountMutation;
use App\Models\AccountTransfer;
use Illuminate\Support\Facades\DB;

class AccountBalanceService
{
    /**
     * Check whether an account has sufficient balance.
     */
    public function hasSufficientBalance(Account|int $account, float $amount): bool
    {
        $accountModel = $account instanceof Account ? $account : Account::findOrFail($account);

        return (float) $accountModel->current_balance >= $amount;
    }

    /**
     * Credit balance to account (Menambah saldo akun).
     */
    public function credit(
        Account|int $account,
        float $amount,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $description = null,
        ?int $userId = null
    ): AccountMutation {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Nominal penambahan saldo harus lebih dari 0.');
        }

        return DB::transaction(function () use ($account, $amount, $referenceType, $referenceId, $description, $userId) {
            $accountModel = $account instanceof Account
                ? Account::lockForUpdate()->findOrFail($account->id)
                : Account::lockForUpdate()->findOrFail($account);

            $balanceBefore = (float) $accountModel->current_balance;
            $balanceAfter = $balanceBefore + $amount;

            $accountModel->current_balance = $balanceAfter;
            $accountModel->save();

            return AccountMutation::create([
                'account_id' => $accountModel->id,
                'user_id' => $userId ?? auth()->id(),
                'mutation_type' => 'credit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
            ]);
        });
    }

    /**
     * Debit balance from account (Mengurangi saldo akun).
     */
    public function debit(
        Account|int $account,
        float $amount,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $description = null,
        ?int $userId = null,
        bool $allowNegative = false
    ): AccountMutation {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Nominal pengurangan saldo harus lebih dari 0.');
        }

        return DB::transaction(function () use ($account, $amount, $referenceType, $referenceId, $description, $userId, $allowNegative) {
            $accountModel = $account instanceof Account
                ? Account::lockForUpdate()->findOrFail($account->id)
                : Account::lockForUpdate()->findOrFail($account);

            $balanceBefore = (float) $accountModel->current_balance;

            if (! $allowNegative && $balanceBefore < $amount) {
                throw new \Exception("Saldo akun '{$accountModel->name}' tidak mencukupi. (Sisa Saldo: Rp ".number_format($balanceBefore, 0, ',', '.').', Dibutuhkan: Rp '.number_format($amount, 0, ',', '.').')');
            }

            $balanceAfter = $balanceBefore - $amount;
            $accountModel->current_balance = $balanceAfter;
            $accountModel->save();

            return AccountMutation::create([
                'account_id' => $accountModel->id,
                'user_id' => $userId ?? auth()->id(),
                'mutation_type' => 'debit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
            ]);
        });
    }

    /**
     * Transfer funds between accounts (misal: Bank Operasional -> Saldo PPOB atau EDC BRILink).
     */
    public function transferBetweenAccounts(array $data): AccountTransfer
    {
        return DB::transaction(function () use ($data) {
            $fromAccount = Account::lockForUpdate()->findOrFail($data['from_account_id']);
            $toAccount = Account::lockForUpdate()->findOrFail($data['to_account_id']);
            $amount = (float) $data['amount'];
            $fee = (float) ($data['transfer_fee'] ?? 0);
            $userId = $data['user_id'] ?? auth()->id();

            if ($fromAccount->id === $toAccount->id) {
                throw new \Exception('Akun asal dan akun tujuan transfer tidak boleh sama.');
            }

            $totalDeduction = $amount + $fee;
            if ((float) $fromAccount->current_balance < $totalDeduction) {
                throw new \Exception("Saldo akun '{$fromAccount->name}' tidak mencukupi untuk transfer.");
            }

            // Generate transfer number
            $prefix = 'TRF-ACC-'.now()->format('Y-m-');
            $last = AccountTransfer::where('transfer_number', 'like', "{$prefix}%")
                ->orderBy('transfer_number', 'desc')
                ->first();
            $lastSeq = $last ? (int) substr($last->transfer_number, -4) : 0;
            $transferNumber = $prefix.str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);

            $transfer = AccountTransfer::create([
                'transfer_number' => $transferNumber,
                'from_account_id' => $fromAccount->id,
                'to_account_id' => $toAccount->id,
                'amount' => $amount,
                'transfer_fee' => $fee,
                'transfer_date' => $data['transfer_date'] ?? now()->toDateString(),
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            // Mutasi Debit pada akun asal
            $this->debit(
                $fromAccount,
                $totalDeduction,
                AccountTransfer::class,
                $transfer->id,
                "Transfer ke {$toAccount->name}".($fee > 0 ? ' (Fee: Rp '.number_format($fee, 0, ',', '.').')' : ''),
                $userId
            );

            // Mutasi Credit pada akun tujuan
            $this->credit(
                $toAccount,
                $amount,
                AccountTransfer::class,
                $transfer->id,
                "Penerimaan transfer dari {$fromAccount->name}",
                $userId
            );

            return $transfer;
        });
    }

    /**
     * Get real-time summary of all active accounts categorized.
     */
    public function getAccountsSummary(): array
    {
        $accounts = Account::where('is_active', true)->orderBy('name')->get();

        $bankAgents = $accounts->where('type', 'bank_agent')->values();
        $ppobProviders = $accounts->where('type', 'ppob_provider')->values();
        $cashAccounts = $accounts->where('type', 'cash')->values();
        $bankAccounts = $accounts->where('type', 'bank')->values();

        return [
            'all' => $accounts,
            'bank_agents' => $bankAgents,
            'ppob_providers' => $ppobProviders,
            'cash_accounts' => $cashAccounts,
            'bank_accounts' => $bankAccounts,
            'total_bank_agent_balance' => $bankAgents->sum('current_balance'),
            'total_ppob_balance' => $ppobProviders->sum('current_balance'),
            'total_cash_balance' => $cashAccounts->sum('current_balance'),
            'total_bank_balance' => $bankAccounts->sum('current_balance'),
            'total_balance' => $accounts->sum('current_balance'),
        ];
    }
}
