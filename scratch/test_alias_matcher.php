<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$courses = \App\Models\Course::select('id', 'name')->get();

$aliases = [
    'diploma in pharmacy' => ['d.pharm', 'd pharm', 'dpharm', 'diploma pharmacy'],
    'bachelor of pharmacy' => ['b.pharm', 'b pharm', 'bpharm', 'b pharmacy'],
    'master of pharmacy' => ['m.pharm', 'm pharm', 'mpharm', 'm pharmacy'],
    'bachelor of technology' => ['b.tech', 'b tech', 'btech'],
    'master of technology' => ['m.tech', 'm tech', 'mtech'],
    'master of business administration' => ['mba'],
    'bachelor of business administration' => ['bba'],
    'bachelor of computer applications' => ['bca'],
    'master of computer applications' => ['mca'],
    'bachelor of commerce' => ['b.com', 'bcom'],
    'master of commerce' => ['m.com', 'mcom'],
    'bachelor of science' => ['b.sc', 'bsc'],
    'master of science' => ['m.sc', 'msc'],
    'bachelor of arts' => ['b.a', 'ba'],
    'master of arts' => ['m.a', 'ma'],
    'bachelor of laws' => ['llb', 'll.b'],
    'master of laws' => ['llm', 'll.m'],
];

function normalizeStr($str) {
    $str = strtolower(trim($str));
    $str = preg_replace('/\b(in|of|and|&|the|for|with|a|an|program|course|degree|honors|hons)\b/i', '', $str);
    $str = preg_replace('/[^a-z0-9]/', '', $str);
    return trim($str);
}

function matchMasterCourse($aiName, $aiShortName, $courses, $aliases) {
    $normName = normalizeStr($aiName);
    $normShort = normalizeStr($aiShortName);

    // 1. Direct normalized match
    foreach ($courses as $c) {
        $normC = normalizeStr($c->name);
        if ($normName !== '' && $normName === $normC) {
            return $c;
        }
        if ($normShort !== '' && $normShort === $normC) {
            return $c;
        }
    }

    // 2. Alias match
    foreach ($aliases as $long => $aliasList) {
        $normLong = normalizeStr($long);
        $matchesLong = ($normName === $normLong || str_contains($normName, $normLong));
        $matchesShort = false;
        foreach ($aliasList as $al) {
            $normAl = normalizeStr($al);
            if ($normShort === $normAl || $normName === $normAl) {
                $matchesShort = true;
                break;
            }
        }

        if ($matchesLong || $matchesShort) {
            // Find course in courses table that matches long or any alias
            foreach ($courses as $c) {
                $normC = normalizeStr($c->name);
                if ($normC === $normLong) return $c;
                foreach ($aliasList as $al) {
                    if ($normC === normalizeStr($al)) return $c;
                }
            }
        }
    }

    // 3. Substring / Similar text match
    $best = null;
    $maxScore = 0;
    foreach ($courses as $c) {
        $normC = normalizeStr($c->name);
        similar_text($normName, $normC, $pct);
        if ($pct > $maxScore && $pct >= 60) {
            $maxScore = $pct;
            $best = $c;
        }
    }

    return $best;
}

$service = new \App\Services\OrganisationImportService();

$tests = [
    ['name' => 'Diploma in Pharmacy', 'short' => 'D.Pharm'],
    ['name' => 'Diploma Pharmacy', 'short' => ''],
    ['name' => 'Bachelor of Pharmacy', 'short' => 'B.Pharm'],
    ['name' => 'B.Tech in Computer Science and Engineering', 'short' => 'B.Tech CSE'],
    ['name' => 'Master of Business Administration', 'short' => 'MBA'],
    ['name' => 'Bachelor of Computer Applications', 'short' => 'BCA'],
    ['name' => 'MBA in Marketing', 'short' => 'MBA'],
    ['name' => 'B.Tech Mechanical Engineering', 'short' => 'B.Tech ME'],
];

echo "=== Testing OrganisationImportService::resolveMasterCourse ===\n";
foreach ($tests as $t) {
    $matched = $service->resolveMasterCourse(null, $t['name'], $t['short']);
    echo "AI Input: '{$t['name']}' ({$t['short']}) ===> Matched Master Course: '" . ($matched->name ?? 'None') . "' (ID: " . ($matched->id ?? 'null') . ")\n";
}

