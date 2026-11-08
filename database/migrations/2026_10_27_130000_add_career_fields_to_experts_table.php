<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Experts on the career pages: the career categories an expert advises on and the classes they counsel (empty: all),
 * and whether they show there. Filled for the current experts from their expert category ("Medical Career Advisor" ->
 * Medical & healthcare…); an expert already set keeps their choice.
 */
return new class extends Migration
{
    /** Words in an expert category's name => career category slugs. */
    private const MAP = [
        'medical|mbbs|doctor|neet' => ['medical'],
        'defen[cs]e|army|navy|nda' => ['defence'],
        'science|technology|engineer|jee' => ['engineering', 'science'],
        'mba|bba|management|business' => ['management', 'commerce'],
        'commerce|finance|\bca\b' => ['commerce'],
        'law|clat' => ['law'],
        'design|art' => ['design'],
        'government|civil|upsc' => ['government'],
    ];

    public function up(): void
    {
        Schema::table('experts', function (Blueprint $t) {
            if (!Schema::hasColumn('experts', 'career_category_ids')) $t->json('career_category_ids')->nullable()->after('expert_category_id');
            if (!Schema::hasColumn('experts', 'career_class_ids')) $t->json('career_class_ids')->nullable()->after('career_category_ids');
            if (!Schema::hasColumn('experts', 'show_on_career_pages')) $t->boolean('show_on_career_pages')->default(true)->after('career_class_ids');
        });

        if (!Schema::hasTable('career_categories')) return;
        $cats = DB::table('career_categories')->pluck('id', 'slug');
        foreach (DB::table('experts')->whereNull('career_category_ids')->get(['id', 'expert_category_id']) as $e) {
            $name = (string) DB::table('expert_categories')->where('id', $e->expert_category_id)->value('name');
            $slugs = [];
            foreach (self::MAP as $re => $s) if (preg_match('/' . $re . '/i', $name)) $slugs = array_merge($slugs, $s);
            // a general adviser (Education Expert, Career Road Map Expert…) covers every category: left empty
            $ids = array_values(array_unique(array_filter(array_map(fn ($s) => $cats[$s] ?? null, $slugs))));
            DB::table('experts')->where('id', $e->id)->update(['career_category_ids' => json_encode($ids)]);
        }
    }

    public function down(): void
    {
        Schema::table('experts', fn (Blueprint $t) => $t->dropColumn(['career_category_ids', 'career_class_ids', 'show_on_career_pages']));
    }
};
