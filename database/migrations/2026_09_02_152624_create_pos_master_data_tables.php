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
        // 1. Kategori Produk (Hierarki Parent-Child)
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 2. Satuan (Units)
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50); // Pcs, Pak, Dus, Karton, Botol, Liter, Kg
            $table->string('short_name', 15); // pcs, pak, dus, krt, btl, ltr, kg
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Customer Groups (Grup Pelanggan: Umum, Member, Reseller, Grosir)
        Schema::create('customer_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 4. Customers
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 200);
            $table->foreignId('customer_group_id')->nullable()->constrained('customer_groups')->nullOnDelete();
            $table->string('phone', 25)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('tax_id', 50)->nullable();
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->integer('loyalty_points')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 5. Suppliers
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 200);
            $table->string('contact_person', 100)->nullable();
            $table->string('phone', 25)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('tax_id', 50)->nullable();
            $table->integer('payment_term_days')->default(0); // Jatuh tempo (hari)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 6. Warehouses / Outlets (Multi-Cabang)
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->text('address')->nullable();
            $table->string('phone', 25)->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 7. Products (Master Produk)
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('base_unit_id')->constrained('units')->cascadeOnDelete();
            $table->string('code', 50)->unique();
            $table->string('barcode', 50)->nullable()->unique();
            $table->string('name', 200);
            $table->string('slug', 200)->nullable();
            $table->string('brand', 100)->nullable();
            $table->text('description')->nullable();
            $table->decimal('purchase_price', 15, 2)->default(0); // HPP dasar (satuan terkecil)
            $table->decimal('selling_price', 15, 2)->default(0);  // Harga jual dasar
            $table->decimal('min_stock', 15, 4)->default(0);      // Reorder point
            $table->decimal('max_stock', 15, 4)->nullable();
            $table->enum('tax_type', ['none', 'inclusive', 'exclusive'])->default('none');
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->boolean('has_expiry')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('image_path', 255)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // 8. Multi Barcodes
        Schema::create('product_barcodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->string('barcode', 50)->unique();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        // 9. Konversi Satuan (Multi-Satuan)
        Schema::create('unit_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('from_unit_id')->constrained('units')->cascadeOnDelete();
            $table->foreignId('to_unit_id')->constrained('units')->cascadeOnDelete();
            $table->decimal('conversion_value', 15, 4); // Misal: 1 Karton = 40 Pcs -> conversion_value = 40
            $table->timestamps();
        });

        // 10. Daftar Harga per Satuan (Price Lists)
        Schema::create('price_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->decimal('selling_price', 15, 2)->default(0);
            $table->timestamps();
            $table->unique(['product_id', 'unit_id']);
        });

        // 11. Harga Berjenjang (Tiered Pricing by Qty / Customer Group)
        Schema::create('tiered_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->foreignId('customer_group_id')->nullable()->constrained('customer_groups')->nullOnDelete();
            $table->decimal('min_qty', 15, 4)->default(1);
            $table->decimal('max_qty', 15, 4)->nullable();
            $table->decimal('price', 15, 2);
            $table->boolean('is_active')->default(true);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });

        // 12. Stok Produk per Gudang
        Schema::create('product_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->decimal('quantity', 15, 4)->default(0); // dalam satuan dasar terkecil
            $table->decimal('reserved_qty', 15, 4)->default(0);
            $table->timestamps();
            $table->unique(['product_id', 'warehouse_id']);
        });

        // 13. Kartu Stok (Mutasi Stok - Append Only)
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->string('reference_type', 60); // PurchaseReceipt, Sale, Opname, Transfer, Adjustment
            $table->unsignedBigInteger('reference_id');
            $table->enum('type', ['in', 'out']);
            $table->decimal('quantity', 15, 4);
            $table->decimal('unit_cost', 15, 2)->default(0); // HPP unit FIFO
            $table->decimal('before_stock', 15, 4);
            $table->decimal('after_stock', 15, 4);
            $table->string('description', 255)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('product_stocks');
        Schema::dropIfExists('tiered_prices');
        Schema::dropIfExists('price_lists');
        Schema::dropIfExists('unit_conversions');
        Schema::dropIfExists('product_barcodes');
        Schema::dropIfExists('products');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('customer_groups');
        Schema::dropIfExists('units');
        Schema::dropIfExists('categories');
    }
};
