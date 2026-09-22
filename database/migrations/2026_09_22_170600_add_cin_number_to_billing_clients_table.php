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
        Schema::table('billing_clients', function (Blueprint $table) {
            if (!Schema::hasColumn('billing_clients', 'cin_number')) {
                $table->string('cin_number', 50)->nullable()->after('pan_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billing_clients', function (Blueprint $table) {
            if (Schema::hasColumn('billing_clients', 'cin_number')) {
                $table->dropColumn('cin_number');
            }
        });
    }
};
