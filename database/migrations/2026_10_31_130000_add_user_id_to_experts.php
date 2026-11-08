<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One login on the website: an expert signs in with mobile OTP like everyone else, and the account (users) is linked
 * to the expert profile by its mobile number (experts.contact_number) the first time; then they land on the website's
 * expert dashboard. Staff keep their CRM login.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('experts', function (Blueprint $table) {
            if (!Schema::hasColumn('experts', 'user_id')) $table->unsignedBigInteger('user_id')->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('experts', function (Blueprint $table) {
            if (Schema::hasColumn('experts', 'user_id')) { $table->dropUnique(['user_id']); $table->dropColumn('user_id'); }
        });
    }
};
