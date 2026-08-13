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
            $table->date('meeting_date')->nullable();
            $table->time('time_slot')->nullable();
            $table->text('meeting_link')->nullable();
            $table->unsignedBigInteger('assign_to_staff_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calling_histories', function (Blueprint $table) {
            $table->dropColumn(['meeting_date', 'time_slot', 'meeting_link', 'assign_to_staff_id']);
        });
    }
};
