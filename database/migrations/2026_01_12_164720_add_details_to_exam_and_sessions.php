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
        Schema::table('exams', function (Blueprint $table) {
            $table->longText('admit_card_download_procedure')->nullable()->after('application_helpdesk_details');
            $table->longText('result_check_procedure')->nullable()->after('admit_card_download_procedure');
        });

        Schema::table('exam_sessions', function (Blueprint $table) {
            $table->string('admit_card_url')->nullable()->after('admit_card_release_date');
            $table->string('result_url')->nullable()->after('result_declaration_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['admit_card_download_procedure', 'result_check_procedure']);
        });

        Schema::table('exam_sessions', function (Blueprint $table) {
            $table->dropColumn(['admit_card_url', 'result_url']);
        });
    }
};
