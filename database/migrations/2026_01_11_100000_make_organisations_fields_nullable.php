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
        Schema::table('organisations', function (Blueprint $table) {
            $table->string('mode')->nullable()->change();
            $table->string('approvals')->nullable()->change();
            $table->string('fees')->nullable()->change();
            $table->string('placement')->nullable()->change();
            $table->decimal('rating', 3, 1)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            // No operation for down to avoid data issues
        });
    }
};
