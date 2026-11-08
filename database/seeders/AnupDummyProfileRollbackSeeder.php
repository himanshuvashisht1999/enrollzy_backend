<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Undo AnupDummyProfileSeeder: restore Anup Kumar Vishal's fields from the backup and delete the dummy
 * slots, students, bookings and reviews it added.
 *
 *   php artisan db:seed --class=AnupDummyProfileRollbackSeeder
 */
class AnupDummyProfileRollbackSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('app/' . AnupDummyProfileSeeder::BACKUP);
        if (!is_file($path)) { $this->command->error('No backup found at ' . $path . '. Nothing to undo.'); return; }
        $b = json_decode(file_get_contents($path), true);

        DB::transaction(function () use ($b) {
            $c = $b['created'] ?? [];
            if (!empty($c['reviews'])) DB::table('reviews')->whereIn('id', $c['reviews'])->delete();
            if (!empty($c['bookings'])) DB::table('bookings')->whereIn('id', $c['bookings'])->delete();
            if (!empty($c['slots'])) DB::table('expert_slots')->whereIn('id', $c['slots'])->delete();
            if (!empty($c['users'])) DB::table('users')->whereIn('id', $c['users'])->delete();

            $restore = [];
            foreach ((array) ($b['changed'] ?? []) as $col) {
                if (array_key_exists($col, $b['original'])) $restore[$col] = $b['original'][$col];
            }
            // a website account linked by the dummy mobile number is unlinked too
            if (\Illuminate\Support\Facades\Schema::hasColumn('experts', 'user_id')) $restore['user_id'] = $b['original']['user_id'] ?? null;
            if ($restore) DB::table('experts')->where('id', $b['expert_id'])->update($restore);
        });

        rename($path, $path . '.restored-' . date('Ymd-His'));
        $this->command->info('Anup Kumar Vishal restored; dummy slots, students, bookings and reviews deleted.');
    }
}
