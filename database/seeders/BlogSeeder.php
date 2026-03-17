<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Blog;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $blogs = [
            [
                'title' => 'NEP 2020 & Arts Programs Explained',
                'excerpt' => 'Understand how NEP 2020 is transforming Arts & Humanities programs in Indian universities, including flexibility in subjects and credit systems.',
                'image' => 'https://picsum.photos/400/250?random=1',
                'author' => 'Admin',
                'category' => 'NEP 2020',
                'content' => 'Full content about NEP 2020 transformation in Arts & Humanities programs.',
                'published_at' => '2025-09-30',
            ],
            [
                'title' => 'Best Engineering Universities in India',
                'excerpt' => 'Explore the top engineering universities in India based on placements, faculty, infrastructure, and global rankings.',
                'image' => 'https://picsum.photos/400/250?random=2',
                'author' => 'Editorial Team',
                'category' => 'Engineering',
                'content' => 'Detailed guide on the best engineering universities in India.',
                'published_at' => '2025-12-11',
            ],
            [
                'title' => 'Top Medical Colleges for NEET Aspirants',
                'excerpt' => 'A complete guide to the best medical colleges in India accepting NEET scores with high success and selection rates.',
                'image' => 'https://picsum.photos/400/250?random=3',
                'author' => 'Career Desk',
                'category' => 'Medical',
                'content' => 'Comprehensive guide to NEET and top medical colleges.',
                'published_at' => '2025-08-01',
            ],
        ];

        foreach ($blogs as $blog) {
            Blog::updateOrCreate(['title' => $blog['title']], $blog);
        }
    }
}
