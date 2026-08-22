<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$cats = ["Engineering", "Medical", "Management", "Law", "School Admission", "Arts", "Commerce", "Civil Services", "Design", "Media & Journalism"];
foreach($cats as $cat) { \App\Models\ExamCategory::firstOrCreate(["title" => $cat], ["status" => 1]); }

