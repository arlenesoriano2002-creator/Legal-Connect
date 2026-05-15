<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LawOfficesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \DB::table('law_offices')->upsert([
            [
                'id' => 1,
                'lawyer' => 'Diffun Branch Lawyer',
                'address' => 'Diffun, Isabela',
                'law_office' => 'Diffun Branch',
                'timezone' => 'Asia/Manila',
                'max_capacity' => json_encode(['default' => 4]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'lawyer' => 'Cordon Branch Lawyer',
                'address' => 'Cordon, Isabela',
                'law_office' => 'Cordon Branch',
                'timezone' => 'Asia/Manila',
                'max_capacity' => json_encode(['default' => 4]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['id'], ['lawyer', 'address', 'law_office', 'timezone', 'max_capacity', 'updated_at']);
    }
}
