<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Chart of Accounts table (Hierarchical Double-Entry Structure)
        if (!Schema::hasTable('chart_of_accounts')) {
            Schema::create('chart_of_accounts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->string('account_code', 50)->unique();
                $table->string('name', 255);
                $table->enum('account_type', ['asset', 'liability', 'equity', 'revenue', 'expense', 'tax'])->index();
                $table->unsignedBigInteger('parent_id')->nullable()->index();
                $table->text('description')->nullable();
                $table->decimal('current_balance', 15, 2)->default(0.00);
                $table->boolean('is_system_account')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 2. Unified Parties Master (Customers, Vendors, Investors, Founders, Employees)
        if (!Schema::hasTable('parties')) {
            Schema::create('parties', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->string('party_type', 50)->default('customer'); // customer, vendor, investor, founder, employee
                $table->string('name', 255);
                $table->string('legal_name', 255)->nullable();
                $table->string('phone', 50)->nullable();
                $table->string('email', 255)->nullable();
                $table->string('gstin', 50)->nullable()->index();
                $table->string('pan', 50)->nullable()->index();
                $table->text('billing_address')->nullable();
                $table->text('shipping_address')->nullable();
                $table->string('state', 100)->nullable();
                $table->string('state_code', 10)->nullable();
                $table->boolean('is_customer')->default(false);
                $table->boolean('is_vendor')->default(false);
                $table->boolean('is_investor')->default(false);
                $table->boolean('is_founder')->default(false);
                $table->boolean('is_employee')->default(false);
                $table->decimal('opening_balance', 15, 2)->default(0.00);
                $table->decimal('current_balance', 15, 2)->default(0.00);
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 3. Tax Rates & Components Master (India GST & TDS Engine)
        if (!Schema::hasTable('tax_rates')) {
            Schema::create('tax_rates', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->string('name', 100);
                $table->enum('tax_type', ['gst', 'tds', 'tcs', 'other'])->default('gst');
                $table->decimal('rate', 5, 2)->default(0.00);
                $table->string('component', 50)->default('gst'); // cgst, sgst, igst, tds_194c, tds_194j, tds_194i, etc.
                $table->string('section', 50)->nullable(); // e.g. 194C, 194J
                $table->unsignedBigInteger('ledger_account_id')->nullable()->index();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 4. Bank Accounts Master
        if (!Schema::hasTable('bank_accounts')) {
            Schema::create('bank_accounts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->string('account_name', 255);
                $table->string('bank_name', 255);
                $table->string('account_number_masked', 100);
                $table->string('ifsc', 50)->nullable();
                $table->string('branch', 255)->nullable();
                $table->decimal('opening_balance', 15, 2)->default(0.00);
                $table->decimal('current_balance', 15, 2)->default(0.00);
                $table->unsignedBigInteger('ledger_account_id')->nullable()->index();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
        Schema::dropIfExists('tax_rates');
        Schema::dropIfExists('parties');
        Schema::dropIfExists('chart_of_accounts');
    }
};
