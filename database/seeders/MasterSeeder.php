<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterSeeder extends Seeder
{
    public function run()
    {
        // 1. Program Levels
        $levels = ['Undergraduate (UG)', 'Postgraduate (PG)', 'Diploma', 'Ph.D', 'Certificate'];
        foreach ($levels as $index => $item) {
            \App\Models\ProgramLevel::firstOrCreate(['title' => $item], ['sort_order' => $index, 'status' => true]);
        }

        // 2. Program Types
        $types = ['Full Time', 'Part Time', 'Online', 'Distance Learning', 'Hybrid'];
        foreach ($types as $index => $item) {
            \App\Models\ProgramType::firstOrCreate(['title' => $item], ['sort_order' => $index, 'status' => true]);
        }

        // 3. Stream Offered
        $streams = ['Engineering', 'Management', 'Medical', 'Science', 'Arts & Humanities', 'Commerce', 'Law', 'Design', 'Pharmacy'];
        foreach ($streams as $index => $item) {
            \App\Models\StreamOffered::firstOrCreate(['title' => $item], ['sort_order' => $index, 'status' => true]);
        }

        // 4. Disciplines
        $disciplines = ['Computer Science', 'Mechanical Engineering', 'Civil Engineering', 'Electrical Engineering', 'Business Administration', 'Economics', 'Psychology', 'Physics', 'Mathematics'];
        foreach ($disciplines as $index => $item) {
            \App\Models\Discipline::firstOrCreate(['title' => $item], ['sort_order' => $index, 'status' => true]);
        }

        // 5. Specializations
        $specializations = ['Artificial Intelligence', 'Data Science', 'Cyber Security', 'Digital Marketing', 'Finance', 'Human Resources', 'Cloud Computing', 'Robotics'];
        foreach ($specializations as $index => $item) {
            \App\Models\Specialization::firstOrCreate(['title' => $item], ['sort_order' => $index, 'status' => true]);
        }

        // 6. Organisation Types
        $orgTypes = ['Private University', 'Government University', 'Deemed University', 'State University', 'Central University', 'Autonomous College'];
        foreach ($orgTypes as $index => $item) {
            \App\Models\OrganisationType::firstOrCreate(['title' => $item], ['sort_order' => $index, 'status' => true]);
        }

        // 7. Accreditation & Approvals
        $approvals = ['UGC', 'AICTE', 'NAAC A++', 'NAAC A+', 'NAAC A', 'NBA', 'PCI', 'BCI', 'MCI'];
        foreach ($approvals as $index => $item) {
            \App\Models\AccreditationApproval::firstOrCreate(['title' => $item], ['sort_order' => $index, 'status' => true]);
        }

        // 8. Campus Types
        $campusTypes = ['Urban', 'Rural', 'Semi-Urban', 'Residential', 'Non-Residential'];
        foreach ($campusTypes as $index => $item) {
            \App\Models\CampusType::firstOrCreate(['title' => $item], ['sort_order' => $index, 'status' => true]);
        }

        // 9. Sports
        $sports = ['Cricket', 'Football', 'Basketball', 'Badminton', 'Volleyball', 'Table Tennis', 'Swimming', 'Athletics', 'Tennis', 'Hockey'];
        foreach ($sports as $index => $item) {
            \App\Models\Sport::firstOrCreate(['title' => $item], ['sort_order' => $index, 'status' => true]);
        }
    }
}
