<?php
$file = 'c:/xampp/htdocs/college_amit_2/app/Http/Controllers/Admin/OrganisationController.php';
if (!file_exists($file)) {
    die("File not found: $file\n");
}
$content = file_get_contents($file);

// 1. Update store and update validation block headers
$content = str_replace(
    '// Type 6: Counselling Body',
    '// Type 6 & 7: Counselling Body & Regulatory Body',
    $content
);
$content = preg_replace(
    '/if\s*\(\$request->organisation_type_id\s*==\s*6\)/',
    'if (in_array($request->organisation_type_id, [6, 7]))',
    $content
);

// 2. Update booleans list (in both store and update)
$booleansToSearch = "'institution_reporting_interface_available'";
$booleansToReplace = "'institution_reporting_interface_available',
            'rti_applicable',
            'audit_conducted',
            'claimed_by_authority'";
$content = str_replace($booleansToSearch, $booleansToReplace, $content);

// 3. Update paths in store and update
$content = str_replace(
    "'6' => 'media/counselling_bodies',",
    "'6' => 'media/counselling_bodies',
                '7' => 'media/regulatory_bodies',",
    $content
);

// 4. Update arrayFields in update (store already done by multi_replace)
$content = str_replace(
    "if (in_array(\$request->organisation_type_id, [5, 6])) {",
    "if (in_array(\$request->organisation_type_id, [5, 6, 7])) {",
    $content
);

// 5. Add unique fields for ID 7 to the rules block (only if they don't exist in the block)
// The user requested many fields for ID 7. I'll make sure they are included in the rules block.
// I'll search for the first rules += [ block for 6/7 and append missing fields.
$content = str_replace(
    "'confidence_score' => 'nullable|integer',",
    "'confidence_score' => 'nullable|integer',
                'abbreviation' => 'nullable|string|max:100',
                'functions' => 'nullable|array',
                'annual_candidate_volume' => 'nullable|string',
                'institutions_covered_count' => 'nullable|integer',
                'states_covered_count' => 'nullable|integer',
                'media_mentions' => 'nullable|string',",
    $content
);

if (file_put_contents($file, $content)) {
    echo "Successfully updated OrganisationController.php\n";
} else {
    echo "Failed to write to OrganisationController.php\n";
}
