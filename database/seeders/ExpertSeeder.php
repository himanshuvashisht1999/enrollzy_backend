<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Expert;

class ExpertSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $experts = [
            [
                'name' => 'Rohit Gupta',
                'role' => 'Education Expert',
                'degree' => 'MBA',
                'exp' => '8 years experience',
                'rating' => '4.8',
                'count' => '2423+ Counselling',
                'img' => 'https://images.unsplash.com/photo-1603415526960-f7e0328c63b1?auto=format&fit=crop&w=400&q=70',
            ],
            [
                'name' => 'Sarthak Garg',
                'role' => 'Sr. Mentor',
                'degree' => 'MCA',
                'exp' => '6 years experience',
                'rating' => '4.7',
                'count' => '2329+ Counselling',
                'img' => 'https://images.unsplash.com/photo-1595152772835-219674b2a8a6?auto=format&fit=crop&w=400&q=70',
            ],
            [
                'name' => 'Sakshi Rajput',
                'role' => 'Sr. Mentor',
                'degree' => 'M.Com',
                'exp' => '5 years experience',
                'rating' => '4.5',
                'count' => '1724+ Counselling',
                'img' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&w=400&q=70',
            ],
            [
                'name' => 'Manish Thapiyal',
                'role' => 'Sr. Mentor',
                'degree' => 'MA',
                'exp' => '6 years experience',
                'rating' => '4.6',
                'count' => '1943+ Counselling',
                'img' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=400&q=70',
            ],
        ];

        foreach ($experts as $expert) {
            Expert::updateOrCreate(['name' => $expert['name']], $expert);
        }
    }
}
