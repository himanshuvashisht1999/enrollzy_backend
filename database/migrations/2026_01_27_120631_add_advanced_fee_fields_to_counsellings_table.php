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
        Schema::table('counsellings', function (Blueprint $table) {
            // Drop old simple fee columns
            $table->dropColumn([
                'counselling_fee_amount',
                'fee_currency',
                'fee_refundable',
                'refund_conditions',
                'payment_modes'
            ]);

            // Add new advanced fee columns
            $table->boolean('registration_fee_required')->default(false);
            $table->json('registration_fee_structure')->nullable();

            $table->boolean('late_registration_allowed')->default(false);
            $table->json('late_fee_rules')->nullable();

            $table->boolean('security_deposit_required')->default(false);
            $table->json('security_deposit_structure')->nullable();

            $table->json('round_specific_fee_rules')->nullable();

            $table->text('refund_policy_summary')->nullable();
            $table->string('refund_timeline')->nullable();
            $table->string('refund_mode')->nullable(); // Original Method, Bank Transfer
            $table->json('forfeiture_scenarios')->nullable();

            $table->json('payment_modes_allowed')->nullable();
            $table->boolean('transaction_charges_applicable')->default(false);
            $table->string('transaction_charge_borne_by')->nullable(); // Candidate, Authority
            $table->string('payment_gateway_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('counsellings', function (Blueprint $table) {
            // Drop new advanced fee columns
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

            // Re-add old simple fee columns
            $table->decimal('counselling_fee_amount', 10, 2)->nullable();
            $table->string('fee_currency')->nullable();
            $table->boolean('fee_refundable')->default(false);
            $table->text('refund_conditions')->nullable();
            $table->json('payment_modes')->nullable();
        });
    }
};
