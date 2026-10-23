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
        if (!Schema::hasTable('billing_clients')) {
            Schema::create('billing_clients', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('company_type')->nullable(); // e.g. Private Limited, Public Limited, LLP, Partnership, Sole Proprietorship, Trust / Society, Individual / Other
                $table->string('contact_person')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('gstin', 25)->nullable();
                $table->string('tan_number', 25)->nullable();
                $table->string('pan_number', 25)->nullable();
                $table->text('address')->nullable();
                $table->string('city', 100)->nullable();
                $table->string('state', 100)->nullable();
                $table->string('pincode', 20)->nullable();
                $table->string('country', 100)->default('India');
                $table->boolean('status')->default(1);
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_clients');
    }
};
