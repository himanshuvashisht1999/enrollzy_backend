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
        Schema::create('advance_pay_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('amount');
            $table->string('advance_pay_ids');
            $table->string('transaction_type');
            $table->string('transaction_for');
            $table->string('log');
            $table->string('staff_id');
            $table->string('status');
            $table->string('payment_method');
            $table->string('debit_account');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advance_pay_transactions');
    }
};
