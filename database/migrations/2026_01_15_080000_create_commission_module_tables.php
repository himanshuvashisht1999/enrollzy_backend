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
        // 1. Commission Policies (Global & Category)
        Schema::create('commission_policies', function (Blueprint $table) {
            $table->id();
            $table->string('policy_type')->default('global'); // global, category
            $table->string('expert_category')->nullable(); // Matches 'role' in Expert table (e.g. 'Career Counsellor')
            $table->string('commission_type')->default('percentage'); // percentage, flat_fee
            $table->decimal('commission_value', 10, 2); // 10.00 for 10%
            $table->boolean('gst_applicable')->default(true); // Is GST calculated on top?
            $table->boolean('tds_applicable')->default(true); // Is TDS deducted?
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Expert Specific Commissions
        Schema::create('expert_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expert_id')->constrained('experts')->onDelete('cascade');
            $table->string('commission_type')->default('percentage'); // percentage, flat_fee
            $table->decimal('commission_value', 10, 2);
            $table->string('reason')->nullable(); // "Contract 2024", "Early Bird"
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Commission Logs (Audit Trail)
        Schema::create('commission_logs', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type'); // Policy, ExpertCommission, Booking
            $table->unsignedBigInteger('entity_id');
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->unsignedBigInteger('action_by')->nullable(); // Admin User ID
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        // 4. Update Bookings Table (Snapshotting)
        Schema::table('bookings', function (Blueprint $table) {
            // Manual Override Fields (For Admin to intervene)
            $table->string('commission_override_type')->nullable(); // percentage, flat_fee
            $table->decimal('commission_override_value', 10, 2)->nullable();
            $table->text('override_reason')->nullable();
            $table->unsignedBigInteger('override_by')->nullable();

            // Applied Commission Snapshot (The "Source of Truth" for this booking)
            $table->string('applied_commission_type')->nullable(); // global, category, expert, override
            $table->decimal('applied_commission_rate', 10, 2)->nullable(); // The value used (e.g., 20.00)
            $table->decimal('applied_gst_rate', 5, 2)->default(18.00);
            $table->decimal('applied_tds_rate', 5, 2)->default(10.00);
            $table->json('commission_breakdown')->nullable(); // Stores full calculation logic in JSON
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'commission_override_type', 
                'commission_override_value', 
                'override_reason', 
                'override_by',
                'applied_commission_type',
                'applied_commission_rate',
                'applied_gst_rate',
                'applied_tds_rate',
                'commission_breakdown'
            ]);
        });
        Schema::dropIfExists('commission_logs');
        Schema::dropIfExists('expert_commissions');
        Schema::dropIfExists('commission_policies');
    }
};
