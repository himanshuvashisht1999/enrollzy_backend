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
        Schema::table('billing_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('billing_invoices', 'client_type')) {
                $table->string('client_type', 50)->default('organisation')->after('invoice_number');
            }
            if (!Schema::hasColumn('billing_invoices', 'billing_client_id')) {
                $table->foreignId('billing_client_id')->nullable()->after('client_type')->constrained('billing_clients')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billing_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('billing_invoices', 'billing_client_id')) {
                $table->dropForeign(['billing_client_id']);
                $table->dropColumn('billing_client_id');
            }
            if (Schema::hasColumn('billing_invoices', 'client_type')) {
                $table->dropColumn('client_type');
            }
        });
    }
};
