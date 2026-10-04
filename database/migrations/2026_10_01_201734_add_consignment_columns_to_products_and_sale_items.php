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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_consignment')->default(false)->after('is_active');
            $table->foreignId('consignment_supplier_id')->nullable()->after('is_consignment')->constrained('suppliers')->nullOnDelete();
            $table->string('consignment_type', 30)->default('fixed_cost')->after('consignment_supplier_id'); // fixed_cost, percentage_commission
            $table->decimal('consignment_rate', 12, 2)->default(0)->after('consignment_type');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->boolean('is_consignment')->default(false)->after('notes');
            $table->foreignId('consignment_supplier_id')->nullable()->after('is_consignment')->constrained('suppliers')->nullOnDelete();
            $table->decimal('consignment_cost', 12, 2)->default(0)->after('consignment_supplier_id');
            $table->unsignedBigInteger('consignment_settlement_id')->nullable()->after('consignment_cost')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropForeign(['consignment_supplier_id']);
            $table->dropColumn([
                'is_consignment',
                'consignment_supplier_id',
                'consignment_cost',
                'consignment_settlement_id',
            ]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['consignment_supplier_id']);
            $table->dropColumn([
                'is_consignment',
                'consignment_supplier_id',
                'consignment_type',
                'consignment_rate',
            ]);
        });
    }
};
