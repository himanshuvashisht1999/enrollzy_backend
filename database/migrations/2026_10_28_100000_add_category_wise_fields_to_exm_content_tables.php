<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Category-wise rules in the cycle tabs:
 * - eligibility: minimum marks and attempts per category (instead of one "General" and one "Reserved" value)
 * - pattern: marks per correct answer and notes (section-wise marking and time live in the sections JSON)
 * - fees: a note ("per paper", "centres outside India")
 * - documents: the categories a document applies to (empty = everyone)
 * The old min_percentage_general / min_percentage_reserved columns are copied into the new list and kept untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exm_eligibilities', function (Blueprint $table) {
            if (!Schema::hasColumn('exm_eligibilities', 'min_percentage_by_category')) $table->json('min_percentage_by_category')->nullable()->after('subjects');   // [{category_id, percentage}]
            if (!Schema::hasColumn('exm_eligibilities', 'attempts_by_category')) $table->json('attempts_by_category')->nullable()->after('max_attempts');            // [{category_id, attempts}]
        });
        Schema::table('exm_patterns', function (Blueprint $table) {
            if (!Schema::hasColumn('exm_patterns', 'marks_per_correct')) $table->decimal('marks_per_correct', 5, 2)->nullable()->after('total_marks');
            if (!Schema::hasColumn('exm_patterns', 'notes')) $table->text('notes')->nullable()->after('sections');
        });
        Schema::table('exm_fees', function (Blueprint $table) {
            if (!Schema::hasColumn('exm_fees', 'note')) $table->string('note')->nullable()->after('currency');
        });
        Schema::table('exm_documents', function (Blueprint $table) {
            if (!Schema::hasColumn('exm_documents', 'category_ids')) $table->json('category_ids')->nullable()->after('is_mandatory');
        });

        // General -> the "General" category; Reserved -> every other category.
        $categories = DB::table('cns_categories')->orderBy('sort_order')->orderBy('id')->get(['id', 'name']);
        $general = $categories->first(fn ($c) => strcasecmp(trim($c->name), 'General') === 0);
        DB::table('exm_eligibilities')->whereNull('min_percentage_by_category')
            ->where(fn ($q) => $q->whereNotNull('min_percentage_general')->orWhereNotNull('min_percentage_reserved'))
            ->get()->each(function ($r) use ($categories, $general) {
                $rows = [];
                foreach ($categories as $c) {
                    $pct = ($general && $c->id === $general->id) ? $r->min_percentage_general : $r->min_percentage_reserved;
                    if ($pct !== null) $rows[] = ['category_id' => $c->id, 'percentage' => (float) $pct];
                }
                DB::table('exm_eligibilities')->where('id', $r->id)->update(['min_percentage_by_category' => $rows ? json_encode($rows) : null]);
            });
    }

    public function down(): void
    {
        $drop = fn (string $table, array $cols) => Schema::table($table, function (Blueprint $t) use ($table, $cols) {
            foreach ($cols as $c) if (Schema::hasColumn($table, $c)) $t->dropColumn($c);
        });
        $drop('exm_eligibilities', ['min_percentage_by_category', 'attempts_by_category']);
        $drop('exm_patterns', ['marks_per_correct', 'notes']);
        $drop('exm_fees', ['note']);
        $drop('exm_documents', ['category_ids']);
    }
};
