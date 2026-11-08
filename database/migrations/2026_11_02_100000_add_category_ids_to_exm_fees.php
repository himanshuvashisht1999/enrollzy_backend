<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Exam fees: one fee row can cover several categories ("EWS, OBC-NCL · Male · ₹900") instead of one row per category.
 * exm_fees.category_ids holds the list (empty = every candidate); category_id keeps the first one for older code and the
 * "category in use" check. Existing rows get their one category as the list.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exm_fees', function (Blueprint $table) {
            if (!Schema::hasColumn('exm_fees', 'category_ids')) $table->json('category_ids')->nullable()->after('category_id');
        });
        DB::table('exm_fees')->whereNull('category_ids')->whereNotNull('category_id')->update(['category_ids' => DB::raw("CONCAT('[', category_id, ']')")]);
    }

    public function down(): void
    {
        Schema::table('exm_fees', fn (Blueprint $table) => $table->dropColumn('category_ids'));
    }
};
