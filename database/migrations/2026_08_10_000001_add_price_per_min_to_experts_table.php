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
        if (Schema::hasTable('experts') && !Schema::hasColumn('experts', 'price_per_min')) {
            Schema::table('experts', function (Blueprint $table) {
                $table->decimal('price_per_min', 10, 2)->default(10.00)->after('email');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('experts') && Schema::hasColumn('experts', 'price_per_min')) {
            Schema::table('experts', function (Blueprint $table) {
                $table->dropColumn('price_per_min');
            });
        }
    }
};
