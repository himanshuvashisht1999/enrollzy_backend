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
        Schema::table('about_us_pages', function (Blueprint $table) {});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('about_us_pages', function (Blueprint $table) {
            $table->dropColumn([
                'mission_text',
                'vision_text',
                'philosophy_text',
                'founder_1_name',
                'founder_1_image',
                'founder_1_facebook',
                'founder_1_linkedin',
                'founder_1_twitter',
                'founder_2_name',
                'founder_2_image',
                'founder_2_facebook',
                'founder_2_linkedin',
                'founder_2_twitter',
                'founders_common_message',
            ]);
        });
    }
};
