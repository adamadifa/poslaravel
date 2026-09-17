<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambahkan kolom pendukung di tabel accounts untuk agen bank & PPOB
        Schema::table('accounts', function (Blueprint $table) {
            $table->decimal('alert_minimum_balance', 15, 2)->default(0)->after('current_balance');
            $table->string('account_holder')->nullable()->after('account_number');
        });

        // Ubah enum type accounts jika MySQL/MariaDB mendukung atau biarkan string/enum aman
        // Pada migration sebelumnya: $table->enum('type', ['cash', 'bank', 'other'])->default('cash');
        // Kita ubah menjadi string agar fleksibel mendukung bank_agent, ppob_provider, cash, bank, other
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('type', 50)->default('cash')->change();
        });

        // 2. Tambahkan kolom rekap transaksi agen pada cashier_shifts
        Schema::table('cashier_shifts', function (Blueprint $table) {
            $table->decimal('total_agent_cash_in', 15, 2)->default(0)->after('total_expenses');
            $table->decimal('total_agent_cash_out', 15, 2)->default(0)->after('total_agent_cash_in');
            $table->decimal('total_agent_profit', 15, 2)->default(0)->after('total_agent_cash_out');
        });

        // 3. Tabel Agent Transactions (Transaksi Agen Bank & Pulsa/PPOB)
        Schema::create('agent_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number', 50)->unique(); // AGT-2026-09-0001
            $table->foreignId('cashier_shift_id')->nullable()->constrained('cashier_shifts')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete(); // Akun Bank / Saldo PPOB
            $table->enum('category', ['bank_agent', 'ppob'])->default('bank_agent');
            $table->string('service_type', 50); // transfer, tarik_tunai, setor_tunai, pulsa, token_pln, ewallet, dll
            $table->string('destination_target', 100); // No Rek Tujuan / No HP / ID Pelanggan
            $table->string('destination_holder', 150)->nullable(); // Nama Penerima (jika transfer/cek rekening)
            $table->decimal('principal_amount', 15, 2)->default(0); // Nominal Pokok (misal: Transfer Rp 500.000)
            $table->decimal('cost_price', 15, 2)->default(0); // HPP / Biaya Bank Pemotong Saldo
            $table->decimal('admin_fee', 15, 2)->default(0); // Biaya Admin Jasa Toko
            $table->decimal('total_customer_paid', 15, 2)->default(0); // Total ditagihkan ke pelanggan
            $table->decimal('net_profit', 15, 2)->default(0); // admin_fee - cost_price (atau profit murni)
            $table->enum('payment_method', ['cash', 'non_cash', 'deduct_balance'])->default('cash');
            $table->string('reference_number', 100)->nullable(); // No SN Pulsa / No Ref EDC Bank
            $table->enum('status', ['success', 'pending', 'failed', 'cancelled'])->default('success');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 4. Tabel Account Mutations (Log Histori Detail Mutasi Saldo Setiap Akun)
        Schema::create('account_mutations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('mutation_type', ['credit', 'debit']); // debit = keluar/berkurang, credit = masuk/bertambah
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->string('reference_type', 100)->nullable(); // AgentTransaction, AccountTransfer, CashFlow, ManualAdjustment
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('description', 255)->nullable();
            $table->timestamps();

            $table->index(['account_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_mutations');
        Schema::dropIfExists('agent_transactions');

        Schema::table('cashier_shifts', function (Blueprint $table) {
            $table->dropColumn(['total_agent_cash_in', 'total_agent_cash_out', 'total_agent_profit']);
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['alert_minimum_balance', 'account_holder']);
        });
    }
};
