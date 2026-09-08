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
