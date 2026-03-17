<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('campuses', function (Blueprint $table) {
            $table->json('exams_prepared_for')->nullable()->after('status');
            $table->json('target_classes')->nullable()->after('exams_prepared_for');
            $table->longText('about_institute')->nullable()->after('target_classes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campuses', function (Blueprint $table) {
            $table->dropColumn(['exams_prepared_for', 'target_classes', 'about_institute']);
        });
    }
};
