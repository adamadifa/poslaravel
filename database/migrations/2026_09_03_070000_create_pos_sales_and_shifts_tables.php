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
        // 1. Cashier Shifts (Sesi Shift Kasir)
        Schema::create('cashier_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->dateTime('opened_at');
            $table->dateTime('closed_at')->nullable();
            $table->decimal('starting_cash', 15, 2)->default(0); // Modal Awal Kasir
            $table->decimal('expected_cash', 15, 2)->default(0); // Kas Diharapkan (Modal + Penjualan Tunai)
            $table->decimal('closing_cash', 15, 2)->nullable();  // Kas Fisik Aktual saat Tutup
            $table->decimal('cash_difference', 15, 2)->default(0); // Selisih Kas (closing - expected)
            $table->decimal('total_sales', 15, 2)->default(0);   // Total Omset Penjualan Shift
            $table->integer('total_transactions')->default(0);   // Jumlah Struk Transaksi
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Sales (Header Transaksi Penjualan POS)
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique(); // INV-2026-09-0001
            $table->foreignId('cashier_shift_id')->nullable()->constrained('cashier_shifts')->nullOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Kasir
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->dateTime('sale_date');
            
            // Kalkulasi Nilai Transaksi
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('change_amount', 15, 2)->default(0); // Kembalian

            // Pembayaran & Status
            $table->enum('payment_method', ['cash', 'transfer', 'qris', 'credit', 'split'])->default('cash');
            $table->enum('payment_status', ['paid', 'partial', 'unpaid'])->default('paid');
            $table->enum('status', ['completed', 'void', 'draft'])->default('completed');
            $table->string('reference_number', 100)->nullable(); // Nomor Ref Transfer/QRIS
            $table->text('notes')->nullable();
            
            // Void Meta
            $table->foreignId('void_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('void_at')->nullable();
            $table->string('void_reason', 255)->nullable();

            $table->timestamps();
        });

        // 3. Sale Items (Detail Item Penjualan)
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->decimal('conversion_ratio', 15, 4)->default(1); // Konversi ke satuan dasar
            $table->decimal('quantity', 15, 4)->default(1);
            $table->decimal('unit_price', 15, 2)->default(0); // Harga satuan jual setelah diskon tier
            $table->decimal('unit_cost', 15, 2)->default(0);  // HPP dasar FIFO
            $table->decimal('discount_amount', 15, 2)->default(0); // Diskon item
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->timestamps();
        });

        // 4. Held Transactions (Transaksi Gantung / Hold Cart)
        Schema::create('held_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference_label', 100); // Misal: "Meja 4" atau "Bpk. Rahmat"
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->json('cart_payload'); // Simpan isi array cart & items
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('held_transactions');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('cashier_shifts');
    }
};
