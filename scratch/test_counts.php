<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Courses: " . \App\Models\Course::count() . "\n";
echo "Program Levels: " . \App\Models\ProgramLevel::count() . "\n";
echo "Streams: " . \App\Models\StreamOffered::count() . "\n";
echo "Disciplines: " . \App\Models\Discipline::count() . "\n";
echo "OrgTypes: " . \App\Models\OrganisationType::count() . "\n";
