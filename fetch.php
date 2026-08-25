<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$items = \App\Models\MegaMenu::with('headerLink')->get();
$output = [];
foreach($items as $item) {
    if ($item->headerLink && is_null($item->headerLink->parent_id)) {
        $output[] = '- **' . $item->headerLink->title . '** > **' . ($item->column_title ?: 'N/A') . '** > ' . $item->title . ' (URL: ' . ($item->url ?: 'N/A') . ')';
    }
}
echo implode("\n", $output);
