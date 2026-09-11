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
            if (!Schema::hasColumn('dynamic_exams', 'result_status')) {
                $table->string('result_status')->nullable()->after('exam_frequency');
            }
            if (!Schema::hasColumn('dynamic_exams', 'registration_status')) {
                $table->string('registration_status')->nullable()->after('result_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dynamic_exams', function (Blueprint $table) {
            $table->dropColumn(['result_status', 'registration_status']);
        });
    }
};
