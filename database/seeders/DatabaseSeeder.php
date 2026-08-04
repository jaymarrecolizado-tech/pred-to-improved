<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(DivisionSeeder::class);
        $this->call(EmployeeSeeder::class);
        $this->call(TravelSourceSeeder::class);
        $this->call(UserGuideSeeder::class);
    }
}
