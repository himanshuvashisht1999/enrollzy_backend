<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VideoTestimonialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $videos = [
            [
                'name' => 'Ankit Sharma',
                'course' => 'MBA Graduate',
                'thumbnail' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=800&q=80',
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'sort_order' => 1
            ],
            [
                'name' => 'Pooja Verma',
                'course' => 'Data Science Expert',
                'thumbnail' => 'https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&w=800&q=80',
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'sort_order' => 2
            ],
            [
                'name' => 'Rohit Mehta',
                'course' => 'B.Tech Engineer',
                'thumbnail' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=800&q=80',
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'sort_order' => 3
            ],
            [
                'name' => 'Sneha Kapoor',
                'course' => 'MCA Professional',
                'thumbnail' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=800&q=80',
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'sort_order' => 4
            ],
        ];

        foreach ($videos as $video) {
            \App\Models\VideoTestimonial::create($video);
        }
    }
}
