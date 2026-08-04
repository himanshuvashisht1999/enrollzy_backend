<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('gst_number')->nullable()->after('address');
        });

        // Use raw SQL to alter ENUM values
        DB::statement("ALTER TABLE billing_payments MODIFY COLUMN payment_mode ENUM('Bank Transfer', 'UPI', 'Cash', 'Cheque', 'TDS') NOT NULL DEFAULT 'Bank Transfer'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('gst_number');
        });

        DB::statement("ALTER TABLE billing_payments MODIFY COLUMN payment_mode ENUM('Bank Transfer', 'UPI', 'Cash', 'Cheque') NOT NULL DEFAULT 'Bank Transfer'");
    }
};

