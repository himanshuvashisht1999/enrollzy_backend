<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The secret the website and the CRM sign expert-document requests with (App\Support\Experts\Bridge). On the live
 * server each site can only reach its own folders, so the website sends uploads to the CRM and fetches files from it
 * over HTTPS; both read this key from the shared database. Filled with a random value on first use.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'expert_docs_key')) $table->string('expert_docs_key', 64)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'expert_docs_key')) $table->dropColumn('expert_docs_key');
        });
    }
};
