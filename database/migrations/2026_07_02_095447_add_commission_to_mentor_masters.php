<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mentor_degrees', function (Blueprint $table) {});
        Schema::table('mentor_industries', function (Blueprint $table) {});
        Schema::table('mentor_mentee_levels', function (Blueprint $table) {});
        Schema::table('mentor_commissions', function (Blueprint $table) {
            if (!Schema::hasColumn('mentor_commissions', 'priority_order')) {
                if (!Schema::hasColumn('mentor_commissions', 'priority_order')) $table->json('priority_order')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('mentor_degrees', function (Blueprint $table) {
            $table->dropColumn('commission_percentage');
        });
        Schema::table('mentor_industries', function (Blueprint $table) {
            $table->dropColumn('commission_percentage');
        });
        Schema::table('mentor_mentee_levels', function (Blueprint $table) {
            $table->dropColumn('commission_percentage');
        });
        Schema::table('mentor_commissions', function (Blueprint $table) {
            $table->dropColumn('priority_order');
        });
    }
};
