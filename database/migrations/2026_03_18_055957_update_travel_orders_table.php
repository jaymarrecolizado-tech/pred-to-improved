<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('travel_orders', function (Blueprint $table) {
            $table->json('travel_location')->nullable()->after('end_date');

            // Only keep this if you REALLY want to delete old columns
            // $table->dropColumn(['origin', 'destination', 'distance']);
        });
    }

    public function down(): void
    {
        Schema::table('travel_orders', function (Blueprint $table) {
            $table->dropColumn('travel_location');
        });
    }
};