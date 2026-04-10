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
        Schema::create('ar_forms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->string('category_id');
            $table->string('sub_category_id')->nullable();
            $table->string('url');
            $table->string('sku');
            $table->string('is_refundable');
            $table->string('service_charge');
            $table->string('gst');
            $table->string('base_price');
            $table->string('form_fee');
            $table->string('final_price');
            $table->string('images')->nullable();
            $table->string('description')->nullable();
            $table->string('tags')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ar_forms');
    }
};
