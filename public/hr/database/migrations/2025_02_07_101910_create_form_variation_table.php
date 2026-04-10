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
        Schema::create('form_variation', function (Blueprint $table) {
            $table->id();
            $table->string('ar_form_id');
            $table->string('name');
            $table->string('fee');
            $table->timestamps();
            $table->softDeletes(); // Adds a deleted_at column for soft deletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_variation');
    }
};
