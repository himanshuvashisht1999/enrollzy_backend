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
        Schema::table('university_courses', function (Blueprint $table) {
            $table->foreignId('course_id')->nullable()->after('university_id')->constrained()->onDelete('cascade');
            $table->dropColumn('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('university_courses', function (Blueprint $table) {
            $table->string('name')->after('university_id');
            $table->dropConstrainedForeignId('course_id');
        });
    }
};
