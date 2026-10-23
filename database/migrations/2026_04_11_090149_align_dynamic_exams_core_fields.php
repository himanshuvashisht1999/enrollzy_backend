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
        Schema::table('dynamic_exams', function (Blueprint $table) {});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dynamic_exams', function (Blueprint $table) {
            if (!Schema::hasColumn('dynamic_exams', 'status')) $table->boolean('status')->default(true)->change();
            $table->dropColumn([
                'official_website',
                'visibility',
                'featured_exam',
                'has_stages',
                'selected_stages'
            ]);
        });
    }
};
