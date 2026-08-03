<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modify enum to add CANCELLED
        DB::statement("ALTER TABLE travel_orders MODIFY COLUMN status ENUM(
            'DRAFT',
            'SUBMITTED',
            'PENDING',
            'APPROVED',
            'REJECTED',
            'COMPLETED',
            'CANCELLED'
        ) NOT NULL DEFAULT 'DRAFT'");

        // Add cancel reason and timestamp
        Schema::table('travel_orders', function (Blueprint $table) {
            $table->text('cancel_reason')->nullable()->after('reject_reason');
            $table->timestamp('cancelled_at')->nullable()->after('rejected_at');
        });
    }

    public function down(): void
    {
        Schema::table('travel_orders', function (Blueprint $table) {
            $table->dropColumn(['cancel_reason', 'cancelled_at']);
        });

        DB::statement("ALTER TABLE travel_orders MODIFY COLUMN status ENUM(
            'DRAFT',
            'SUBMITTED',
            'PENDING',
            'APPROVED',
            'REJECTED',
            'COMPLETED'
        ) NOT NULL DEFAULT 'DRAFT'");
    }
};