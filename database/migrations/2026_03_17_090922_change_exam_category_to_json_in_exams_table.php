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
        \Illuminate\Support\Facades\DB::table('exams')->whereNotNull('exam_category')->get()->each(function ($exam) {
            $cat = $exam->exam_category;
            if (!empty($cat) && !str_starts_with(trim($cat), '[') && !str_starts_with(trim($cat), '{')) {
                \Illuminate\Support\Facades\DB::table('exams')->where('id', $exam->id)->update([
                    'exam_category' => json_encode([$cat])
                ]);
            }
        });

        Schema::table('exams', function (Blueprint $table) {});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            if (!Schema::hasColumn('exams', 'exam_category')) $table->string('exam_category')->nullable()->change();
        });
    }
};
