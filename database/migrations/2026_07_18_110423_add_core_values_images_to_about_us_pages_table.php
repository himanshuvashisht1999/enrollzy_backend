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
        Schema::table('about_us_pages', function (Blueprint $table) {
            $table->string('mission_image')->nullable()->after('mission_text');
            $table->string('vision_image')->nullable()->after('vision_text');
            $table->string('philosophy_image')->nullable()->after('philosophy_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('about_us_pages', function (Blueprint $table) {
            $table->dropColumn(['mission_image', 'vision_image', 'philosophy_image']);
        });
    }
};
