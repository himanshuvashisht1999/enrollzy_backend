<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * calling_histories.user_id holds a lead's id (users.id) but was a varchar(255) with no index. Every "has this lead been
 * called?" check (lead assignment counts and pools, the calling dashboards) compared a number with text, so MySQL read the
 * whole table for each lead: the Lead Assignment count for a category of ~63,000 leads ran for minutes and never showed.
 * As a number column with an index the same count takes under a second.
 */
return new class extends Migration
{
    public function up(): void
    {
        // only numbers can become numbers: stop (and change nothing) if any row holds something else
        $bad = DB::table('calling_histories')->whereRaw("user_id IS NULL OR user_id = '' OR user_id NOT REGEXP '^[0-9]+$'")->count();
        if ($bad) {
            throw new RuntimeException("calling_histories has $bad row(s) whose user_id is not a lead id; fix them first: SELECT id, user_id FROM calling_histories WHERE user_id IS NULL OR user_id = '' OR user_id NOT REGEXP '^[0-9]+\$';");
        }
        $hasIndex = collect(DB::select("SHOW INDEX FROM calling_histories WHERE Key_name = 'calling_histories_user_id_index'"))->isNotEmpty();
        DB::statement('ALTER TABLE `calling_histories` MODIFY `user_id` BIGINT UNSIGNED NOT NULL' . ($hasIndex ? '' : ', ADD INDEX `calling_histories_user_id_index` (`user_id`)'));
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `calling_histories` DROP INDEX `calling_histories_user_id_index`, MODIFY `user_id` VARCHAR(255) NOT NULL');
    }
};
