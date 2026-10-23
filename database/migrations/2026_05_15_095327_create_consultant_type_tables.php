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
        if (!Schema::hasTable('consultant_types')) {
        Schema::create('consultant_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status')->default('active');
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->timestamps();
        });
        }

        if (!Schema::hasTable('consultant_statuses')) {
        Schema::create('consultant_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->timestamps();
        });
        }

        if (!Schema::hasTable('consultant_access_levels')) {
        Schema::create('consultant_access_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->timestamps();
        });
        }

        if (!Schema::hasTable('consultant_lead_visibilities')) {
        Schema::create('consultant_lead_visibilities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key')->unique();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->timestamps();
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultant_types');
        Schema::dropIfExists('consultant_statuses');
        Schema::dropIfExists('consultant_access_levels');
        Schema::dropIfExists('consultant_lead_visibilities');
    }
};
