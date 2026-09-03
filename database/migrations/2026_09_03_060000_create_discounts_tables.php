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
        // 1. Discounts (Header Program Diskon & Promo)
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('code', 50)->nullable()->unique();
            $table->enum('type', [
                'percentage_item',      // Diskon % per item tertentu
                'fixed_item',           // Potongan Rp per item tertentu
                'percentage_invoice',   // Diskon % total transaksi belanja
                'fixed_invoice',        // Potongan Rp total transaksi belanja
                'buy_x_get_y'           // Beli X produk A gratis/diskon Y produk B
            ]);
            $table->decimal('value', 15, 2)->default(0); // Nilai % atau nominal Rp
            $table->decimal('min_order_amount', 15, 2)->nullable(); // Syarat minimal belanja (Rp)
            $table->decimal('max_discount_amount', 15, 2)->nullable(); // Batas maksimal diskon nominal
            
            // Kolom pendukung BOGO (Buy X Get Y)
            $table->decimal('buy_qty', 15, 4)->nullable(); // Jumlah beli (X)
            $table->decimal('get_qty', 15, 4)->nullable(); // Jumlah gratis/diskon (Y)
            $table->foreignId('reward_product_id')->nullable()->constrained('products')->nullOnDelete();

            // Segmentasi & Periode
            $table->foreignId('customer_group_id')->nullable()->constrained('customer_groups')->nullOnDelete();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('is_combinable')->default(false); // Bisa digabung dengan promo lain
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Discount Items (Daftar Produk yang Memenuhi Syarat Promo)
        Schema::create('discount_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_id')->constrained('discounts')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['discount_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_items');
        Schema::dropIfExists('discounts');
    }
};
