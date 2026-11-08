<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Expert payouts (admin → Expert Payouts): when a payout was paid, and a note for it. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payouts', function (Blueprint $table) {
            if (!Schema::hasColumn('payouts', 'paid_at')) $table->timestamp('paid_at')->nullable()->after('period_end');
            if (!Schema::hasColumn('payouts', 'notes')) $table->text('notes')->nullable()->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('payouts', function (Blueprint $table) {
            foreach (['paid_at', 'notes'] as $c) if (Schema::hasColumn('payouts', $c)) $table->dropColumn($c);
        });
    }
};
