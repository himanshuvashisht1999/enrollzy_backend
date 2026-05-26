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
        Schema::table('homepage_sections', function (Blueprint $table) {
            $table->string('title')->nullable()->after('name');
            $table->text('subtitle')->nullable()->after('title');
            $table->string('cta_title')->nullable()->after('subtitle');
            $table->string('cta_url')->nullable()->after('cta_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('homepage_sections', function (Blueprint $table) {
            $table->dropColumn(['title', 'subtitle', 'cta_title', 'cta_url']);
        });
    }
};
