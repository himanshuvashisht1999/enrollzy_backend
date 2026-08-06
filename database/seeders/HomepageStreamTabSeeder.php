<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HomepageStreamTabSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'key' => 'medical',
                'name' => 'Medical',
                'keywords' => json_encode(['med', 'health', 'pharma', 'nursing', 'dental', 'ayurved', 'hospital']),
                'default_exams' => json_encode(['NEET UG', 'NEET PG', 'AIIMS', 'JIPMER', 'JIPMER PG']),
                'default_states' => json_encode(['Maharashtra', 'Karnataka', 'Tamil Nadu', 'Uttar Pradesh', 'Delhi']),
                'default_courses' => json_encode(['MBBS', 'BDS', 'B.Sc Nursing', 'BAMS', 'MD / MS', 'Pharm.D']),
                'sort_order' => 1,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'science',
                'name' => 'Science',
                'keywords' => json_encode(['science', 'tech', 'research', 'iisc', 'iiser', 'bio', 'chem']),
                'default_exams' => json_encode(['JEE Main', 'CUET UG', 'NEST', 'KVPY', 'CSIR NET']),
                'default_states' => json_encode(['Karnataka', 'Maharashtra', 'Tamil Nadu', 'Punjab', 'Delhi']),
                'default_courses' => json_encode(['B.Sc Physics', 'M.Sc Biotechnology', 'B.Sc Chemistry', 'B.Sc Mathematics', 'M.Sc Physics']),
                'sort_order' => 2,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'hotel',
                'name' => 'Hotel Management',
                'keywords' => json_encode(['hotel', 'hospitality', 'catering', 'tourism', 'culinary']),
                'default_exams' => json_encode(['NCHMCT JEE', 'MAH HM CET', 'IHM CET', 'CUET UG', 'UGAT']),
                'default_states' => json_encode(['Delhi', 'Goa', 'Maharashtra', 'West Bengal', 'Karnataka']),
                'default_courses' => json_encode(['BHM (Hotel Mgmt)', 'B.Sc Hospitality', 'Diploma in Catering', 'MBA Hospitality']),
                'sort_order' => 3,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'it',
                'name' => 'Information Technology',
                'keywords' => json_encode(['tech', 'computer', 'it', 'digital', 'cyber', 'software', 'polytechnic']),
                'default_exams' => json_encode(['JEE Main', 'JEE Advanced', 'BITSAT', 'VITEEE', 'SRMJEEE']),
                'default_states' => json_encode(['Karnataka', 'Telangana', 'Maharashtra', 'Tamil Nadu', 'Haryana']),
                'default_courses' => json_encode(['B.Tech CSE', 'BCA', 'MCA', 'M.Tech IT', 'B.Tech AI & Data Science']),
                'sort_order' => 4,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'arts',
                'name' => 'Arts & Humanities',
                'keywords' => json_encode(['art', 'humanities', 'social', 'design', 'literature', 'language', 'history']),
                'default_exams' => json_encode(['CUET UG', 'JNU Entrance', 'TISSNET', 'PUBDET', 'IPU CET']),
                'default_states' => json_encode(['Delhi', 'West Bengal', 'Maharashtra', 'Uttar Pradesh', 'Kerala']),
                'default_courses' => json_encode(['BA English', 'BA Psychology', 'BA History', 'MA Political Science', 'BA Sociology']),
                'sort_order' => 5,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'agri',
                'name' => 'Agriculture',
                'keywords' => json_encode(['agri', 'farm', 'horticulture', 'veterinary', 'crop', 'soil']),
                'default_exams' => json_encode(['ICAR AIEEA', 'KEAM Agri', 'MHT CET Agri', 'UPCATET', 'MP PAT']),
                'default_states' => json_encode(['Punjab', 'Uttar Pradesh', 'Maharashtra', 'Haryana', 'Tamil Nadu']),
                'default_courses' => json_encode(['B.Sc Agriculture', 'B.Tech Agri Engg', 'M.Sc Agronomy', 'B.V.Sc & AH']),
                'sort_order' => 6,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'law',
                'name' => 'Law',
                'keywords' => json_encode(['law', 'legal', 'nlu', 'juridical', 'justice', 'advocate']),
                'default_exams' => json_encode(['CLAT', 'AILET', 'LSAT India', 'SLAT', 'MH CET Law']),
                'default_states' => json_encode(['Delhi', 'Karnataka', 'Maharashtra', 'Telangana', 'West Bengal']),
                'default_courses' => json_encode(['BA LLB (Hons)', 'BBA LLB', 'LLM Corporate Law', 'LLB (3 Year)']),
                'sort_order' => 7,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'pharmacy',
                'name' => 'Pharmacy',
                'keywords' => json_encode(['pharma', 'medicine', 'drug', 'pharmaceutical']),
                'default_exams' => json_encode(['GPAT', 'NIPER JEE', 'MHT CET Pharma', 'WBJEE Pharma', 'KCET Pharma']),
                'default_states' => json_encode(['Maharashtra', 'Gujarat', 'Uttar Pradesh', 'Punjab', 'Karnataka']),
                'default_courses' => json_encode(['B.Pharm', 'D.Pharm', 'M.Pharm', 'Pharm.D', 'B.Pharm (Lateral Entry)']),
                'sort_order' => 8,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'education',
                'name' => 'Education',
                'keywords' => json_encode(['education', 'teacher', 'training', 'pedagogy', 'bed', 'dled']),
                'default_exams' => json_encode(['RIE CEE', 'DU B.Ed', 'MAH B.Ed CET', 'Bihar B.Ed CET', 'UP B.Ed JEE']),
                'default_states' => json_encode(['Uttar Pradesh', 'Bihar', 'Delhi', 'Rajasthan', 'Madhya Pradesh']),
                'default_courses' => json_encode(['B.Ed', 'D.El.Ed', 'M.Ed', 'B.El.Ed', 'Special B.Ed']),
                'sort_order' => 9,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('homepage_stream_tabs')->truncate();
        DB::table('homepage_stream_tabs')->insert($data);
    }
}
