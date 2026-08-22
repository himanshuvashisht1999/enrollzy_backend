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
        Schema::create('target_leads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id');
            $table->integer('year');
            $table->string('month');
            $table->integer('month_target_calling')->default(0);
            $table->integer('month_target_admissions')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('target_leads');
    }
};
