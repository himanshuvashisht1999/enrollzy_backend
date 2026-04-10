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
        Schema::create('ar_form_orders', function (Blueprint $table) {
            $table->id(); 
            $table->string('order_no');
            $table->string('user_id');
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('landmark');
            $table->string('address');
            $table->string('city');
            $table->string('state');
            $table->string('pincode');
            $table->string('status');
            $table->string('store_id');
            $table->string('orderSource');
            $table->string('invoice_type');
            $table->string('order_notes');
            $table->string('invoicelookup');
            $table->string('gstin');
            $table->string('refund_limit');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ar_form_orders');
    }
};
