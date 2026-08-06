<?php

// Bootstrap Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->boot();

use Illuminate\Support\Facades\DB;

echo "=== Syncing mega_menus parent_id=null with header_links ===\n\n";

// Get all active header_links
$headerLinks = DB::table('header_links')->where('status', 1)->orderBy('sort_order')->get();

foreach ($headerLinks as $hl) {
    // Check if a root mega_menu item already references this header_link_id
    $existing = DB::table('mega_menus')
        ->whereNull('parent_id')
        ->where('header_link_id', $hl->id)
        ->first();

    if (!$existing) {
        // Check by title similarity
        $byTitle = DB::table('mega_menus')
            ->whereNull('parent_id')
            ->whereNull('header_link_id')
            ->where(function($q) use ($hl) {
                $q->where('title', 'like', '%' . substr($hl->title, 0, 8) . '%');
            })
            ->first();

        if ($byTitle) {
            // Link existing root entry to this header_link
            DB::table('mega_menus')->where('id', $byTitle->id)->update([
                'header_link_id' => $hl->id,
                'title'          => $hl->title,
                'sort_order'     => $hl->sort_order,
            ]);
            echo "Linked existing mega_menu ID={$byTitle->id} ('{$byTitle->title}') -> header_link '{$hl->title}'\n";
        } else {
            // Create new root entry for this header_link
            $newId = DB::table('mega_menus')->insertGetId([
                'parent_id'      => null,
                'header_link_id' => $hl->id,
                'title'          => $hl->title,
                'url'            => $hl->url,
                'sort_order'     => $hl->sort_order,
                'status'         => 1,
                'is_highlighted' => 0,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
            echo "Created new mega_menu root ID={$newId} for header_link '{$hl->title}'\n";
        }
    } else {
        // Update title/sort_order from header_link
        DB::table('mega_menus')->where('id', $existing->id)->update([
            'title'      => $hl->title,
            'sort_order' => $hl->sort_order,
        ]);
        echo "Already linked: mega_menu ID={$existing->id} for header_link '{$hl->title}'\n";
    }
}

// Now update sub-items to set header_link_id based on their parent's header_link_id
$rootItems = DB::table('mega_menus')->whereNull('parent_id')->whereNotNull('header_link_id')->get();
foreach ($rootItems as $root) {
    DB::table('mega_menus')
        ->where('parent_id', $root->id)
        ->whereNull('header_link_id')
        ->update(['header_link_id' => $root->header_link_id]);
    echo "Updated sub-items under mega_menu ID={$root->id} ('{$root->title}') with header_link_id={$root->header_link_id}\n";
}

echo "\n=== Done! ===\n";
