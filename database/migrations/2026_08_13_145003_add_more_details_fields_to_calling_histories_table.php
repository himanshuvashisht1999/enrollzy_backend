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
        Schema::table('calling_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('program_level_id')->nullable()->after('category_id');
            $table->string('session')->nullable()->after('course_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calling_histories', function (Blueprint $table) {
            $table->dropColumn(['program_level_id', 'session']);
        });
    }
};
