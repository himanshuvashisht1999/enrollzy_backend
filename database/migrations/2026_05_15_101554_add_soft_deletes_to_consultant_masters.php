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
        if (Schema::hasTable('consultant_lead_visibilities')) {
            Schema::table('consultant_lead_visibilities', function (Blueprint $table) {
                if (Schema::hasColumn('consultant_lead_visibilities', 'key')) {
                    $table->dropColumn('key');
                }
                if (!Schema::hasColumn('consultant_lead_visibilities', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        if (Schema::hasTable('consultant_types')) {
            Schema::table('consultant_types', function (Blueprint $table) {
                if (!Schema::hasColumn('consultant_types', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        if (Schema::hasTable('consultant_access_levels')) {
            Schema::table('consultant_access_levels', function (Blueprint $table) {
                if (!Schema::hasColumn('consultant_access_levels', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultant_statuses', function (Blueprint $table) {
            if (!Schema::hasColumn('consultant_statuses', 'color')) $table->string('color')->nullable();
            $table->dropSoftDeletes();
        });

        Schema::table('consultant_lead_visibilities', function (Blueprint $table) {
            if (!Schema::hasColumn('consultant_lead_visibilities', 'key')) $table->string('key')->unique();
            $table->dropSoftDeletes();
        });

        Schema::table('consultant_types', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('consultant_access_levels', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
