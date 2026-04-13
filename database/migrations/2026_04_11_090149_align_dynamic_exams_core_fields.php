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
        Schema::table('dynamic_exams', function (Blueprint $table) {
            // Change status from boolean to string to match Exam model
            $table->string('status')->default('Active')->change();
            
            // Add missing core fields
            $table->string('official_website')->nullable()->after('about_exam');
            $table->string('visibility')->default('Public')->after('official_website');
            $table->boolean('featured_exam')->default(false)->after('visibility');
            $table->boolean('has_stages')->default(false)->after('featured_exam');
            $table->json('selected_stages')->nullable()->after('has_stages');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dynamic_exams', function (Blueprint $table) {
            $table->boolean('status')->default(true)->change();
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
