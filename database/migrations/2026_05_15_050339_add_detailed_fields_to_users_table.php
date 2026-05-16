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
        Schema::table('users', function (Blueprint $table) {
            // Personal Details
            $table->date('dob')->nullable();
            $table->string('gender')->nullable();
            $table->string('aadhaar_number')->nullable();
            $table->string('alternate_mobile')->nullable();
            
            // Course / Program of Interest
            $table->json('interested_in_ids')->nullable(); // Multi-select
            $table->string('interested_in_course')->nullable();
            $table->string('program_level')->nullable();
            $table->string('mode')->nullable(); // Offline, Online, Hybrid
            $table->json('session_ids')->nullable(); // Multi-select
            
            // Parents / Guardian Details
            $table->string('father_name')->nullable();
            $table->string('father_mobile')->nullable();
            $table->string('father_email')->nullable();
            $table->string('father_occupation')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('mother_mobile')->nullable();
            $table->string('mother_email')->nullable();
            $table->string('mother_occupation')->nullable();
            
            // Additional Information
            $table->boolean('sibling_enrolled')->default(false);
            $table->string('referred_by')->nullable();
            $table->string('source')->nullable();
            
            // Office Use
            $table->string('registration_no')->nullable();
            $table->string('class_batch')->nullable();
            $table->string('counselor_name')->nullable();
            $table->date('registration_date')->nullable();
            $table->string('payment_status')->nullable();
            $table->text('remarks')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'dob', 'gender', 'aadhaar_number', 'alternate_mobile',
                'interested_in_ids', 'interested_in_course', 'program_level', 'mode', 'session_ids',
                'father_name', 'father_mobile', 'father_email', 'father_occupation',
                'mother_name', 'mother_mobile', 'mother_email', 'mother_occupation',
                'sibling_enrolled', 'referred_by', 'source',
                'registration_no', 'class_batch', 'counselor_name', 'registration_date', 'payment_status', 'remarks'
            ]);
        });
    }
};
