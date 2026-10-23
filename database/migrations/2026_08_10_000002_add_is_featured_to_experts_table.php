<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('experts') && !Schema::hasColumn('experts', 'is_featured')) {
            Schema::table('experts', function (Blueprint $table) {});
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('experts') && Schema::hasColumn('experts', 'is_featured')) {
            Schema::table('experts', function (Blueprint $table) {
                $table->dropColumn('is_featured');
            });
        }
    }
};
