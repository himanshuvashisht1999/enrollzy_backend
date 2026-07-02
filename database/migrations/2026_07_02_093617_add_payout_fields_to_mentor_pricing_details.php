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
        Schema::table('mentor_pricing_details', function (Blueprint $table) {
            $table->string('upi_qr_code')->nullable();
            $table->string('bank_account_holder_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_ifsc_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mentor_pricing_details', function (Blueprint $table) {
            $table->dropColumn([
                'upi_qr_code', 
                'bank_account_holder_name', 
                'bank_account_number', 
                'bank_name', 
                'bank_ifsc_code'
            ]);
        });
    }
};
