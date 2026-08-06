<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scholarships', function (Blueprint $table) {
            // Homepage Card fields
            $table->string('display_title')->nullable()->after('short_name');          // Alternate homepage title
            $table->string('card_icon')->nullable()->after('display_title');           // FontAwesome class or image path
            $table->string('card_background_color', 20)->nullable()->after('card_icon');  // e.g. #1a73e8
            $table->string('card_text_color', 20)->nullable()->after('card_background_color'); // e.g. #ffffff

            // Banner fields
            $table->string('banner_title')->nullable()->after('banner_image');
            $table->string('banner_subtitle')->nullable()->after('banner_title');

            // Provider
            $table->string('provider_url')->nullable()->after('provider_logo');
        });
    }

    public function down(): void
    {
        Schema::table('scholarships', function (Blueprint $table) {
            $table->dropColumn([
                'display_title', 'card_icon', 'card_background_color', 'card_text_color',
                'banner_title', 'banner_subtitle', 'provider_url',
            ]);
        });
    }
};
