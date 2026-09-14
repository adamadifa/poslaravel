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
        Schema::table('sales', function (Blueprint $table) {
            $table->enum('service_type', ['dine_in', 'take_away'])->nullable()->after('payment_status');
            $table->foreignId('dining_table_id')->nullable()->after('service_type')->constrained('dining_tables')->nullOnDelete();
            $table->string('queue_number')->nullable()->after('dining_table_id'); // e.g. "A-001"
            $table->enum('order_status', ['new_order', 'preparing', 'ready', 'served', 'completed'])->default('completed')->after('queue_number');
            $table->integer('guest_count')->nullable()->after('order_status');
            $table->decimal('service_charge', 15, 2)->default(0)->after('grand_total');
            $table->foreignId('waiter_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('subtotal'); // item custom notes like "tidak pakai daun bawang"
            $table->enum('item_status', ['pending', 'preparing', 'ready', 'served'])->default('pending')->after('notes');
            $table->timestamp('prepared_at')->nullable()->after('item_status');
            $table->timestamp('served_at')->nullable()->after('prepared_at');
        });

        Schema::create('queue_counters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->date('counter_date');
            $table->string('prefix', 5)->default('A');
            $table->integer('last_number')->default(0);
            $table->timestamps();

            $table->unique(['warehouse_id', 'counter_date', 'prefix']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queue_counters');

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn(['notes', 'item_status', 'prepared_at', 'served_at']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['dining_table_id']);
            $table->dropForeign(['waiter_id']);
            $table->dropColumn([
                'service_type',
                'dining_table_id',
                'queue_number',
                'order_status',
                'guest_count',
                'service_charge',
                'waiter_id',
            ]);
        });
    }
};
