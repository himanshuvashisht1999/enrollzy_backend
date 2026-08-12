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
        Schema::table('admin', function (Blueprint $table) {
            $table->unsignedBigInteger('staff_type_id')->nullable()->after('id');
            $table->foreign('staff_type_id')->references('id')->on('staff_types')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin', function (Blueprint $table) {
            $table->dropForeign(['staff_type_id']);
            $table->dropColumn('staff_type_id');
        });
    }
};
