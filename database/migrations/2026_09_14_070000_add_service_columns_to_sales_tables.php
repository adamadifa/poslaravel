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
            $table->foreignId('service_booking_id')->nullable()->after('waiter_id')->constrained('service_bookings')->nullOnDelete();
            $table->foreignId('assigned_staff_id')->nullable()->after('service_booking_id')->constrained('users')->nullOnDelete();
            $table->enum('service_status', ['waiting', 'in_progress', 'completed'])->nullable()->after('assigned_staff_id');
            $table->timestamp('service_started_at')->nullable()->after('service_status');
            $table->timestamp('service_completed_at')->nullable()->after('service_started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['service_booking_id']);
            $table->dropForeign(['assigned_staff_id']);
            $table->dropColumn([
                'service_booking_id',
                'assigned_staff_id',
                'service_status',
                'service_started_at',
                'service_completed_at',
            ]);
        });
    }
};
