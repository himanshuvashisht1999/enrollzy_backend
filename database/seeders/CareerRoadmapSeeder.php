<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CareerRoadmapSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Early stage — foundation years' => ['Class 5', 'Class 6', 'Class 7', 'Class 8'],
            'Decision years' => ['Class 9', 'Class 10'],
            'Critical years' => ['Class 11', 'Class 12'],
            'Higher education' => ['Graduation', 'Post graduation']
        ];

        foreach ($categories as $categoryName => $stages) {
            $category = \App\Models\CareerRoadmapCategory::firstOrCreate(
                ['name' => $categoryName],
                ['status' => 1]
            );

            foreach ($stages as $stageName) {
                \App\Models\CareerRoadmapStage::firstOrCreate(
                    [
                        'category_id' => $category->id,
                        'title' => $stageName
                    ],
                    [
                        'status' => 1
                    ]
                );
            }
        }
    }
}
