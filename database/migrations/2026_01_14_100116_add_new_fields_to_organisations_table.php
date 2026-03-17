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
        Schema::table('organisations', function (Blueprint $table) {
            $table->string('organisation_id_number')->nullable()->after('id');
            $table->string('brand_type')->nullable()->after('organisation_type_id'); // Independent, Chain, Franchise
            $table->string('central_authority')->nullable()->after('brand_type');
            $table->string('head_office_location')->nullable()->after('head_office_city'); // General location field if separate from structured address
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropColumn(['organisation_id_number', 'brand_type', 'central_authority', 'head_office_location']);
        });
    }
};
