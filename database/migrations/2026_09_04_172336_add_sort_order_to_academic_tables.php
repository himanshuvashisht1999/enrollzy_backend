<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('organisations', 'sort_order')) {
            Schema::table('organisations', function (Blueprint $table) {
                $table->integer('sort_order')->default(1)->after('id');
            });
            DB::table('organisations')->whereNull('sort_order')->orWhere('sort_order', 0)->update(['sort_order' => 1]);
        }

        if (!Schema::hasColumn('campuses', 'sort_order')) {
            Schema::table('campuses', function (Blueprint $table) {
                $table->integer('sort_order')->default(1)->after('id');
            });
            DB::table('campuses')->whereNull('sort_order')->orWhere('sort_order', 0)->update(['sort_order' => 1]);
        }

        if (!Schema::hasColumn('departments', 'sort_order')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->integer('sort_order')->default(1)->after('id');
            });
            DB::table('departments')->whereNull('sort_order')->orWhere('sort_order', 0)->update(['sort_order' => 1]);
        }

        if (Schema::hasColumn('organisation_courses', 'sort_order')) {
            DB::table('organisation_courses')->whereNull('sort_order')->orWhere('sort_order', 0)->update(['sort_order' => 1]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('organisations', 'sort_order')) {
            Schema::table('organisations', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }

        if (Schema::hasColumn('campuses', 'sort_order')) {
            Schema::table('campuses', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }

        if (Schema::hasColumn('departments', 'sort_order')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }
    }
};
