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
        Schema::table('organisations', function (Blueprint $table) {});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropColumn([
                'institute_id', 'brand_name', 'slug', 'logo', 'cover_image',
                'established_year', 'ownership_type', 'institute_type',
                'head_office_city', 'head_office_state', 'head_office_country',
                'about_institute', 'vision_mission', 'why_choose_us'
            ]);
        });
    }
};
