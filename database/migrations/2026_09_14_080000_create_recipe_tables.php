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
        // 1. Update product_type in products table to support raw_material
        Schema::table('products', function (Blueprint $table) {
            $table->string('product_type', 30)->default('standard')->change();
        });

        // 2. Create recipes table (Menu / Finished Goods -> Raw Materials BOM)
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('ingredient_product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity', 15, 4);
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->decimal('waste_percent', 5, 2)->default(0);
            $table->decimal('cost_estimate', 15, 2)->default(0);
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'ingredient_product_id']);
        });

        // 3. Create modifier_recipes table (Add-ons / Modifiers -> Raw Materials BOM)
        Schema::create('modifier_recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modifier_id')->constrained('modifiers')->cascadeOnDelete();
            $table->foreignId('ingredient_product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity', 15, 4);
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['modifier_id', 'ingredient_product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modifier_recipes');
        Schema::dropIfExists('recipes');
    }
};
