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
        Schema::table('organisation_courses', function (Blueprint $blade) {
            $blade->foreignId('language_id')->nullable()->constrained('languages')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisation_courses', function (Blueprint $blade) {
            $blade->dropForeign(['language_id']);
            $blade->dropColumn('language_id');
        });
    }
};
