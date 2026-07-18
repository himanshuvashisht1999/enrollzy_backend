<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$exam = \App\Models\DynamicExam::where('slug', 'national-eligibility-cum-entrance-test-undergraduate')->first();
if ($exam && is_array($exam->sections)) {
    foreach($exam->sections as $s) {
        if (isset($s['content']) && is_array($s['content'])) {
            foreach($s['content'] as $item) {
                var_dump($item);
            }
        }
    }
}
