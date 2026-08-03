<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add FOR_REVISION to travel_orders status enum
        DB::statement("ALTER TABLE travel_orders MODIFY COLUMN status ENUM(
            'DRAFT',
            'SUBMITTED',
            'PENDING',
            'APPROVED',
            'REJECTED',
            'COMPLETED',
            'CANCELLED',
            'FOR_REVISION'
        ) NOT NULL DEFAULT 'DRAFT'");

        // Add FOR_REVISION to travel_approvals status enum
        DB::statement("ALTER TABLE travel_approvals MODIFY COLUMN status ENUM(
            'PENDING',
            'APPROVED',
            'REJECTED',
            'CANCELLED',
            'FOR_REVISION'
        ) NOT NULL DEFAULT 'PENDING'");

        // Add revision columns to travel_approvals
        Schema::table('travel_approvals', function (Blueprint $table) {
            $table->text('revision_reason')->nullable()->after('reject_reason');
            $table->timestamp('revised_at')->nullable()->after('rejected_at');
        });

        // Add revision columns to travel_orders
        Schema::table('travel_orders', function (Blueprint $table) {
            $table->text('revision_reason')->nullable()->after('cancel_reason');
            $table->timestamp('revised_at')->nullable()->after('cancelled_at');
        });
    }

    public function down(): void
    {
        Schema::table('travel_approvals', function (Blueprint $table) {
            $table->dropColumn(['revision_reason', 'revised_at']);
        });

        Schema::table('travel_orders', function (Blueprint $table) {
            $table->dropColumn(['revision_reason', 'revised_at']);
        });

        DB::statement("ALTER TABLE travel_approvals MODIFY COLUMN status ENUM(
            'PENDING',
            'APPROVED',
            'REJECTED',
            'CANCELLED'
        ) NOT NULL DEFAULT 'PENDING'");

        DB::statement("ALTER TABLE travel_orders MODIFY COLUMN status ENUM(
            'DRAFT',
            'SUBMITTED',
            'PENDING',
            'APPROVED',
            'REJECTED',
            'COMPLETED',
            'CANCELLED'
        ) NOT NULL DEFAULT 'DRAFT'");
    }
};