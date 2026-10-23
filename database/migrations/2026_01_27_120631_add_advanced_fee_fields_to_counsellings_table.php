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
        Schema::table('counsellings', function (Blueprint $table) {});
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
            if (!Schema::hasColumn('counsellings', 'counselling_fee_amount')) $table->decimal('counselling_fee_amount', 10, 2)->nullable();
            if (!Schema::hasColumn('counsellings', 'fee_currency')) $table->string('fee_currency')->nullable();
            if (!Schema::hasColumn('counsellings', 'fee_refundable')) $table->boolean('fee_refundable')->default(false);
            if (!Schema::hasColumn('counsellings', 'refund_conditions')) $table->text('refund_conditions')->nullable();
            if (!Schema::hasColumn('counsellings', 'payment_modes')) $table->json('payment_modes')->nullable();
        });
    }
};
