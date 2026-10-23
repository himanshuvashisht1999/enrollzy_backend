<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tags reviews created by a CSV import with the batch that created them, so a whole import can be undone. */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('entity_reviews') && !Schema::hasColumn('entity_reviews', 'import_batch')) {
            Schema::table('entity_reviews', function (Blueprint $table) {
                $table->string('import_batch', 40)->nullable()->after('legacy_source')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('entity_reviews', 'import_batch')) {
            Schema::table('entity_reviews', function (Blueprint $table) {
                $table->dropIndex(['import_batch']);
                $table->dropColumn('import_batch');
            });
        }
    }
};
