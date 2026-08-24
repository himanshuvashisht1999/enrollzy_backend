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
        Schema::table('calling_status', function (Blueprint $table) {
            $table->enum('current_academic_details', ['yes', 'no'])->default('no')->after('is_more_details');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calling_status', function (Blueprint $table) {
            $table->dropColumn('current_academic_details');
        });
    }
};
