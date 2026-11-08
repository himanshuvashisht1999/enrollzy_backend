<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * OTP abuse protection for the website's mobile sign-in: every OTP request (sent or refused) with its IP and number,
 * and IP addresses the team has restricted for good (CRM: Security > OTP & blocked IPs).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('otp_requests')) {
            Schema::create('otp_requests', function (Blueprint $table) {
                $table->id();
                $table->string('ip', 45);
                $table->string('mobile', 15)->nullable();
                // sent, widget (the live site's MSG91 widget sends it), paused (2 in a row), daily (10 in 24 hours), blocked (IP restricted)
                $table->string('outcome', 12);
                $table->string('user_agent', 255)->nullable();
                $table->string('page', 255)->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->index(['ip', 'created_at']);
                $table->index(['mobile', 'created_at']);
                $table->index('created_at');
            });
        }
        if (!Schema::hasTable('blocked_ips')) {
            Schema::create('blocked_ips', function (Blueprint $table) {
                $table->id();
                $table->string('ip', 45)->unique();
                $table->string('reason', 500)->nullable();
                $table->unsignedBigInteger('blocked_by')->nullable(); // admin id
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('blocked_ips');
        Schema::dropIfExists('otp_requests');
    }
};
