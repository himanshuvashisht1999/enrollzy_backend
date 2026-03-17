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
        Schema::create('campuses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('organisation_id')->constrained()->onDelete('cascade');
            
            // A. Identity & Mapping
            $table->string('campus_name');
            $table->string('slug')->unique();
            $table->enum('campus_type', ['Main', 'Regional', 'Satellite'])->default('Main');
            $table->year('established_year')->nullable();

            // B. Location Details
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->default('India');
            $table->string('pincode')->nullable();
            $table->text('full_address')->nullable();
            $table->string('google_map_url')->nullable();
            $table->string('nearest_transport_hub')->nullable();

            // C. Physical Infrastructure
            $table->decimal('campus_area_acres', 8, 2)->nullable();
            $table->integer('academic_blocks_count')->default(0);
            $table->integer('classrooms_count')->default(0);
            $table->boolean('smart_classrooms')->default(false);
            $table->integer('laboratories_count')->default(0);
            $table->boolean('library_available')->default(false);
            $table->integer('library_books_count')->default(0);
            $table->boolean('digital_library_access')->default(false);
            $table->integer('research_centers_count')->default(0);

            // D. Hostel & Amenities
            $table->boolean('hostel_available')->default(false);
            $table->enum('hostel_type', ['Boys', 'Girls', 'Both', 'None'])->nullable();
            $table->integer('hostel_capacity')->default(0);
            $table->string('food_facility')->nullable(); // Mess, Canteen, etc.
            $table->boolean('medical_facility_available')->default(false);
            $table->json('sports_facilities')->nullable(); // Array

            // E. Transport & Access
            $table->boolean('transport_available')->default(false);
            $table->integer('bus_routes_count')->default(0);
            $table->boolean('parking_available')->default(false);

            // F. Safety & Compliance
            $table->boolean('cctv_coverage')->default(false);
            $table->integer('security_staff_count')->default(0);
            $table->boolean('fire_safety_certified')->default(false);
            $table->boolean('disaster_management_plan')->default(false);

            // G. Digital & Contact
            $table->json('campus_contact_numbers')->nullable(); // Array
            $table->string('campus_email')->nullable();
            $table->string('campus_website')->nullable();

            // H. Admin & Control
            $table->boolean('verification_status')->default(false);
            // status: 1=Active, 0=Inactive
            $table->boolean('status')->default(true);
            $table->timestamp('last_updated_on')->useCurrent();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campuses');
    }
};
