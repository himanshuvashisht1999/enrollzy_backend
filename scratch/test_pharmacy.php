<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$pharmacyCourses = \App\Models\Course::where('name', 'like', '%Pharm%')->get(['id', 'name']);
echo "Existing Pharmacy Master Courses in DB:\n";
foreach ($pharmacyCourses as $c) {
    echo "ID: {$c->id} - {$c->name}\n";
}

$mbaCourses = \App\Models\Course::where('name', 'like', '%MBA%')->orWhere('name', 'like', '%Business Administration%')->get(['id', 'name']);
echo "\nExisting MBA/Business Admin Courses in DB:\n";
foreach ($mbaCourses as $c) {
    echo "ID: {$c->id} - {$c->name}\n";
}
