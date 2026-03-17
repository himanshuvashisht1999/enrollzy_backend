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
        Schema::create('universities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('mode'); // Online/Distance, Online, etc.
            $table->string('approvals'); // UGC, AICTE, etc.
            $table->string('fees'); // ₹1,50,000 etc.
            $table->string('placement'); // Yes, Limited, etc.
            $table->decimal('rating', 3, 1); // 4.5, 4.6, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('universities');
    }
};
