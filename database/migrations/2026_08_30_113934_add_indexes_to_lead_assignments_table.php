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
        Schema::table('lead_assignments', function (Blueprint $table) {
            $table->index(['staff_id', 'created_at']);
            $table->index('customer_id');
            $table->index('assigned_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_assignments', function (Blueprint $table) {
            $table->dropIndex(['staff_id', 'created_at']);
            $table->dropIndex(['customer_id']);
            $table->dropIndex(['assigned_by']);
        });
    }
};
