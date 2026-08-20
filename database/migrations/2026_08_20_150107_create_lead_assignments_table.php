<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('staff_id');
            $table->unsignedBigInteger('assigned_by');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_assignments');
    }
};
