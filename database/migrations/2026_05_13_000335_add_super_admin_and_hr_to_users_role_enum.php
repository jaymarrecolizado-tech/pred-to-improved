<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add super_admin and hr to the role enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','employee','super_admin','hr') NOT NULL DEFAULT 'employee'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','employee') NOT NULL DEFAULT 'employee'");
    }
};