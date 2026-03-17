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
            $table->boolean('registration_fee_required')->default(false);
            $table->json('registration_fee_structure')->nullable();

            $table->boolean('late_registration_allowed')->default(false);
            $table->json('late_fee_rules')->nullable();

            $table->boolean('security_deposit_required')->default(false);
            $table->json('security_deposit_structure')->nullable();

            $table->json('round_specific_fee_rules')->nullable();

            $table->text('refund_policy_summary')->nullable();
            $table->string('refund_timeline')->nullable();
            $table->string('refund_mode')->nullable();
            $table->json('forfeiture_scenarios')->nullable();

            $table->json('payment_modes_allowed')->nullable();
            $table->boolean('transaction_charges_applicable')->default(false);
            $table->string('transaction_charge_borne_by')->nullable();
            $table->string('payment_gateway_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn([
                'registration_fee_required',
                'registration_fee_structure',
                'late_registration_allowed',
                'late_fee_rules',
                'security_deposit_required',
                'security_deposit_structure',
                'round_specific_fee_rules',
                'refund_policy_summary',
                'refund_timeline',
                'refund_mode',
                'forfeiture_scenarios',
                'payment_modes_allowed',
                'transaction_charges_applicable',
                'transaction_charge_borne_by',
                'payment_gateway_name'
            ]);
        });
    }
};
