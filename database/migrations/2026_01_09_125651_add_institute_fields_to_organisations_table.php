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
        Schema::table('organisations', function (Blueprint $table) {
            $table->string('institute_id')->unique()->nullable()->after('id');
            $table->string('brand_name')->nullable()->after('name');
            $table->string('slug')->unique()->nullable()->after('brand_name');
            $table->string('logo')->nullable()->after('image');
            $table->string('cover_image')->nullable()->after('logo');
            $table->integer('established_year')->nullable()->after('cover_image');
            $table->string('ownership_type')->nullable()->after('established_year'); // Private / Trust / Franchise / Govt
            $table->string('institute_type')->nullable()->after('ownership_type'); // Coaching Only / Integrated / Residential
            $table->string('head_office_city')->nullable()->after('address');
            $table->string('head_office_state')->nullable()->after('head_office_city');
            $table->string('head_office_country')->nullable()->after('head_office_state');
            $table->longText('about_institute')->nullable()->after('review');
            $table->longText('vision_mission')->nullable()->after('about_institute');
            $table->longText('why_choose_us')->nullable()->after('vision_mission');
        });
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
