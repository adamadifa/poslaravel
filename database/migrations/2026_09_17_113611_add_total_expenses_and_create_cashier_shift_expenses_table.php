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
        Schema::table('cashier_shifts', function (Blueprint $table) {
            $table->decimal('total_expenses', 15, 2)->default(0)->after('total_transactions');
        });

        Schema::create('cashier_shift_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cashier_shift_id')->constrained('cashier_shifts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('category', 100)->default('Operasional');
            $table->text('notes')->nullable();
            $table->dateTime('expense_date');
            $table->foreignId('cash_flow_id')->nullable()->constrained('cash_flows')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashier_shift_expenses');

        Schema::table('cashier_shifts', function (Blueprint $table) {
            $table->dropColumn('total_expenses');
        });
    }
};
