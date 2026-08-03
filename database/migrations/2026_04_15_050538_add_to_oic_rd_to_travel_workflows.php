<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('travel_workflows', function (Blueprint $table) {
            $table->boolean('to_oic_rd')->default(false)->after('to_approve');
        });
    }

    public function down(): void
    {
        Schema::table('travel_workflows', function (Blueprint $table) {
            $table->dropColumn('to_oic_rd');
        });
    }
};