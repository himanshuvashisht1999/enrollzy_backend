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
        Schema::create('organisation_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained()->cascadeOnDelete();
            $table->string('grade'); // Class 1, Class 10, etc.
            $table->decimal('tuition_fee_annual', 10, 2)->nullable();
            $table->decimal('admission_fee', 10, 2)->nullable();
            $table->decimal('development_fee', 10, 2)->nullable();
            $table->decimal('transport_fee', 10, 2)->nullable();
            $table->decimal('hostel_fee', 10, 2)->nullable();
            $table->decimal('other_charges', 10, 2)->nullable();
            $table->text('scholarship_details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organisation_fees');
    }
};
