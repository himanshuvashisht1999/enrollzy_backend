<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn([
                'application_mode',
                'application_steps',
                'documents_required',
                'application_fee',
                'fee_currency',
                'payment_modes',
                'application_helpdesk_details'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->string('application_mode')->nullable();
            $table->json('application_steps')->nullable();
            $table->json('documents_required')->nullable();
            $table->decimal('application_fee', 10, 2)->nullable();
            $table->string('fee_currency')->default('INR');
            $table->json('payment_modes')->nullable();
            $table->text('application_helpdesk_details')->nullable();
        });
    }
};
