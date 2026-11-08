<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Billing details (address, GSTIN, TAN, PAN, CIN ...) of the organisations and campuses we invoice, for the billing
 * module only (App\Models\BillingProfile). campus_id '' is the organisation's own row; a campus row can say
 * "same as organisation". The website never reads this table.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('billing_profiles')) {
            Schema::create('billing_profiles', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organisation_id');
                $table->string('campus_id', 36)->default(''); // campuses use UUIDs; '' = the organisation itself
                $table->boolean('same_as_organisation')->default(false); // campus rows: bill with the organisation's details
                $table->string('legal_name')->nullable();
                $table->string('contact_person')->nullable();
                $table->string('email')->nullable();
                $table->string('phone', 50)->nullable();
                $table->string('gstin', 25)->nullable();
                $table->string('tan_number', 25)->nullable();
                $table->string('pan_number', 25)->nullable();
                $table->string('cin_number', 50)->nullable();
                $table->text('address')->nullable();
                $table->string('city', 100)->nullable();
                $table->string('state', 100)->nullable();
                $table->string('pincode', 20)->nullable();
                $table->string('country', 100)->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->unique(['organisation_id', 'campus_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_profiles');
    }
};
