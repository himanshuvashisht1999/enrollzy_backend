<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AboutUsPage;
use App\Models\AboutUsOffer;
use App\Models\AboutUsFeature;
use App\Models\AboutUsImpact;

AboutUsPage::firstOrCreate([], [
    'hero_title' => 'We simplify education decisions. You shape your future.',
    'hero_subtitle' => 'ABOUT US',
    'hero_description' => "Enrollzy is India's trusted education discovery platform that helps students explore, compare and access the best schools, coaching institutes, scholarships, exam preparation, certifications and higher education opportunities.",
    'story_title' => 'A journey built on a simple belief',
    'story_subtitle' => 'OUR STORY',
    'story_purpose_text' => 'To empower every learner to discover the right opportunities and build a better tomorrow.',
    'story_description' => '<p>We started Enrollzy with a simple belief - every student deserves the right guidance and access to the best opportunities.</p><p>But the education landscape is fragmented, confusing and time-consuming. Information is scattered, comparisons are difficult and genuine guidance is hard to find.</p><p>Enrollzy was created to change that.</p><p>We bring everything a learner needs - all in one place, with transparency, accuracy and trust.</p>',
    'offers_title' => 'A complete education ecosystem',
    'offers_subtitle' => 'WHAT WE OFFER',
    'features_title' => "Designed for today's learners and families",
    'features_subtitle' => 'WHY CHOOSE ENROLLZY',
    'cta_title' => 'Your journey. Our mission.',
    'cta_description' => 'Wherever you are in your education journey, Enrollzy is here to help you take the right next step.',
    'cta_button_1_text' => 'Explore Institutes',
    'cta_button_1_link' => '#',
    'cta_button_2_text' => 'Join Community',
    'cta_button_2_link' => '#',
]);

$offers = [
    ['title' => 'Schools & Institutes', 'description' => 'Find the perfect match from thousands of top-rated educational institutions.'],
    ['title' => 'Exams & Preparation', 'description' => 'Get the latest information, syllabus, and preparation strategies for all major exams.'],
    ['title' => 'Scholarships', 'description' => 'Discover and apply for scholarships that can help fund your education dreams.'],
    ['title' => 'Courses & Certifications', 'description' => 'Explore trending courses to upskill and stay ahead in your career.'],
    ['title' => 'Expert Guidance', 'description' => 'Connect with industry experts and alumni for personalized career advice.'],
    ['title' => 'Student Community', 'description' => 'Join a thriving community of learners to share knowledge and experiences.'],
];
foreach($offers as $i => $o) {
    AboutUsOffer::firstOrCreate(['title' => $o['title']], ['description' => $o['description'], 'sort_order' => $i]);
}

$features = [
    ['title' => 'Verified Data', 'description' => '100% authentic and verified information from official sources.'],
    ['title' => 'Unbiased Comparisons', 'description' => 'Compare institutes side-by-side without any hidden agendas.'],
    ['title' => 'Personalized Discovery', 'description' => 'Get recommendations tailored to your unique profile and goals.'],
    ['title' => 'Direct Applications', 'description' => 'Apply directly to multiple institutes with a single profile.'],
    ['title' => 'Expert Support', 'description' => 'Dedicated counseling team to assist you at every step.'],
    ['title' => 'End-to-End Tracking', 'description' => 'Track your application status and updates in real-time.'],
];
foreach($features as $i => $f) {
    AboutUsFeature::firstOrCreate(['title' => $f['title']], ['description' => $f['description'], 'sort_order' => $i]);
}

$impacts = [
    ['count_text' => '2M+', 'label' => 'Students Helped'],
    ['count_text' => '5000+', 'label' => 'Institutes Listed'],
    ['count_text' => '1000+', 'label' => 'Expert Mentors'],
    ['count_text' => '50+', 'label' => 'Cities Covered'],
];
foreach($impacts as $i => $imp) {
    AboutUsImpact::firstOrCreate(['label' => $imp['label']], ['count_text' => $imp['count_text'], 'sort_order' => $i]);
}

echo "Database Seeded.";
