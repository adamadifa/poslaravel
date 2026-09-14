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
        Schema::create('dining_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->string('table_number');
            $table->string('area')->default('Indoor'); // Indoor, Outdoor, Rooftop, VIP, etc.
            $table->integer('capacity')->default(4);
            $table->enum('status', ['available', 'occupied', 'reserved', 'maintenance'])->default('available');
            $table->foreignId('current_sale_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['warehouse_id', 'status']);
            $table->unique(['warehouse_id', 'table_number']);
        });

        Schema::create('table_merges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('primary_table_id')->constrained('dining_tables')->cascadeOnDelete();
            $table->foreignId('merged_table_id')->constrained('dining_tables')->cascadeOnDelete();
            $table->foreignId('sale_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->timestamp('merged_at')->useCurrent();
            $table->timestamp('unmerged_at')->nullable();
            $table->timestamps();
        });

        Schema::create('table_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dining_table_id')->constrained('dining_tables')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('guest_name');
            $table->string('guest_phone')->nullable();
            $table->integer('guest_count')->default(2);
            $table->date('reservation_date');
            $table->time('reservation_time');
            $table->integer('duration_minutes')->default(120);
            $table->enum('status', ['pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show'])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['warehouse_id', 'reservation_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_reservations');
        Schema::dropIfExists('table_merges');
        Schema::dropIfExists('dining_tables');
    }
};
