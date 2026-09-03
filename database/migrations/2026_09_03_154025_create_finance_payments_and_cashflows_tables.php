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
        // 1. Payments Table (AP Payment & AR Collection)
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->enum('payment_type', ['payable', 'receivable']); // payable (Hutang Supplier) / receivable (Piutang Pelanggan)
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->nullableMorphs('payable'); // purchase_receipts (AP) or sales (AR)
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method')->default('cash'); // cash, transfer, check, other
            $table->string('reference_number')->nullable(); // No Giro / No Bukti Transfer
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 2. Cash Flows Table (Buku Kas Masuk / Keluar / Biaya)
        Schema::create('cash_flows', function (Blueprint $table) {
            $table->id();
            $table->string('cash_flow_number')->unique();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->enum('type', ['income', 'expense']); // Masuk (Income) / Keluar (Expense)
            $table->string('category'); // Penjualan, Penerimaan Piutang, Modal, Pembelian, Bayar Hutang, Gaji, Listrik, Sewa, Transport, dll
            $table->decimal('amount', 15, 2);
            $table->date('transaction_date');
            $table->nullableMorphs('reference'); // Sale, PurchaseReceipt, Payment, AccountTransfer, dll
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 3. Account Transfers Table (Mutasi Antar Kas / Bank)
        Schema::create('account_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique();
            $table->foreignId('from_account_id')->constrained('accounts')->cascadeOnDelete();
            $table->foreignId('to_account_id')->constrained('accounts')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->decimal('transfer_fee', 15, 2)->default(0); // Biaya admin transfer
            $table->date('transfer_date');
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_transfers');
        Schema::dropIfExists('cash_flows');
        Schema::dropIfExists('payments');
    }
};
