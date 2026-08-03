<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE travel_approvals MODIFY COLUMN status ENUM(
            'PENDING',
            'APPROVED',
            'REJECTED',
            'CANCELLED'
        ) NOT NULL DEFAULT 'PENDING'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE travel_approvals MODIFY COLUMN status ENUM(
            'PENDING',
            'APPROVED',
            'REJECTED'
        ) NOT NULL DEFAULT 'PENDING'");
    }
};