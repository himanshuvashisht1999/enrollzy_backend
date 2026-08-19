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
            $table->string('program_level_text')->nullable()->after('program_level_id');
            $table->string('school_type_text')->nullable()->after('school_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calling_histories', function (Blueprint $table) {
            $table->dropColumn(['program_level_text', 'school_type_text']);
        });
    }
};
