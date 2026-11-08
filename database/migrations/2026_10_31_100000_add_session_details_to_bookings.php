<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The website's booking page and the student's dashboard: who books and what for, the expert's written summary after
 * the call, and a student's request to cancel (the team cancels it in the CRM).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'topic')) $table->string('topic', 120)->nullable()->after('notes');                   // what they want help with
            if (!Schema::hasColumn('bookings', 'attendee')) $table->string('attendee', 20)->nullable()->after('topic');              // Student, Parent, Student and parent
            if (!Schema::hasColumn('bookings', 'class_level')) $table->string('class_level', 40)->nullable()->after('attendee');     // e.g. Class 11–12
            if (!Schema::hasColumn('bookings', 'phone')) $table->string('phone', 20)->nullable()->after('class_level');              // contact number for this session
            if (!Schema::hasColumn('bookings', 'session_summary')) $table->text('session_summary')->nullable()->after('meeting_link'); // written by the expert after the call
            if (!Schema::hasColumn('bookings', 'cancel_requested_at')) $table->timestamp('cancel_requested_at')->nullable()->after('session_summary');
            if (!Schema::hasColumn('bookings', 'cancel_reason')) $table->string('cancel_reason', 500)->nullable()->after('cancel_requested_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            foreach (['topic', 'attendee', 'class_level', 'phone', 'session_summary', 'cancel_requested_at', 'cancel_reason'] as $c) if (Schema::hasColumn('bookings', $c)) $table->dropColumn($c);
        });
    }
};
