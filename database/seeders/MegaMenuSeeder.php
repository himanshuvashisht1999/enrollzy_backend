<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MegaMenu;

class MegaMenuSeeder extends Seeder
{
    public function run(): void
    {
        if (MegaMenu::count() > 0) {
            return;
        }

        // 1. Boarding Schools
        $cat1 = MegaMenu::create(['title' => 'Boarding Schools', 'url' => '/all-schools', 'sort_order' => 1]);
        
        // School Type
        MegaMenu::create(['parent_id' => $cat1->id, 'column_title' => 'School Type', 'title' => 'Boys Boarding Schools', 'url' => '/all-schools?school_type=Boys+Boarding', 'sort_order' => 1]);
        MegaMenu::create(['parent_id' => $cat1->id, 'column_title' => 'School Type', 'title' => 'Girls Boarding Schools', 'url' => '/all-schools?school_type=Girls+Boarding', 'sort_order' => 2]);
        MegaMenu::create(['parent_id' => $cat1->id, 'column_title' => 'School Type', 'title' => 'Co-Ed Boarding Schools', 'url' => '/all-schools?school_type=Co-Ed+Boarding', 'sort_order' => 3]);
        MegaMenu::create(['parent_id' => $cat1->id, 'column_title' => 'School Type', 'title' => 'Residential Schools', 'url' => '/all-schools?school_type=Residential', 'sort_order' => 4]);

        // Curriculum
        MegaMenu::create(['parent_id' => $cat1->id, 'column_title' => 'Curriculum', 'title' => 'CBSE Boarding', 'url' => '/all-schools?board=CBSE', 'sort_order' => 1]);
        MegaMenu::create(['parent_id' => $cat1->id, 'column_title' => 'Curriculum', 'title' => 'ICSE Boarding', 'url' => '/all-schools?board=ICSE', 'sort_order' => 2]);
        MegaMenu::create(['parent_id' => $cat1->id, 'column_title' => 'Curriculum', 'title' => 'IB Boarding', 'url' => '/all-schools?board=IB', 'sort_order' => 3]);
        MegaMenu::create(['parent_id' => $cat1->id, 'column_title' => 'Curriculum', 'title' => 'Cambridge Boarding', 'url' => '/all-schools?board=Cambridge', 'sort_order' => 4]);

        // Browse by State
        MegaMenu::create(['parent_id' => $cat1->id, 'column_title' => 'Browse by State', 'title' => 'Uttarakhand', 'url' => '/all-schools?state=Uttarakhand', 'sort_order' => 1]);
        MegaMenu::create(['parent_id' => $cat1->id, 'column_title' => 'Browse by State', 'title' => 'Himachal Pradesh', 'url' => '/all-schools?state=Himachal+Pradesh', 'sort_order' => 2]);
        MegaMenu::create(['parent_id' => $cat1->id, 'column_title' => 'Browse by State', 'title' => 'Rajasthan', 'url' => '/all-schools?state=Rajasthan', 'sort_order' => 3]);
        MegaMenu::create(['parent_id' => $cat1->id, 'column_title' => 'Browse by State', 'title' => 'Karnataka', 'url' => '/all-schools?state=Karnataka', 'sort_order' => 4]);

        // 2. Universities
        $cat2 = MegaMenu::create(['title' => 'Universities', 'url' => '/university', 'sort_order' => 2]);
        MegaMenu::create(['parent_id' => $cat2->id, 'column_title' => 'Browse by Stream', 'title' => 'Engineering', 'url' => '/university?search=Engineering', 'sort_order' => 1]);
        MegaMenu::create(['parent_id' => $cat2->id, 'column_title' => 'Browse by Stream', 'title' => 'Medical', 'url' => '/university?search=Medical', 'sort_order' => 2]);
        MegaMenu::create(['parent_id' => $cat2->id, 'column_title' => 'Browse by Stream', 'title' => 'Management', 'url' => '/university?search=Management', 'sort_order' => 3]);
        MegaMenu::create(['parent_id' => $cat2->id, 'column_title' => 'Browse by Stream', 'title' => 'Law', 'url' => '/university?search=Law', 'sort_order' => 4]);

        MegaMenu::create(['parent_id' => $cat2->id, 'column_title' => 'Browse by Degree', 'title' => 'Undergraduate', 'url' => '/university?search=Undergraduate', 'sort_order' => 1]);
        MegaMenu::create(['parent_id' => $cat2->id, 'column_title' => 'Browse by Degree', 'title' => 'Postgraduate', 'url' => '/university?search=Postgraduate', 'sort_order' => 2]);

        // 3. Integrated Coaching
        $cat3 = MegaMenu::create(['title' => 'Integrated Coaching', 'url' => '/all-coaching', 'sort_order' => 3]);
        MegaMenu::create(['parent_id' => $cat3->id, 'column_title' => 'Coaching Categories', 'title' => 'IIT JEE Coaching', 'url' => '/all-coaching?search=JEE', 'sort_order' => 1]);
        MegaMenu::create(['parent_id' => $cat3->id, 'column_title' => 'Coaching Categories', 'title' => 'NEET Medical Coaching', 'url' => '/all-coaching?search=NEET', 'sort_order' => 2]);
        MegaMenu::create(['parent_id' => $cat3->id, 'column_title' => 'Coaching Categories', 'title' => 'NDA Defence Coaching', 'url' => '/all-coaching?search=NDA', 'sort_order' => 3]);

        // 4. Top Exams
        $cat4 = MegaMenu::create(['title' => 'Top Exams', 'url' => '/top-exams', 'sort_order' => 4]);
        MegaMenu::create(['parent_id' => $cat4->id, 'column_title' => 'Engineering Exams', 'title' => 'JEE Main', 'url' => '/top-exams', 'sort_order' => 1]);
        MegaMenu::create(['parent_id' => $cat4->id, 'column_title' => 'Engineering Exams', 'title' => 'JEE Advanced', 'url' => '/top-exams', 'sort_order' => 2]);
        MegaMenu::create(['parent_id' => $cat4->id, 'column_title' => 'Medical Exams', 'title' => 'NEET UG', 'url' => '/top-exams', 'sort_order' => 1]);
    }
}
