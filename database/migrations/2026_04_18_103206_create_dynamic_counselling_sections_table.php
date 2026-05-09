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
        Schema::create('dynamic_counselling_sections', function (Blueprint $table) {
            $table->id();
            $table->uuid('counselling_id');
            $table->foreign('counselling_id')->references('id')->on('counsellings')->onDelete('cascade');
            $table->string('heading');
            $table->json('content')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dynamic_counselling_sections');
    }
};
