<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Alumni;

class AlumniSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $alumni = [
            [
                'name' => 'Rahul Sharma',
                'designation' => 'Senior Software Engineer',
                'company' => 'Google',
                'experience_years' => '5+ Years',
                'image' => null, // Will use UI Avatar fallback
                'status' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Priya Patel',
                'designation' => 'Product Manager',
                'company' => 'Microsoft',
                'experience_years' => '4 Years',
                'image' => null,
                'status' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Amit Singh',
                'designation' => 'Data Scientist',
                'company' => 'Amazon',
                'experience_years' => '3 Years',
                'image' => null,
                'status' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Sneha Gupta',
                'designation' => 'UX Designer',
                'company' => 'Adobe',
                'experience_years' => '6 Years',
                'image' => null,
                'status' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($alumni as $data) {
            Alumni::create($data);
        }
    }
}
