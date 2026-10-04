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
        // 1. Penerimaan Barang Konsinyasi (Consignment Receipts)
        Schema::create('consignment_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number', 50)->unique();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('receipt_date');
            $table->string('status', 30)->default('received'); // received, cancelled
            $table->integer('total_items')->default(0);
            $table->decimal('total_quantity', 12, 4)->default(0);
            $table->decimal('total_estimated_value', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('consignment_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consignment_receipt_id')->constrained('consignment_receipts')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->decimal('quantity', 12, 4);
            $table->decimal('conversion_ratio', 12, 4)->default(1);
            $table->decimal('quantity_base', 12, 4);
            $table->decimal('consignment_cost', 12, 2)->default(0);
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        // 2. Settlement / Rekonsiliasi & Penagihan Konsinyasi
        Schema::create('consignment_settlements', function (Blueprint $table) {
            $table->id();
            $table->string('settlement_number', 50)->unique();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_sold_quantity', 12, 4)->default(0);
            $table->decimal('total_gross_sales', 14, 2)->default(0);
            $table->decimal('total_supplier_amount', 14, 2)->default(0);
            $table->decimal('total_store_commission', 14, 2)->default(0);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->string('payment_status', 30)->default('unpaid'); // unpaid, partial, paid
            $table->string('payment_method', 30)->nullable(); // cash, transfer, qris
            $table->foreignId('payment_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->date('payment_date')->nullable();
            $table->string('status', 30)->default('approved'); // draft, approved, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('consignment_settlement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consignment_settlement_id')->constrained('consignment_settlements')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->decimal('quantity_sold', 12, 4)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->decimal('consignment_cost', 12, 2)->default(0);
            $table->decimal('gross_sales', 14, 2)->default(0);
            $table->decimal('supplier_payable', 14, 2)->default(0);
            $table->decimal('store_commission', 14, 2)->default(0);
            $table->timestamps();
        });

        // 3. Retur Barang Konsinyasi (Consignment Returns)
        Schema::create('consignment_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number', 50)->unique();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('return_date');
            $table->integer('total_items')->default(0);
            $table->decimal('total_quantity', 12, 4)->default(0);
            $table->string('status', 30)->default('completed'); // draft, completed, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('consignment_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consignment_return_id')->constrained('consignment_returns')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->decimal('quantity', 12, 4);
            $table->decimal('conversion_ratio', 12, 4)->default(1);
            $table->decimal('quantity_base', 12, 4);
            $table->string('reason', 100)->nullable(); // expired, damaged, slow_moving, supplier_request
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consignment_return_items');
        Schema::dropIfExists('consignment_returns');
        Schema::dropIfExists('consignment_settlement_items');
        Schema::dropIfExists('consignment_settlements');
        Schema::dropIfExists('consignment_receipt_items');
        Schema::dropIfExists('consignment_receipts');
    }
};
