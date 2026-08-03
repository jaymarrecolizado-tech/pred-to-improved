<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Division;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisions = [
            [
                'name' => 'Office of the Regional Director',
                'code' => 'ORD',
                'description' => 'The central office responsible for providing leadership, direction, and oversight for all regional programs and initiatives.',
            ],
            [
                'name' => 'Admin and Finance Division',
                'code' => 'AFD',
                'description' => 'Handles administrative services, financial management, budgeting, and resource allocation for efficient operations.',
            ],
            [
                'name' => 'Technical and Operations Division',
                'code' => 'TOD',
                'description' => 'Provides technical expertise and oversees operational activities, ensuring program implementation and compliance.',
            ],
            [
                'name' => 'Batanes Provincial Office',
                'code' => 'BPO',
                'description' => 'The provincial extension office serving Batanes, coordinating local programs and administrative functions.',
            ],
            [
                'name' => 'Cagayan Provincial Office',
                'code' => 'CPO',
                'description' => 'Responsible for managing operations, support services, and program execution within the province of Cagayan.',
            ],
            [
                'name' => 'Isabela-Cauayan Provincial Office',
                'code' => 'ICPO',
                'description' => 'Serves as the local office in Cauayan, Isabela, providing administrative and technical support for regional projects.',
            ],
            [
                'name' => 'Isabela-Santiago Provincial Office',
                'code' => 'ISPO',
                'description' => 'Provincial office in Santiago, Isabela, managing outreach, support, and program implementation across its jurisdiction.',
            ],
            [
                'name' => 'Quirino Provincial Office',
                'code' => 'QPO',
                'description' => 'Supports the Quirino area by overseeing projects, operations, and providing administrative assistance.',
            ],
            [
                'name' => 'Nueva Vizcaya-Bayombong Office',
                'code' => 'NVBO',
                'description' => 'The provincial office located in Bayombong, Nueva Vizcaya, focused on program delivery and local operations.',
            ],
        ];

        foreach ($divisions as $division) {
            Division::updateOrCreate(
                ['code' => $division['code']],
                $division
            );
        }
    }
}
