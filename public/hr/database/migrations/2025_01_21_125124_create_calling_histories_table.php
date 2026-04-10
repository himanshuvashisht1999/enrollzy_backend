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
        Schema::create('calling_histories', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('category_id')->nullable();
            $table->string('institute_id')->nullable();
            $table->string('user_name');
            $table->string('user_phone');
            $table->string('reason');
            $table->string('comment');
            $table->string('updated_by');
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calling_histories');
    }
};
