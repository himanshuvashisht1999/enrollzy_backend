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
        if (!Schema::hasTable('organisation_documents')) {
            Schema::create('organisation_documents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organisation_id');
                $table->string('title');
                $table->string('file_path');
                $table->string('file_name')->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('organisation_id')
                      ->references('id')
                      ->on('organisations')
                      ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organisation_documents');
    }
};
