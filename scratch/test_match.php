<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$courses = \App\Models\Course::select('id', 'name')->get();

function normalizeString($str) {
    $str = strtolower($str);
    // Remove stop words
    $stopWords = ['in', 'of', 'and', '&', 'the', 'for', 'with', 'a', 'an', 'program', 'course', 'degree'];
    $str = preg_replace('/\b(' . implode('|', $stopWords) . ')\b/i', '', $str);
    // Remove punctuation
    $str = preg_replace('/[^a-z0-9]/', '', $str);
    return trim($str);
}

function findBestMatch($query, $items, $field = 'name') {
    $normQuery = normalizeString($query);
    $bestMatch = null;
    $highestScore = 0;

    foreach ($items as $item) {
        $val = $item->$field;
        $normVal = normalizeString($val);

        if ($normQuery === $normVal) {
            return ['item' => $item, 'score' => 100];
        }

        // Check similarity
        similar_text($normQuery, $normVal, $percent);
        if ($percent > $highestScore) {
            $highestScore = $percent;
            $bestMatch = $item;
        }
    }

    return ['item' => $bestMatch, 'score' => $highestScore];
}

$testQueries = [
    'Diploma in Pharmacy',
    'B.Tech in Computer Science and Engineering',
    'Master of Business Administration',
    'B.Pharm',
    'Bachelor of Computer Applications'
];

foreach ($testQueries as $q) {
    $res = findBestMatch($q, $courses);
    echo "Query: '{$q}' => Matched: '" . ($res['item']->name ?? 'None') . "' (Score: " . round($res['score'], 1) . "%)\n";
}
