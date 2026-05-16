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
        Schema::create('consultants', function (Blueprint $table) {
            $table->id();
            
            // 1. Basic Consultant Details
            $table->string('consultant_id')->unique();
            $table->string('full_name');
            $table->string('business_name')->nullable();
            $table->string('phone')->unique();
            $table->string('alternate_mobile')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('image')->nullable();
            $table->string('gender')->nullable();
            $table->date('dob')->nullable();

            // 2. Business Details
            $table->string('consultant_type')->nullable(); // Individual / Agency / Freelancer / Institute
            $table->boolean('is_gst_registered')->default(false);
            $table->string('gst_number')->nullable();
            $table->string('pan_number')->nullable();
            $table->string('aadhaar_number')->nullable();
            $table->integer('years_of_experience')->nullable();
            $table->string('team_size')->nullable();
            $table->text('office_address')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('pincode')->nullable();
            $table->string('website')->nullable();
            $table->string('linkedin_profile')->nullable();

            // 3. Specialization & Expertise
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('sub_category_id')->nullable();
            $table->unsignedBigInteger('sub_sub_category_id')->nullable();
            $table->string('expertise_level')->nullable(); // Beginner, Intermediate, Expert
            $table->json('preferred_universities')->nullable();
            $table->json('preferred_courses')->nullable();
            $table->json('preferred_modes_of_study')->nullable(); // Online / Regular / Distance / Hybrid

            // 4. Lead Source Information
            $table->boolean('generates_own_leads')->default(false);
            $table->boolean('requires_company_leads')->default(false);
            $table->boolean('runs_ads')->default(false);
            $table->boolean('has_counseling_office')->default(false);
            $table->boolean('walk_in_students')->default(false);
            $table->integer('approx_leads_per_month')->nullable();

            // 5. Geographic Working Area
            $table->json('working_states')->nullable();
            $table->json('working_cities')->nullable();
            $table->boolean('can_handle_pan_india')->default(false);
            $table->json('languages_known')->nullable();

            // 6. Bank & Payout Details
            $table->string('account_holder_name')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('upi_id')->nullable();
            $table->string('qr_code_upload')->nullable();
            $table->string('cancelled_cheque_upload')->nullable();
            $table->string('pan_card_upload')->nullable();

            // 7. Documents & Verification
            $table->string('aadhaar_upload')->nullable();
            $table->string('pan_upload')->nullable();
            $table->string('gst_certificate_upload')->nullable();
            $table->string('business_registration_upload')->nullable();
            $table->string('visiting_card_upload')->nullable();
            $table->json('office_photos')->nullable();

            // 8. CRM Access & Permissions
            $table->enum('status', ['active', 'inactive', 'pending', 'blocked'])->default('active');
            $table->string('access_level')->nullable();
            $table->string('lead_visibility')->nullable();
            $table->boolean('lead_assignment_allowed')->default(true);

            $table->unsignedBigInteger('organization_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultants');
    }
};
