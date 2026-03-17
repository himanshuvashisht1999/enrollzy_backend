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
        Schema::table('counsellings', function (Blueprint $table) {
            $table->boolean('partial_refund_allowed')->default(false)->after('security_deposit_required');
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->boolean('partial_refund_allowed')->default(false)->after('security_deposit_required');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('counsellings', function (Blueprint $table) {
            $table->dropColumn('partial_refund_allowed');
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('partial_refund_allowed');
        });
    }
};
