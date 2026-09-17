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
        Schema::create('ppob_products', function (Blueprint $table) {
            $table->id();
            $table->string('category', 50)->default('pulsa')->index(); // pulsa, paket_data, token_pln, ewallet, tagihan, other
            $table->string('provider', 100)->nullable()->index(); // Telkomsel, Indosat, XL, PLN, DANA, GoPay, OVO, ShopeePay
            $table->string('code', 50)->nullable()->unique();
            $table->string('name', 200);
            $table->decimal('cost_price', 15, 2)->default(0); // HPP / Modal saldo
            $table->decimal('selling_price', 15, 2)->default(0); // Harga jual kasir
            $table->foreignId('default_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->boolean('is_active')->default(true)->index();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppob_products');
    }
};
