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
        Schema::table('users', function (Blueprint $table) {
            $table->index('phone');
            $table->index('mobile');
            $table->index('role');
            $table->index('organization_id');
            $table->index('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['phone']);
            $table->dropIndex(['mobile']);
            $table->dropIndex(['role']);
            $table->dropIndex(['organization_id']);
            $table->dropIndex(['category_id']);
        });
    }
};
