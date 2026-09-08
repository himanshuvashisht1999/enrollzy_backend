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
        Schema::table('exams', function (Blueprint $table) {});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            if (!Schema::hasColumn('exams', 'application_mode')) $table->string('application_mode')->nullable();
            if (!Schema::hasColumn('exams', 'application_steps')) $table->json('application_steps')->nullable();
            if (!Schema::hasColumn('exams', 'documents_required')) $table->json('documents_required')->nullable();
            if (!Schema::hasColumn('exams', 'application_fee')) $table->decimal('application_fee', 10, 2)->nullable();
            if (!Schema::hasColumn('exams', 'fee_currency')) $table->string('fee_currency')->default('INR');
            if (!Schema::hasColumn('exams', 'payment_modes')) $table->json('payment_modes')->nullable();
            if (!Schema::hasColumn('exams', 'application_helpdesk_details')) $table->text('application_helpdesk_details')->nullable();
        });
    }
};
