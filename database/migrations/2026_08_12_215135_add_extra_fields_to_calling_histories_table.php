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
            $table->unsignedBigInteger('university_id')->nullable()->after('calling_action_id');
            $table->string('university_text')->nullable()->after('university_id');
            $table->unsignedBigInteger('course_id')->nullable()->after('university_text');
            $table->string('course_text')->nullable()->after('course_id');
            $table->enum('course_type', ['online', 'offline'])->nullable()->after('course_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calling_histories', function (Blueprint $table) {
            $table->dropColumn(['university_id', 'university_text', 'course_id', 'course_text', 'course_type']);
        });
    }
};
