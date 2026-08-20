<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('filtered_pages', function (Blueprint $table) {
            $table->string('university_type')->nullable()->after('curriculum');
            $table->string('degree')->nullable()->after('university_type');
            $table->unsignedBigInteger('stream_id')->nullable()->after('degree');
        });
    }

    public function down(): void
    {
        Schema::table('filtered_pages', function (Blueprint $table) {
            $table->dropColumn(['university_type', 'degree', 'stream_id']);
        });
    }
};
