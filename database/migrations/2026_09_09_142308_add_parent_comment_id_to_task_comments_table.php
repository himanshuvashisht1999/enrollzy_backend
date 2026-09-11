<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('task_comments')) {
            Schema::table('task_comments', function (Blueprint $table) {
                if (!Schema::hasColumn('task_comments', 'parent_comment_id')) {
                    $table->unsignedBigInteger('parent_comment_id')->nullable()->after('task_id')->index();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('task_comments')) {
            Schema::table('task_comments', function (Blueprint $table) {
                if (Schema::hasColumn('task_comments', 'parent_comment_id')) {
                    $table->dropColumn('parent_comment_id');
                }
            });
        }
    }
};
