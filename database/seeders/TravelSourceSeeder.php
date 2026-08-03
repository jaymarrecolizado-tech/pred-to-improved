<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TravelSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('travel_sources')->insert([
            [
                'name' => 'CYBERSECURITY',
                'vehicles' => json_encode([
                    ['car_name' => 'Isuzi Crosswind', 'plate_number' => 'SHS 987'],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'PNPKI',
                'vehicles' => json_encode([
                    ['car_name' => 'Nissan Patrol', 'plate_number' => 'SDF 424'],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'TECH4ED-DTC',
                'vehicles' => json_encode([
                    ['car_name' => 'Nissan Patrol', 'plate_number' => 'SBY 225'],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'ILCDB',
                'vehicles' => json_encode([
                    ['car_name' => 'Ford Ranger', 'plate_number' => 'CBI 8522'],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'eLGU-EGovSD',
                'vehicles' => json_encode([
                    ['car_name' => 'Toyota Hilux', 'plate_number' => 'NJA 8967'],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'FREE WI-FI',
                'vehicles' => json_encode([
                    ['car_name' => 'GECS-HUB (Hino 500)', 'plate_number' => 'SNJ 8786'],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'GASS',
                'vehicles' => json_encode([
                    ['car_name' => 'GECS-Dispatch (D-Max)', 'plate_number' => 'SNA 6213'],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'GECS',
                'vehicles' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'IIDB',
                'vehicles' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'GOVNET',
                'vehicles' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'NIPPSB',
                'vehicles' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'IIDB',
                'vehicles' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
