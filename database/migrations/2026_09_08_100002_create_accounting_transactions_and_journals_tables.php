<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Accounting Transactions Header (Business transaction router)
        if (!Schema::hasTable('accounting_transactions')) {
            Schema::create('accounting_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->string('transaction_number', 50)->unique();
                $table->date('transaction_date')->index();
                $table->string('transaction_type', 50)->index(); 
                // sales_invoice, purchase_bill, receipt, payment, expense, credit_note, debit_note, founder_investment, third_party_investment, bank_transfer, journal, tax_payment, tds_payment, gst_payment
                $table->string('reference_type', 100)->nullable();
                $table->unsignedBigInteger('reference_id')->nullable()->index();
                $table->decimal('total_amount', 15, 2)->default(0.00);
                $table->text('description')->nullable();
                $table->enum('status', ['draft', 'pending_approval', 'approved', 'posted', 'cancelled', 'reversed'])->default('draft')->index();
                $table->unsignedInteger('created_by')->nullable()->index();
                $table->unsignedInteger('approved_by')->nullable()->index();
                $table->timestamp('approved_at')->nullable();
                $table->unsignedInteger('posted_by')->nullable()->index();
                $table->timestamp('posted_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 2. Journal Entries Header (Logical double-entry voucher)
        if (!Schema::hasTable('journal_entries')) {
            Schema::create('journal_entries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('transaction_id')->nullable()->index();
                $table->string('journal_number', 50)->unique();
                $table->date('journal_date')->index();
                $table->string('entry_type', 50)->default('auto'); // auto, manual, adjustment, opening, closing, reversal
                $table->text('description')->nullable();
                $table->decimal('total_debit', 15, 2)->default(0.00);
                $table->decimal('total_credit', 15, 2)->default(0.00);
                $table->enum('status', ['draft', 'posted', 'reversed'])->default('posted')->index();
                $table->unsignedInteger('created_by')->nullable()->index();
                $table->unsignedInteger('posted_by')->nullable()->index();
                $table->timestamp('posted_at')->nullable();
                $table->timestamps();
            });
        }

        // 3. Journal Entry Lines (The double-entry lines: Sum(Debit) == Sum(Credit))
        if (!Schema::hasTable('journal_entry_lines')) {
            Schema::create('journal_entry_lines', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('journal_entry_id')->index();
                $table->unsignedBigInteger('account_id')->index();
                $table->unsignedBigInteger('party_id')->nullable()->index();
                $table->decimal('debit', 15, 2)->default(0.00);
                $table->decimal('credit', 15, 2)->default(0.00);
                $table->text('description')->nullable();
                $table->string('tax_code', 50)->nullable();
                $table->string('reference', 100)->nullable();
                $table->timestamps();
            });
        }

        // 4. Accounting Audit Logs (Preserves immutable audit trail)
        if (!Schema::hasTable('accounting_audit_logs')) {
            Schema::create('accounting_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedInteger('user_id')->nullable()->index();
                $table->string('entity_type', 100)->index();
                $table->unsignedBigInteger('entity_id')->index();
                $table->string('action', 50); // Created, Edited, Approved, Posted, Cancelled, Reversed, Allocated
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address', 50)->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_audit_logs');
        Schema::dropIfExists('journal_entry_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('accounting_transactions');
    }
};
