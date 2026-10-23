<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A confirmed address is not consent to every follow someone else creates with it:
        // each follow is confirmed on its own (unless the request proves it is the same person).
        Schema::table('cns_subscriptions', function (Blueprint $table) {
            $table->dateTime('confirmed_at')->nullable()->after('alert_types');
        });
    }

    public function down(): void
    {
        Schema::table('cns_subscriptions', fn (Blueprint $t) => $t->dropColumn('confirmed_at'));
    }
};
