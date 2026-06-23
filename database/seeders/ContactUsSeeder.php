<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContactUsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\ContactUsDetail::updateOrCreate(
            ['id' => 1],
            [
                'hero_title' => 'Big decisions need clarity.',
                'hero_subtitle' => 'Talk it through with Enrollzy.',
                'phone_general' => '+91 98765 43210',
                'phone_toll_free' => '1800-123-4567',
                'phone_international' => '+1 415 555 0123',
                'address_head_office' => 'Enrollzy Head Office,' . "\n" . 'Sector 62, Noida,' . "\n" . 'Uttar Pradesh 201309',
                'address_regional_office' => 'Enrollzy Regional Office,' . "\n" . 'Koramangala, Bangalore,' . "\n" . 'Karnataka 560034',
                'address_us_office' => '123 Tech Park,' . "\n" . 'San Francisco, CA 94105,' . "\n" . 'United States',
                'office_timings' => '10 AM to 7 PM',
                'email_queries' => 'queries@enrollzy.com',
                'email_support' => 'support@enrollzy.com',
                'co_founder_name' => 'John Doe',
                'co_founder_title' => 'Our Co-Founder',
                'co_founder_message' => 'This inbox exists for concerns that matter, without filters, without delays.',
                'co_founder_email' => 'founder@enrollzy.com',
                'co_founder_linkedin' => 'https://linkedin.com/',
                'co_founder_instagram' => 'https://instagram.com/',
                'map_embed_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d14008.114827184852!2d77.372551!3d28.628902!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x390ce5456ef36d9f%3A0x3b7191b12861361!2sSector%2062%2C%20Noida%2C%20Uttar%20Pradesh!5e0!3m2!1sen!2sin!4v1700000000000!5m2!1sen!2sin',
                'career_coach_title' => 'It\'s insurance against regret.',
                'career_coach_points' => [
                    "Unbiased target through right university",
                    "AI-powered match to your degree",
                    "Support till the end of your journey"
                ],
                'btn_book_session_url' => '#',
                'btn_talk_advisor_url' => '#'
            ]
        );
    }
}
