<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Expenses Header (Multi-tier approval workflow)
        if (!Schema::hasTable('expenses')) {
            Schema::create('expenses', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->string('expense_number', 50)->unique();
                $table->date('expense_date')->index();
                $table->string('title', 255);
                $table->unsignedBigInteger('category_account_id')->nullable()->index();
                $table->unsignedBigInteger('paid_through_account_id')->nullable()->index(); // Bank or Cash account
                $table->unsignedBigInteger('party_id')->nullable()->index(); // Payee/Employee/Vendor
                $table->decimal('subtotal', 15, 2)->default(0.00);
                $table->decimal('tax_amount', 15, 2)->default(0.00);
                $table->decimal('total_amount', 15, 2)->default(0.00);
                $table->string('payment_method', 50)->default('bank_transfer'); // bank_transfer, cash, upi, credit_card
                $table->string('payment_reference', 100)->nullable();
                $table->string('receipt_attachment', 255)->nullable();
                $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'posted', 'paid'])->default('draft')->index();
                $table->unsignedInteger('submitted_by')->nullable()->index();
                $table->timestamp('submitted_at')->nullable();
                $table->unsignedInteger('approved_by')->nullable()->index();
                $table->timestamp('approved_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->unsignedInteger('posted_by')->nullable()->index();
                $table->timestamp('posted_at')->nullable();
                $table->unsignedBigInteger('transaction_id')->nullable()->index();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 2. Expense Line Items
        if (!Schema::hasTable('expense_items')) {
            Schema::create('expense_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('expense_id')->index();
                $table->unsignedBigInteger('account_id')->index(); // Specific Expense Account in COA
                $table->text('description')->nullable();
                $table->decimal('amount', 15, 2)->default(0.00);
                $table->unsignedBigInteger('tax_rate_id')->nullable()->index();
                $table->decimal('tax_amount', 15, 2)->default(0.00);
                $table->decimal('total_amount', 15, 2)->default(0.00);
                $table->timestamps();
            });
        }

        // 3. Funding & Capital Records (Founder capital & 3rd-party investments)
        if (!Schema::hasTable('funding_records')) {
            Schema::create('funding_records', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->string('funding_number', 50)->unique();
                $table->unsignedBigInteger('party_id')->index(); // Founder / Investor party
                $table->enum('funding_type', [
                    'founder_capital',
                    'founder_current',
                    'equity_investment',
                    'preference_shares',
                    'convertible_note',
                    'unsecured_loan',
                    'secured_loan',
                    'grant'
                ])->default('founder_capital')->index();
                $table->decimal('amount', 15, 2)->default(0.00);
                $table->decimal('equity_percentage', 5, 2)->nullable();
                $table->decimal('valuation', 15, 2)->nullable();
                $table->decimal('shares_issued', 15, 2)->nullable();
                $table->decimal('share_price', 15, 2)->nullable();
                $table->unsignedBigInteger('deposit_bank_account_id')->index(); // Bank/Cash receiving account
                $table->unsignedBigInteger('equity_ledger_account_id')->index(); // COA Equity/Liability account
                $table->date('received_date')->index();
                $table->string('reference_number', 100)->nullable(); // UTR / Wire Ref
                $table->string('terms_document', 255)->nullable();
                $table->enum('status', ['draft', 'received', 'posted', 'cancelled'])->default('draft')->index();
                $table->text('notes')->nullable();
                $table->unsignedInteger('created_by')->nullable()->index();
                $table->unsignedInteger('posted_by')->nullable()->index();
                $table->timestamp('posted_at')->nullable();
                $table->unsignedBigInteger('transaction_id')->nullable()->index();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 4. Bank Transactions & Reconciliation
        if (!Schema::hasTable('bank_transactions')) {
            Schema::create('bank_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('bank_account_id')->index();
                $table->date('transaction_date')->index();
                $table->enum('type', ['deposit', 'withdrawal', 'transfer'])->index();
                $table->decimal('amount', 15, 2)->default(0.00);
                $table->string('reference_number', 100)->nullable();
                $table->string('payee_payer', 255)->nullable();
                $table->text('description')->nullable();
                $table->enum('reconciliation_status', ['unreconciled', 'reconciled', 'excluded'])->default('unreconciled')->index();
                $table->timestamp('reconciled_at')->nullable();
                $table->unsignedInteger('reconciled_by')->nullable()->index();
                $table->unsignedBigInteger('journal_entry_line_id')->nullable()->index();
                $table->unsignedBigInteger('transaction_id')->nullable()->index();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
        Schema::dropIfExists('funding_records');
        Schema::dropIfExists('expense_items');
        Schema::dropIfExists('expenses');
    }
};
