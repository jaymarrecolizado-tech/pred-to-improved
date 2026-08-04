<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Normalize existing scalar vehicle values to JSON arrays before type change
        DB::table('travel_orders')
            ->whereNotNull('vehicle')
            ->where('vehicle', '!=', '')
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $decoded = json_decode($row->vehicle, true);
                    if (is_array($decoded)) {
                        continue;
                    }

                    DB::table('travel_orders')
                        ->where('id', $row->id)
                        ->update([
                            'vehicle' => json_encode([$row->vehicle]),
                        ]);
                }
            });

        Schema::table('travel_orders', function (Blueprint $table) {
            $table->json('vehicle')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('travel_orders')
            ->whereNotNull('vehicle')
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $decoded = json_decode($row->vehicle, true);
                    if (!is_array($decoded)) {
                        continue;
                    }

                    DB::table('travel_orders')
                        ->where('id', $row->id)
                        ->update([
                            'vehicle' => $decoded[0] ?? null,
                        ]);
                }
            });

        Schema::table('travel_orders', function (Blueprint $table) {
            $table->string('vehicle')->nullable()->change();
        });
    }
};
