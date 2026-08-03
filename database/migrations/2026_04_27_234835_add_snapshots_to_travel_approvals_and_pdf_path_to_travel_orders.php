<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add snapshot columns to travel_approvals
        Schema::table('travel_approvals', function (Blueprint $table) {
            $table->string('approver_name_snapshot')->nullable()->after('approver_id');
            $table->string('approver_position_snapshot')->nullable()->after('approver_name_snapshot');
            $table->string('approver_signature_snapshot')->nullable()->after('approver_position_snapshot');
        });

        // Add stored PDF path to travel_orders
        Schema::table('travel_orders', function (Blueprint $table) {
            $table->string('pdf_path')->nullable()->after('attachment');
        });
    }

    public function down(): void
    {
        Schema::table('travel_approvals', function (Blueprint $table) {
            $table->dropColumn([
                'approver_name_snapshot',
                'approver_position_snapshot',
                'approver_signature_snapshot',
            ]);
        });

        Schema::table('travel_orders', function (Blueprint $table) {
            $table->dropColumn('pdf_path');
        });
    }
};