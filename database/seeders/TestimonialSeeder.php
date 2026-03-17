<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Testimonial;

class TestimonialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $testimonials = [
            [
                'name' => 'Rahul Singh',
                'role' => 'MBA Student',
                'content' => 'The counselling session was eye-opening. I finally found the right specialization for my career growth.',
                'rating' => 5,
                'image' => 'https://i.pravatar.cc/150?u=rahul',
            ],
            [
                'name' => 'Priya Sharma',
                'role' => 'Career Changer',
                'content' => 'Switching from Marketing to Data Science seemed impossible until I spoke with the experts here.',
                'rating' => 5,
                'image' => 'https://i.pravatar.cc/150?u=priya',
            ],
            [
                'name' => 'Amit Kumar',
                'role' => 'Final Year Student',
                'content' => 'Detailed comparison of universities helped me save both time and money. Highly recommended!',
                'rating' => 4,
                'image' => 'https://i.pravatar.cc/150?u=amit',
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::updateOrCreate(['name' => $testimonial['name']], $testimonial);
        }
    }
}
