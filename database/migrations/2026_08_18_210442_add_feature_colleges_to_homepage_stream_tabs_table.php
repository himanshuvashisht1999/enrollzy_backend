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
        Schema::table('homepage_stream_tabs', function (Blueprint $table) {
            $table->json('feature_colleges')->nullable()->after('default_courses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('homepage_stream_tabs', function (Blueprint $table) {
            $table->dropColumn('feature_colleges');
        });
    }
};
