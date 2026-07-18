<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $facilities = [
            ['name' => 'Alumni Associations', 'icon' => 'fas fa-graduation-cap'],
            ['name' => 'Auditorium', 'icon' => 'fas fa-bullhorn'],
            ['name' => 'Boys Hostel', 'icon' => 'fas fa-male'],
            ['name' => 'Girls Hostel', 'icon' => 'fas fa-female'],
            ['name' => 'I.T Infrastructure', 'icon' => 'fas fa-desktop'],
            ['name' => 'Cafeteria', 'icon' => 'fas fa-coffee'],
            ['name' => 'Classrooms', 'icon' => 'fas fa-chalkboard'],
            ['name' => 'Laboratories', 'icon' => 'fas fa-flask'],
            ['name' => 'Library', 'icon' => 'fas fa-book'],
            ['name' => 'Medical/Hospital', 'icon' => 'fas fa-first-aid'],
            ['name' => 'Guest Room/Waiting Room', 'icon' => 'fas fa-bed'],
            ['name' => 'Gym', 'icon' => 'fas fa-dumbbell'],
            ['name' => 'Sports', 'icon' => 'fas fa-basketball-ball'],
            ['name' => 'Swimming Pool', 'icon' => 'fas fa-swimmer'],
            ['name' => 'Transport Facility', 'icon' => 'fas fa-bus'],
            ['name' => 'Wifi', 'icon' => 'fas fa-wifi'],
        ];

        foreach ($facilities as $facility) {
            \App\Models\Facility::firstOrCreate(
                ['name' => $facility['name']],
                ['icon' => $facility['icon'], 'status' => 1]
            );
        }
    }
}
