<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Sales Invoices (Receivables Header)
        if (!Schema::hasTable('sales_invoices')) {
            Schema::create('sales_invoices', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->string('invoice_number', 50)->unique();
                $table->unsignedBigInteger('party_id')->index(); // Customer Party
                $table->date('invoice_date')->index();
                $table->date('due_date')->index();
                $table->decimal('subtotal', 15, 2)->default(0.00);
                $table->decimal('discount_amount', 15, 2)->default(0.00);
                $table->decimal('cgst_amount', 15, 2)->default(0.00);
                $table->decimal('sgst_amount', 15, 2)->default(0.00);
                $table->decimal('igst_amount', 15, 2)->default(0.00);
                $table->decimal('total_tax', 15, 2)->default(0.00);
                $table->decimal('tds_deducted', 15, 2)->default(0.00);
                $table->decimal('total_amount', 15, 2)->default(0.00);
                $table->decimal('paid_amount', 15, 2)->default(0.00);
                $table->decimal('balance_due', 15, 2)->default(0.00);
                $table->enum('payment_status', ['unpaid', 'partially_paid', 'paid', 'overdue'])->default('unpaid')->index();
                $table->enum('status', ['draft', 'sent', 'posted', 'cancelled'])->default('draft')->index();
                $table->string('place_of_supply', 100)->nullable();
                $table->text('notes')->nullable();
                $table->text('terms')->nullable();
                $table->unsignedInteger('created_by')->nullable()->index();
                $table->unsignedInteger('posted_by')->nullable()->index();
                $table->timestamp('posted_at')->nullable();
                $table->unsignedBigInteger('transaction_id')->nullable()->index();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 2. Sales Invoice Line Items
        if (!Schema::hasTable('sales_invoice_items')) {
            Schema::create('sales_invoice_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sales_invoice_id')->index();
                $table->string('item_name', 255);
                $table->text('description')->nullable();
                $table->string('hsn_sac', 50)->nullable();
                $table->decimal('quantity', 10, 2)->default(1.00);
                $table->decimal('unit_price', 15, 2)->default(0.00);
                $table->decimal('discount_amount', 15, 2)->default(0.00);
                $table->decimal('taxable_amount', 15, 2)->default(0.00);
                $table->unsignedBigInteger('tax_rate_id')->nullable()->index();
                $table->decimal('gst_rate', 5, 2)->default(0.00);
                $table->decimal('cgst_amount', 15, 2)->default(0.00);
                $table->decimal('sgst_amount', 15, 2)->default(0.00);
                $table->decimal('igst_amount', 15, 2)->default(0.00);
                $table->decimal('total_amount', 15, 2)->default(0.00);
                $table->unsignedBigInteger('revenue_account_id')->nullable()->index();
                $table->timestamps();
            });
        }

        // 3. Vendor Bills (Payables Header)
        if (!Schema::hasTable('vendor_bills')) {
            Schema::create('vendor_bills', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->string('bill_number', 50)->unique();
                $table->string('vendor_bill_ref', 100)->nullable();
                $table->unsignedBigInteger('party_id')->index(); // Vendor Party
                $table->date('bill_date')->index();
                $table->date('due_date')->index();
                $table->decimal('subtotal', 15, 2)->default(0.00);
                $table->decimal('cgst_amount', 15, 2)->default(0.00);
                $table->decimal('sgst_amount', 15, 2)->default(0.00);
                $table->decimal('igst_amount', 15, 2)->default(0.00);
                $table->decimal('total_tax', 15, 2)->default(0.00);
                $table->unsignedBigInteger('tds_rate_id')->nullable()->index();
                $table->decimal('tds_rate', 5, 2)->default(0.00);
                $table->decimal('tds_amount', 15, 2)->default(0.00);
                $table->decimal('total_amount', 15, 2)->default(0.00);
                $table->decimal('net_payable', 15, 2)->default(0.00);
                $table->decimal('paid_amount', 15, 2)->default(0.00);
                $table->decimal('balance_due', 15, 2)->default(0.00);
                $table->enum('payment_status', ['unpaid', 'partially_paid', 'paid', 'overdue'])->default('unpaid')->index();
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

        // 4. Vendor Bill Line Items
        if (!Schema::hasTable('vendor_bill_items')) {
            Schema::create('vendor_bill_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('vendor_bill_id')->index();
                $table->string('item_name', 255);
                $table->text('description')->nullable();
                $table->string('hsn_sac', 50)->nullable();
                $table->decimal('quantity', 10, 2)->default(1.00);
                $table->decimal('unit_price', 15, 2)->default(0.00);
                $table->decimal('taxable_amount', 15, 2)->default(0.00);
                $table->unsignedBigInteger('tax_rate_id')->nullable()->index();
                $table->decimal('gst_rate', 5, 2)->default(0.00);
                $table->decimal('cgst_amount', 15, 2)->default(0.00);
                $table->decimal('sgst_amount', 15, 2)->default(0.00);
                $table->decimal('igst_amount', 15, 2)->default(0.00);
                $table->decimal('total_amount', 15, 2)->default(0.00);
                $table->unsignedBigInteger('expense_account_id')->nullable()->index();
                $table->timestamps();
            });
        }

        // 5. Payment Allocations (Linking receipts/payments to invoices/bills)
        if (!Schema::hasTable('payment_allocations')) {
            Schema::create('payment_allocations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->unsignedBigInteger('transaction_id')->index(); // Payment or Receipt Accounting Transaction
                $table->string('allocatable_type', 100)->index(); // 'sales_invoice' or 'vendor_bill'
                $table->unsignedBigInteger('allocatable_id')->index();
                $table->decimal('allocated_amount', 15, 2)->default(0.00);
                $table->decimal('discount_allowed', 15, 2)->default(0.00);
                $table->decimal('tds_deducted', 15, 2)->default(0.00);
                $table->date('allocation_date')->index();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');
        Schema::dropIfExists('vendor_bill_items');
        Schema::dropIfExists('vendor_bills');
        Schema::dropIfExists('sales_invoice_items');
        Schema::dropIfExists('sales_invoices');
    }
};
