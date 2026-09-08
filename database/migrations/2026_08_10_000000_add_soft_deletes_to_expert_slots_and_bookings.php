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
        if (Schema::hasTable('expert_slots') && !Schema::hasColumn('expert_slots', 'deleted_at')) {
            Schema::table('expert_slots', function (Blueprint $table) {});
        }

        if (Schema::hasTable('bookings') && !Schema::hasColumn('bookings', 'deleted_at')) {
            Schema::table('bookings', function (Blueprint $table) {});
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('expert_slots') && Schema::hasColumn('expert_slots', 'deleted_at')) {
            Schema::table('expert_slots', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'deleted_at')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
