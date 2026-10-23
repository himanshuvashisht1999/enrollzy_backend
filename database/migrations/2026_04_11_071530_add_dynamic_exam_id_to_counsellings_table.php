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
        Schema::table('counsellings', function (Blueprint $table) {});
    }

    public function down(): void
    {
        Schema::table('counsellings', function (Blueprint $table) {
            $table->dropForeign(['dynamic_exam_id']);
            $table->dropColumn('dynamic_exam_id');
        });
    }
};
