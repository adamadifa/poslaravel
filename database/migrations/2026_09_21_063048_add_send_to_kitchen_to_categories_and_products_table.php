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
        if (Schema::hasTable('categories') && ! Schema::hasColumn('categories', 'send_to_kitchen')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->boolean('send_to_kitchen')->default(true)->after('is_active');
            });
        }

        if (Schema::hasTable('products') && ! Schema::hasColumn('products', 'send_to_kitchen')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('send_to_kitchen')->nullable()->after('is_active'); // null means inherit from category
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('categories') && Schema::hasColumn('categories', 'send_to_kitchen')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('send_to_kitchen');
            });
        }

        if (Schema::hasTable('products') && Schema::hasColumn('products', 'send_to_kitchen')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('send_to_kitchen');
            });
        }
    }
};
