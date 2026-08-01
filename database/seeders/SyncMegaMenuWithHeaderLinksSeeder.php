<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncMegaMenuWithHeaderLinksSeeder extends Seeder
{
    public function run(): void
    {
        // Step 1: Add header_link_id column if not exists
        if (!Schema::hasColumn('mega_menus', 'header_link_id')) {
            Schema::table('mega_menus', function ($table) {
                $table->unsignedBigInteger('header_link_id')->nullable()->after('parent_id');
            });
        }

        $this->command->info('Syncing mega_menus root categories with header_links...');

        $headerLinks = DB::table('header_links')->where('status', 1)->orderBy('sort_order')->get();

        foreach ($headerLinks as $hl) {
            $existing = DB::table('mega_menus')
                ->whereNull('parent_id')
                ->where('header_link_id', $hl->id)
                ->first();

            if (!$existing) {
                $byTitle = DB::table('mega_menus')
                    ->whereNull('parent_id')
                    ->whereNull('header_link_id')
                    ->where('title', 'like', '%' . substr(trim($hl->title), 0, 8) . '%')
                    ->first();

                if ($byTitle) {
                    DB::table('mega_menus')->where('id', $byTitle->id)->update([
                        'header_link_id' => $hl->id,
                        'title'          => $hl->title,
                        'sort_order'     => $hl->sort_order,
                        'updated_at'     => now(),
                    ]);
                    $this->command->info("Linked existing mega_menu ID={$byTitle->id} ('{$byTitle->title}') -> header_link '{$hl->title}'");
                } else {
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
                    $this->command->info("Created new mega_menu root ID={$newId} for header_link '{$hl->title}'");
                }
            } else {
                DB::table('mega_menus')->where('id', $existing->id)->update([
                    'title'      => $hl->title,
                    'sort_order' => $hl->sort_order,
                    'updated_at' => now(),
                ]);
                $this->command->info("Already synced: mega_menu ID={$existing->id} for header_link '{$hl->title}'");
            }
        }

        // Step 2: Update sub-items (parent_id != null) with their root's header_link_id
        $rootItems = DB::table('mega_menus')->whereNull('parent_id')->whereNotNull('header_link_id')->get();
        foreach ($rootItems as $root) {
            $updated = DB::table('mega_menus')
                ->where('parent_id', $root->id)
                ->update(['header_link_id' => $root->header_link_id, 'updated_at' => now()]);
            $this->command->info("Updated {$updated} sub-items under '{$root->title}' with header_link_id={$root->header_link_id}");
        }

        $this->command->info('Done!');
    }
}
