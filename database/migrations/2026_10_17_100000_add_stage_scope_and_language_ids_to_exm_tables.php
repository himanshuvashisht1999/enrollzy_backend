<?php

use App\Models\Language;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Distinguishes "All stages (common)" from an untagged event.
        Schema::table('exm_cycle_events', function (Blueprint $table) {
            $table->boolean('applies_to_all_stages')->default(false)->after('stage_id');
        });

        // languages: free-text names -> Language master ids. Unknown names are dropped.
        $byTitle = Language::pluck('id', 'title')->mapWithKeys(fn ($id, $t) => [mb_strtolower(trim($t)) => $id]);
        DB::table('exm_exams')->whereNotNull('languages')->get(['id', 'languages'])->each(function ($row) use ($byTitle) {
            $ids = collect(json_decode($row->languages, true) ?: [])
                ->map(fn ($v) => is_numeric($v) ? (int) $v : $byTitle[mb_strtolower(trim($v))] ?? null)
                ->filter()->unique()->values()->all();
            DB::table('exm_exams')->where('id', $row->id)->update(['languages' => $ids ? json_encode($ids) : null]);
        });
    }

    public function down(): void
    {
        Schema::table('exm_cycle_events', function (Blueprint $table) {
            $table->dropColumn('applies_to_all_stages');
        });
    }
};
