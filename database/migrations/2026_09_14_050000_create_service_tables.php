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
            $table->enum('product_type', ['standard', 'food', 'beverage', 'service', 'combo'])->default('standard')->after('name');
            $table->integer('duration_minutes')->nullable()->after('product_type'); // e.g. 30, 60, 90 mins for salon/service
            $table->boolean('is_bookable')->default(false)->after('duration_minutes');
            $table->boolean('require_staff_assignment')->default(false)->after('is_bookable');
            $table->integer('max_concurrent')->nullable()->after('require_staff_assignment');
        });

        Schema::create('service_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->enum('commission_type', ['none', 'fixed', 'percent'])->default('none');
            $table->decimal('commission_value', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'product_id']);
        });

        Schema::create('sale_item_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_item_id')->constrained('sale_items')->cascadeOnDelete();
            $table->foreignId('staff_user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['assigned', 'in_progress', 'completed', 'cancelled'])->default('assigned');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('duration_actual_minutes')->nullable();
            $table->decimal('commission_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_item_assignments');
        Schema::dropIfExists('service_staff');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'product_type',
                'duration_minutes',
                'is_bookable',
                'require_staff_assignment',
                'max_concurrent',
            ]);
        });
    }
};
