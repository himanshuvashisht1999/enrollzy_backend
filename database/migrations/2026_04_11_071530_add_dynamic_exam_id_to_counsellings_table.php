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
        Schema::table('counsellings', function (Blueprint $table) {
            $table->unsignedBigInteger('dynamic_exam_id')->nullable()->after('exam_id');
            $table->foreign('dynamic_exam_id')->references('id')->on('dynamic_exams')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('counsellings', function (Blueprint $table) {
            $table->dropForeign(['dynamic_exam_id']);
            $table->dropColumn('dynamic_exam_id');
        });
    }
};
