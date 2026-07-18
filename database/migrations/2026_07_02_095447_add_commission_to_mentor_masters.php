<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mentor_degrees', function (Blueprint $table) {
            $table->decimal('commission_percentage', 5, 2)->nullable();
        });
        Schema::table('mentor_industries', function (Blueprint $table) {
            $table->decimal('commission_percentage', 5, 2)->nullable();
        });
        Schema::table('mentor_mentee_levels', function (Blueprint $table) {
            $table->decimal('commission_percentage', 5, 2)->nullable();
        });
        Schema::table('mentor_commissions', function (Blueprint $table) {
            $table->json('priority_order')->nullable();
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
