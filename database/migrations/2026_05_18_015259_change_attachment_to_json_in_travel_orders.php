<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Wrap existing single attachment strings into JSON arrays
        DB::statement("
            UPDATE travel_orders
            SET attachment = JSON_ARRAY(attachment)
            WHERE attachment IS NOT NULL
            AND attachment != ''
            AND attachment NOT LIKE '[%'
        ");

        Schema::table('travel_orders', function (Blueprint $table) {
            $table->json('attachment')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('travel_orders', function (Blueprint $table) {
            $table->string('attachment')->nullable()->change();
        });
    }
};