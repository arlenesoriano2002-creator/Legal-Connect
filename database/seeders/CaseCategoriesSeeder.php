<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CaseCategory;
use Illuminate\Support\Facades\DB;

class CaseCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'category' => 'Family Law',
                'case_name' => 'Divorce'
            ],
            [
                'category' => 'Family Law',
                'case_name' => 'Child Custody'
            ],
            [
                'category' => 'Family Law',
                'case_name' => 'Adoption'
            ],
            [
                'category' => 'Personal Injury',
                'case_name' => 'Accidents'
            ],
            [
                'category' => 'Personal Injury',
                'case_name' => 'Medical Malpractice'
            ],
            [
                'category' => 'Real Estate',
                'case_name' => 'Property Transactions'
            ],
            [
                'category' => 'Real Estate',
                'case_name' => 'Landlord-Tenant Disputes'
            ],
            [
                'category' => 'Real Estate',
                'case_name' => 'Title Issues'
            ],
            [
                'category' => 'Business Law',
                'case_name' => 'Entity Formation'
            ],
            [
                'category' => 'Business Law',
                'case_name' => 'Contracts'
            ],
            [
                'category' => 'Business Law',
                'case_name' => 'Compliance'
            ],
            [
                'category' => 'Business Law',
                'case_name' => 'Business Litigation'
            ],
            [
                'category' => 'Criminal Law',
                'case_name' => 'Defends individuals or entities charged with crimes'
            ],
            [
                'category' => 'Human Rights Law',
                'case_name' => 'Advocates for the protection of fundamental human rights'
            ],
        ];

        foreach ($categories as $category) {
            CaseCategory::create($category);
        }
    }
}