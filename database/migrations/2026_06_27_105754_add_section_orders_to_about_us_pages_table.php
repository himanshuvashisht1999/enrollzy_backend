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
            $defaultOrder = json_encode(['hero', 'story', 'core_values', 'offers', 'features', 'impacts', 'founders', 'teams', 'cta']);
            $table->json('section_orders')->default($defaultOrder)->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('about_us_pages', function (Blueprint $table) {
            $table->dropColumn('section_orders');
        });
    }
};
