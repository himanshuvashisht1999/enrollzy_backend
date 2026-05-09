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
            if (!Schema::hasColumn('calling_histories', 'organization_id')) {
                $table->integer('organization_id')->nullable()->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calling_histories', function (Blueprint $table) {
            $table->dropColumn('organization_id');
        });
    }
};
