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
        Schema::table('settings', function (Blueprint $table) {
            $table->string('hero_title')->nullable();
            $table->text('hero_description')->nullable();
            $table->text('hero_features')->nullable();
            $table->string('hero_cta_1_text')->nullable();
            $table->string('hero_cta_1_link')->nullable();
            $table->string('hero_cta_2_text')->nullable();
            $table->string('hero_cta_2_link')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            //
        });
    }
};
