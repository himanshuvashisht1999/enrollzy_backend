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
        if (!Schema::hasTable('organisation_field_configs')) {
            Schema::create('organisation_field_configs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organisation_type_id')->unique();
                $table->json('fields_config')->nullable();
                $table->timestamps();

                $table->foreign('organisation_type_id')
                      ->references('id')
                      ->on('organisation_types')
                      ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organisation_field_configs');
    }
};
