<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** A booking moved to another slot keeps its id, payment and commission; these record the last move. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'rescheduled_at')) $table->timestamp('rescheduled_at')->nullable()->after('meeting_link');
            if (!Schema::hasColumn('bookings', 'rescheduled_from_slot_id')) $table->unsignedBigInteger('rescheduled_from_slot_id')->nullable()->after('rescheduled_at');
            if (!Schema::hasColumn('bookings', 'rescheduled_by')) $table->string('rescheduled_by', 20)->nullable()->after('rescheduled_from_slot_id'); // student, expert, admin
            if (!Schema::hasColumn('bookings', 'reschedule_count')) $table->unsignedTinyInteger('reschedule_count')->default(0)->after('rescheduled_by');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            foreach (['rescheduled_at', 'rescheduled_from_slot_id', 'rescheduled_by', 'reschedule_count'] as $c) if (Schema::hasColumn('bookings', $c)) $table->dropColumn($c);
        });
    }
};
