-- =========================================================================
-- ENROLLZY ACCOUNTING MODULE SCHEMA & SEED DATA
-- Generated: 2026-09-08 09:43:59
-- Database: enrollzy_backend
-- =========================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- Table structure for `chart_of_accounts`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `chart_of_accounts`;
CREATE TABLE `chart_of_accounts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  `account_code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `account_type` enum('asset','liability','equity','revenue','expense','tax') NOT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `description` text DEFAULT NULL,
  `current_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `is_system_account` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chart_of_accounts_account_code_unique` (`account_code`),
  KEY `chart_of_accounts_organization_id_index` (`organization_id`),
  KEY `chart_of_accounts_account_type_index` (`account_type`),
  KEY `chart_of_accounts_parent_id_index` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=90 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table structure for `parties`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `parties`;
CREATE TABLE `parties` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  `party_type` varchar(50) NOT NULL DEFAULT 'customer',
  `name` varchar(255) NOT NULL,
  `legal_name` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `gstin` varchar(50) DEFAULT NULL,
  `pan` varchar(50) DEFAULT NULL,
  `billing_address` text DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `state_code` varchar(10) DEFAULT NULL,
  `is_customer` tinyint(1) NOT NULL DEFAULT 0,
  `is_vendor` tinyint(1) NOT NULL DEFAULT 0,
  `is_investor` tinyint(1) NOT NULL DEFAULT 0,
  `is_founder` tinyint(1) NOT NULL DEFAULT 0,
  `is_employee` tinyint(1) NOT NULL DEFAULT 0,
  `opening_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `current_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parties_organization_id_index` (`organization_id`),
  KEY `parties_gstin_index` (`gstin`),
  KEY `parties_pan_index` (`pan`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table structure for `tax_rates`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `tax_rates`;
CREATE TABLE `tax_rates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `tax_type` enum('gst','tds','tcs','other') NOT NULL DEFAULT 'gst',
  `rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `component` varchar(50) NOT NULL DEFAULT 'gst',
  `section` varchar(50) DEFAULT NULL,
  `ledger_account_id` bigint(20) unsigned DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tax_rates_organization_id_index` (`organization_id`),
  KEY `tax_rates_ledger_account_id_index` (`ledger_account_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table structure for `bank_accounts`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `bank_accounts`;
CREATE TABLE `bank_accounts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  `account_name` varchar(255) NOT NULL,
  `bank_name` varchar(255) NOT NULL,
  `account_number_masked` varchar(100) NOT NULL,
  `ifsc` varchar(50) DEFAULT NULL,
  `branch` varchar(255) DEFAULT NULL,
  `opening_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `current_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `ledger_account_id` bigint(20) unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bank_accounts_organization_id_index` (`organization_id`),
  KEY `bank_accounts_ledger_account_id_index` (`ledger_account_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table structure for `accounting_transactions`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `accounting_transactions`;
CREATE TABLE `accounting_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  `transaction_number` varchar(50) NOT NULL,
  `transaction_date` date NOT NULL,
  `transaction_type` varchar(50) NOT NULL,
  `reference_type` varchar(100) DEFAULT NULL,
  `reference_id` bigint(20) unsigned DEFAULT NULL,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `status` enum('draft','pending_approval','approved','posted','cancelled','reversed') NOT NULL DEFAULT 'draft',
  `created_by` int(10) unsigned DEFAULT NULL,
  `approved_by` int(10) unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `posted_by` int(10) unsigned DEFAULT NULL,
  `posted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accounting_transactions_transaction_number_unique` (`transaction_number`),
  KEY `accounting_transactions_organization_id_index` (`organization_id`),
  KEY `accounting_transactions_transaction_date_index` (`transaction_date`),
  KEY `accounting_transactions_transaction_type_index` (`transaction_type`),
  KEY `accounting_transactions_reference_id_index` (`reference_id`),
  KEY `accounting_transactions_status_index` (`status`),
  KEY `accounting_transactions_created_by_index` (`created_by`),
  KEY `accounting_transactions_approved_by_index` (`approved_by`),
  KEY `accounting_transactions_posted_by_index` (`posted_by`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table structure for `journal_entries`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `journal_entries`;
CREATE TABLE `journal_entries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  `transaction_id` bigint(20) unsigned DEFAULT NULL,
  `journal_number` varchar(50) NOT NULL,
  `journal_date` date NOT NULL,
  `entry_type` varchar(50) NOT NULL DEFAULT 'auto',
  `description` text DEFAULT NULL,
  `total_debit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_credit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','posted','reversed') NOT NULL DEFAULT 'posted',
  `created_by` int(10) unsigned DEFAULT NULL,
  `posted_by` int(10) unsigned DEFAULT NULL,
  `posted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `journal_entries_journal_number_unique` (`journal_number`),
  KEY `journal_entries_organization_id_index` (`organization_id`),
  KEY `journal_entries_transaction_id_index` (`transaction_id`),
  KEY `journal_entries_journal_date_index` (`journal_date`),
  KEY `journal_entries_status_index` (`status`),
  KEY `journal_entries_created_by_index` (`created_by`),
  KEY `journal_entries_posted_by_index` (`posted_by`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table structure for `journal_entry_lines`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `journal_entry_lines`;
CREATE TABLE `journal_entry_lines` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `journal_entry_id` bigint(20) unsigned NOT NULL,
  `account_id` bigint(20) unsigned NOT NULL,
  `party_id` bigint(20) unsigned DEFAULT NULL,
  `debit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `credit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `tax_code` varchar(50) DEFAULT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `journal_entry_lines_journal_entry_id_index` (`journal_entry_id`),
  KEY `journal_entry_lines_account_id_index` (`account_id`),
  KEY `journal_entry_lines_party_id_index` (`party_id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table structure for `accounting_audit_logs`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `accounting_audit_logs`;
CREATE TABLE `accounting_audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `entity_type` varchar(100) NOT NULL,
  `entity_id` bigint(20) unsigned NOT NULL,
  `action` varchar(50) NOT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `accounting_audit_logs_organization_id_index` (`organization_id`),
  KEY `accounting_audit_logs_user_id_index` (`user_id`),
  KEY `accounting_audit_logs_entity_type_index` (`entity_type`),
  KEY `accounting_audit_logs_entity_id_index` (`entity_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table structure for `sales_invoices`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `sales_invoices`;
CREATE TABLE `sales_invoices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `party_id` bigint(20) unsigned NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date NOT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `cgst_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `sgst_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `igst_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_tax` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tds_deducted` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `balance_due` decimal(15,2) NOT NULL DEFAULT 0.00,
  `payment_status` enum('unpaid','partially_paid','paid','overdue') NOT NULL DEFAULT 'unpaid',
  `status` enum('draft','sent','posted','cancelled') NOT NULL DEFAULT 'draft',
  `place_of_supply` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `terms` text DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `posted_by` int(10) unsigned DEFAULT NULL,
  `posted_at` timestamp NULL DEFAULT NULL,
  `transaction_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sales_invoices_invoice_number_unique` (`invoice_number`),
  KEY `sales_invoices_organization_id_index` (`organization_id`),
  KEY `sales_invoices_party_id_index` (`party_id`),
  KEY `sales_invoices_invoice_date_index` (`invoice_date`),
  KEY `sales_invoices_due_date_index` (`due_date`),
  KEY `sales_invoices_payment_status_index` (`payment_status`),
  KEY `sales_invoices_status_index` (`status`),
  KEY `sales_invoices_created_by_index` (`created_by`),
  KEY `sales_invoices_posted_by_index` (`posted_by`),
  KEY `sales_invoices_transaction_id_index` (`transaction_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table structure for `sales_invoice_items`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `sales_invoice_items`;
CREATE TABLE `sales_invoice_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sales_invoice_id` bigint(20) unsigned NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `hsn_sac` varchar(50) DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `unit_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `taxable_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax_rate_id` bigint(20) unsigned DEFAULT NULL,
  `gst_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `cgst_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `sgst_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `igst_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `revenue_account_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sales_invoice_items_sales_invoice_id_index` (`sales_invoice_id`),
  KEY `sales_invoice_items_tax_rate_id_index` (`tax_rate_id`),
  KEY `sales_invoice_items_revenue_account_id_index` (`revenue_account_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table structure for `vendor_bills`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `vendor_bills`;
CREATE TABLE `vendor_bills` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  `bill_number` varchar(50) NOT NULL,
  `vendor_bill_ref` varchar(100) DEFAULT NULL,
  `party_id` bigint(20) unsigned NOT NULL,
  `bill_date` date NOT NULL,
  `due_date` date NOT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `cgst_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `sgst_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `igst_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_tax` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tds_rate_id` bigint(20) unsigned DEFAULT NULL,
  `tds_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `tds_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `net_payable` decimal(15,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `balance_due` decimal(15,2) NOT NULL DEFAULT 0.00,
  `payment_status` enum('unpaid','partially_paid','paid','overdue') NOT NULL DEFAULT 'unpaid',
  `status` enum('draft','received','posted','cancelled') NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `posted_by` int(10) unsigned DEFAULT NULL,
  `posted_at` timestamp NULL DEFAULT NULL,
  `transaction_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vendor_bills_bill_number_unique` (`bill_number`),
  KEY `vendor_bills_organization_id_index` (`organization_id`),
  KEY `vendor_bills_party_id_index` (`party_id`),
  KEY `vendor_bills_bill_date_index` (`bill_date`),
  KEY `vendor_bills_due_date_index` (`due_date`),
  KEY `vendor_bills_tds_rate_id_index` (`tds_rate_id`),
  KEY `vendor_bills_payment_status_index` (`payment_status`),
  KEY `vendor_bills_status_index` (`status`),
  KEY `vendor_bills_created_by_index` (`created_by`),
  KEY `vendor_bills_posted_by_index` (`posted_by`),
  KEY `vendor_bills_transaction_id_index` (`transaction_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table structure for `vendor_bill_items`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `vendor_bill_items`;
CREATE TABLE `vendor_bill_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_bill_id` bigint(20) unsigned NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `hsn_sac` varchar(50) DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `unit_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `taxable_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax_rate_id` bigint(20) unsigned DEFAULT NULL,
  `gst_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `cgst_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `sgst_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `igst_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `expense_account_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vendor_bill_items_vendor_bill_id_index` (`vendor_bill_id`),
  KEY `vendor_bill_items_tax_rate_id_index` (`tax_rate_id`),
  KEY `vendor_bill_items_expense_account_id_index` (`expense_account_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table structure for `payment_allocations`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `payment_allocations`;
CREATE TABLE `payment_allocations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  `transaction_id` bigint(20) unsigned NOT NULL,
  `allocatable_type` varchar(100) NOT NULL,
  `allocatable_id` bigint(20) unsigned NOT NULL,
  `allocated_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount_allowed` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tds_deducted` decimal(15,2) NOT NULL DEFAULT 0.00,
  `allocation_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_allocations_organization_id_index` (`organization_id`),
  KEY `payment_allocations_transaction_id_index` (`transaction_id`),
  KEY `payment_allocations_allocatable_type_index` (`allocatable_type`),
  KEY `payment_allocations_allocatable_id_index` (`allocatable_id`),
  KEY `payment_allocations_allocation_date_index` (`allocation_date`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table structure for `expenses`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `expenses`;
CREATE TABLE `expenses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  `expense_number` varchar(50) NOT NULL,
  `expense_date` date NOT NULL,
  `title` varchar(255) NOT NULL,
  `category_account_id` bigint(20) unsigned DEFAULT NULL,
  `paid_through_account_id` bigint(20) unsigned DEFAULT NULL,
  `party_id` bigint(20) unsigned DEFAULT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(50) NOT NULL DEFAULT 'bank_transfer',
  `payment_reference` varchar(100) DEFAULT NULL,
  `receipt_attachment` varchar(255) DEFAULT NULL,
  `status` enum('draft','submitted','approved','rejected','posted','paid') NOT NULL DEFAULT 'draft',
  `submitted_by` int(10) unsigned DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `approved_by` int(10) unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `posted_by` int(10) unsigned DEFAULT NULL,
  `posted_at` timestamp NULL DEFAULT NULL,
  `transaction_id` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `expenses_expense_number_unique` (`expense_number`),
  KEY `expenses_organization_id_index` (`organization_id`),
  KEY `expenses_expense_date_index` (`expense_date`),
  KEY `expenses_category_account_id_index` (`category_account_id`),
  KEY `expenses_paid_through_account_id_index` (`paid_through_account_id`),
  KEY `expenses_party_id_index` (`party_id`),
  KEY `expenses_status_index` (`status`),
  KEY `expenses_submitted_by_index` (`submitted_by`),
  KEY `expenses_approved_by_index` (`approved_by`),
  KEY `expenses_posted_by_index` (`posted_by`),
  KEY `expenses_transaction_id_index` (`transaction_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table structure for `expense_items`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `expense_items`;
CREATE TABLE `expense_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `expense_id` bigint(20) unsigned NOT NULL,
  `account_id` bigint(20) unsigned NOT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax_rate_id` bigint(20) unsigned DEFAULT NULL,
  `tax_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expense_items_expense_id_index` (`expense_id`),
  KEY `expense_items_account_id_index` (`account_id`),
  KEY `expense_items_tax_rate_id_index` (`tax_rate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table structure for `funding_records`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `funding_records`;
CREATE TABLE `funding_records` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  `funding_number` varchar(50) NOT NULL,
  `party_id` bigint(20) unsigned NOT NULL,
  `funding_type` enum('founder_capital','founder_current','equity_investment','preference_shares','convertible_note','unsecured_loan','secured_loan','grant') NOT NULL DEFAULT 'founder_capital',
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `equity_percentage` decimal(5,2) DEFAULT NULL,
  `valuation` decimal(15,2) DEFAULT NULL,
  `shares_issued` decimal(15,2) DEFAULT NULL,
  `share_price` decimal(15,2) DEFAULT NULL,
  `deposit_bank_account_id` bigint(20) unsigned NOT NULL,
  `equity_ledger_account_id` bigint(20) unsigned NOT NULL,
  `received_date` date NOT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `terms_document` varchar(255) DEFAULT NULL,
  `status` enum('draft','received','posted','cancelled') NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `posted_by` int(10) unsigned DEFAULT NULL,
  `posted_at` timestamp NULL DEFAULT NULL,
  `transaction_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `funding_records_funding_number_unique` (`funding_number`),
  KEY `funding_records_organization_id_index` (`organization_id`),
  KEY `funding_records_party_id_index` (`party_id`),
  KEY `funding_records_funding_type_index` (`funding_type`),
  KEY `funding_records_deposit_bank_account_id_index` (`deposit_bank_account_id`),
  KEY `funding_records_equity_ledger_account_id_index` (`equity_ledger_account_id`),
  KEY `funding_records_received_date_index` (`received_date`),
  KEY `funding_records_status_index` (`status`),
  KEY `funding_records_created_by_index` (`created_by`),
  KEY `funding_records_posted_by_index` (`posted_by`),
  KEY `funding_records_transaction_id_index` (`transaction_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table structure for `bank_transactions`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `bank_transactions`;
CREATE TABLE `bank_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint(20) unsigned DEFAULT NULL,
  `bank_account_id` bigint(20) unsigned NOT NULL,
  `transaction_date` date NOT NULL,
  `type` enum('deposit','withdrawal','transfer') NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `reference_number` varchar(100) DEFAULT NULL,
  `payee_payer` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `reconciliation_status` enum('unreconciled','reconciled','excluded') NOT NULL DEFAULT 'unreconciled',
  `reconciled_at` timestamp NULL DEFAULT NULL,
  `reconciled_by` int(10) unsigned DEFAULT NULL,
  `journal_entry_line_id` bigint(20) unsigned DEFAULT NULL,
  `transaction_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bank_transactions_organization_id_index` (`organization_id`),
  KEY `bank_transactions_bank_account_id_index` (`bank_account_id`),
  KEY `bank_transactions_transaction_date_index` (`transaction_date`),
  KEY `bank_transactions_type_index` (`type`),
  KEY `bank_transactions_reconciliation_status_index` (`reconciliation_status`),
  KEY `bank_transactions_reconciled_by_index` (`reconciled_by`),
  KEY `bank_transactions_journal_entry_line_id_index` (`journal_entry_line_id`),
  KEY `bank_transactions_transaction_id_index` (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Default Seeds for `chart_of_accounts`
-- -----------------------------------------------------
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1', NULL, '1000', 'Assets', 'asset', NULL, NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('2', NULL, '1100', 'Current Assets', 'asset', '1', NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('3', NULL, '1110', 'Cash on Hand', 'asset', '2', NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('4', NULL, '1120', 'Bank Accounts', 'asset', '2', NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('5', NULL, '1121', 'HDFC Current Bank Account', 'asset', '4', NULL, '1103000.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:43:09', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('6', NULL, '1122', 'SBI Bank Account', 'asset', '4', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('7', NULL, '1130', 'Accounts Receivable (Debtors)', 'asset', '2', NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:43:09', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('8', NULL, '1140', 'Prepaid Expenses', 'asset', '2', NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('9', NULL, '1150', 'GST Input Tax Credit (ITC)', 'asset', '2', NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('10', NULL, '1151', 'Input CGST', 'asset', '9', NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('11', NULL, '1152', 'Input SGST', 'asset', '9', NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('12', NULL, '1153', 'Input IGST', 'asset', '9', NULL, '18000.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:43:09', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('13', NULL, '1160', 'TDS Receivable', 'asset', '2', NULL, '20000.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:43:09', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('14', NULL, '1200', 'Fixed Assets', 'asset', '1', NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('15', NULL, '1210', 'Computer & IT Equipment', 'asset', '14', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('16', NULL, '1220', 'Office Furniture & Fixtures', 'asset', '14', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('17', NULL, '1290', 'Accumulated Depreciation', 'asset', '14', NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('18', NULL, '2000', 'Liabilities', 'liability', NULL, NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('19', NULL, '2100', 'Current Liabilities', 'liability', '18', NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('20', NULL, '2110', 'Accounts Payable (Creditors)', 'liability', '19', NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:43:09', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('21', NULL, '2120', 'GST Output Tax Payable', 'liability', '19', NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('22', NULL, '2121', 'Output CGST', 'liability', '21', NULL, '18000.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:43:09', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('23', NULL, '2122', 'Output SGST', 'liability', '21', NULL, '18000.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:43:09', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('24', NULL, '2123', 'Output IGST', 'liability', '21', NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('25', NULL, '2130', 'TDS Payable', 'liability', '19', NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('26', NULL, '2131', 'TDS Payable - 194C (Contractors)', 'liability', '25', NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('27', NULL, '2132', 'TDS Payable - 194J (Professional Fees)', 'liability', '25', NULL, '10000.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:43:09', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('28', NULL, '2133', 'TDS Payable - 194I (Rent)', 'liability', '25', NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('29', NULL, '2140', 'Salaries & Wages Payable', 'liability', '19', NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('30', NULL, '2150', 'Accrued Expenses', 'liability', '19', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('31', NULL, '2200', 'Non-Current Liabilities', 'liability', '18', NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('32', NULL, '2210', 'Long Term Bank Loans', 'liability', '31', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('33', NULL, '2220', 'Unsecured Loans & Convertible Notes', 'liability', '31', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('34', NULL, '3000', 'Equity & Capital', 'equity', NULL, NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('35', NULL, '3100', 'Founder Capital Accounts', 'equity', '34', NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('36', NULL, '3110', 'Founder 1 Capital Account', 'equity', '35', NULL, '1000000.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:43:09', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('37', NULL, '3120', 'Founder 2 Capital Account', 'equity', '35', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('38', NULL, '3130', 'Founder 3 Capital Account', 'equity', '35', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('39', NULL, '3140', 'Founder Current / Drawings Account', 'equity', '35', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('40', NULL, '3200', 'Investor Share Capital', 'equity', '34', NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('41', NULL, '3210', 'Equity Share Capital', 'equity', '40', NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('42', NULL, '3220', 'Preference Share Capital', 'equity', '40', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('43', NULL, '3230', 'Securities Premium Reserve', 'equity', '40', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('44', NULL, '3300', 'Retained Earnings', 'equity', '34', NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('45', NULL, '3310', 'Retained Earnings / Current Period Profit', 'equity', '44', NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('46', NULL, '4000', 'Revenue / Income', 'revenue', NULL, NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('47', NULL, '4100', 'Operating Revenue', 'revenue', '46', NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('48', NULL, '4110', 'University / Institution Commission', 'revenue', '47', NULL, '200000.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:43:09', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('49', NULL, '4120', 'Student Processing & Application Fees', 'revenue', '47', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('50', NULL, '4130', 'Advisory & Consultation Services', 'revenue', '47', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('51', NULL, '4140', 'Marketing & Sponsorship Revenue', 'revenue', '47', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('52', NULL, '4200', 'Other Income', 'revenue', '46', NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('53', NULL, '4210', 'Interest Income', 'revenue', '52', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('54', NULL, '4220', 'Foreign Exchange Gain/Loss', 'revenue', '52', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('55', NULL, '4290', 'Miscellaneous Income', 'revenue', '52', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('56', NULL, '5000', 'Expenses', 'expense', NULL, NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('57', NULL, '5100', 'Direct / Cost of Services', 'expense', '56', NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('58', NULL, '5110', 'Commission Paid to Sub-Agents', 'expense', '57', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('59', NULL, '5120', 'University Application Fees Paid', 'expense', '57', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('60', NULL, '5200', 'Employee & Staff Costs', 'expense', '56', NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('61', NULL, '5210', 'Staff Salaries & Wages', 'expense', '60', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('62', NULL, '5220', 'Staff Incentives & Bonuses', 'expense', '60', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('63', NULL, '5230', 'Staff Welfare & Training', 'expense', '60', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('64', NULL, '5300', 'Administrative & Office Expenses', 'expense', '56', NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('65', NULL, '5310', 'Office Rent', 'expense', '64', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('66', NULL, '5320', 'Electricity & Utilities', 'expense', '64', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('67', NULL, '5330', 'Office Supplies, Printing & Stationery', 'expense', '64', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('68', NULL, '5340', 'Internet & Telecommunication', 'expense', '64', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('69', NULL, '5400', 'Technology & Cloud Infrastructure', 'expense', '56', NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('70', NULL, '5410', 'Server Hosting & AWS/GCP Cloud', 'expense', '69', NULL, '100000.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:43:09', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('71', NULL, '5420', 'SaaS Software Subscriptions & Licenses', 'expense', '69', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('72', NULL, '5430', 'Software Development & IT Support', 'expense', '69', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('73', NULL, '5500', 'Marketing & Advertising', 'expense', '56', NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('74', NULL, '5510', 'Digital Ads (Google, Meta, LinkedIn)', 'expense', '73', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('75', NULL, '5520', 'Education Fairs & Student Seminars', 'expense', '73', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('76', NULL, '5530', 'Promotional Materials & Branding', 'expense', '73', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('77', NULL, '5600', 'Legal & Professional Fees', 'expense', '56', NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('78', NULL, '5610', 'CA, Audit & Accounting Fees', 'expense', '77', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('79', NULL, '5620', 'Legal & Compliance Charges', 'expense', '77', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('80', NULL, '5700', 'Travel & Conveyance', 'expense', '56', NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('81', NULL, '5710', 'Business Travel & Hotel Accommodation', 'expense', '80', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('82', NULL, '5720', 'Local Conveyance & Food Expenses', 'expense', '80', NULL, '5000.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:43:09', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('83', NULL, '5800', 'Financial & Depreciation Charges', 'expense', '56', NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('84', NULL, '5810', 'Bank & Payment Gateway Processing Fees', 'expense', '83', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('85', NULL, '5820', 'Depreciation & Amortization Expense', 'expense', '83', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('86', NULL, '6000', 'Taxes & Statutory Expenses', 'tax', NULL, NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('87', NULL, '6100', 'Direct Taxes', 'tax', '86', NULL, '0.00', '1', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('88', NULL, '6110', 'Income Tax / Corporate Tax Expense', 'tax', '87', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);
INSERT INTO `chart_of_accounts` (`id`, `organization_id`, `account_code`, `name`, `account_type`, `parent_id`, `description`, `current_balance`, `is_system_account`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('89', NULL, '6120', 'Advance Tax Paid', 'tax', '87', NULL, '0.00', '0', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);

-- -----------------------------------------------------
-- Default Seeds for `tax_rates`
-- -----------------------------------------------------
INSERT INTO `tax_rates` (`id`, `organization_id`, `name`, `tax_type`, `rate`, `component`, `section`, `ledger_account_id`, `description`, `is_active`, `created_at`, `updated_at`) VALUES ('1', NULL, 'GST 18% (9% CGST + 9% SGST / 18% IGST)', 'gst', '18.00', 'gst', NULL, NULL, 'Standard GST Rate for Educational & Advisory Services', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22');
INSERT INTO `tax_rates` (`id`, `organization_id`, `name`, `tax_type`, `rate`, `component`, `section`, `ledger_account_id`, `description`, `is_active`, `created_at`, `updated_at`) VALUES ('2', NULL, 'GST 5%', 'gst', '5.00', 'gst', NULL, NULL, 'Reduced GST Rate 5%', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22');
INSERT INTO `tax_rates` (`id`, `organization_id`, `name`, `tax_type`, `rate`, `component`, `section`, `ledger_account_id`, `description`, `is_active`, `created_at`, `updated_at`) VALUES ('3', NULL, 'GST 12%', 'gst', '12.00', 'gst', NULL, NULL, 'GST Rate 12%', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22');
INSERT INTO `tax_rates` (`id`, `organization_id`, `name`, `tax_type`, `rate`, `component`, `section`, `ledger_account_id`, `description`, `is_active`, `created_at`, `updated_at`) VALUES ('4', NULL, 'GST 28%', 'gst', '28.00', 'gst', NULL, NULL, 'Luxury / Higher Bracket GST Rate 28%', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22');
INSERT INTO `tax_rates` (`id`, `organization_id`, `name`, `tax_type`, `rate`, `component`, `section`, `ledger_account_id`, `description`, `is_active`, `created_at`, `updated_at`) VALUES ('5', NULL, 'GST 0% / Exempt', 'gst', '0.00', 'gst', NULL, NULL, 'Nil Rated or Exempt Services', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22');
INSERT INTO `tax_rates` (`id`, `organization_id`, `name`, `tax_type`, `rate`, `component`, `section`, `ledger_account_id`, `description`, `is_active`, `created_at`, `updated_at`) VALUES ('6', NULL, 'TDS u/s 194C - Contractor (1% / 2%)', 'tds', '2.00', 'tds_194c', '194C', NULL, 'TDS on Payments to Contractors & Sub-contractors', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22');
INSERT INTO `tax_rates` (`id`, `organization_id`, `name`, `tax_type`, `rate`, `component`, `section`, `ledger_account_id`, `description`, `is_active`, `created_at`, `updated_at`) VALUES ('7', NULL, 'TDS u/s 194J - Professional & Technical Fees (10%)', 'tds', '10.00', 'tds_194j', '194J', NULL, 'TDS on Fees for Professional or Technical Services', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22');
INSERT INTO `tax_rates` (`id`, `organization_id`, `name`, `tax_type`, `rate`, `component`, `section`, `ledger_account_id`, `description`, `is_active`, `created_at`, `updated_at`) VALUES ('8', NULL, 'TDS u/s 194J - Tech Subscriptions / Call Center (2%)', 'tds', '2.00', 'tds_194j', '194J', NULL, 'TDS on Technical Services / Software Royalty (2%)', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22');
INSERT INTO `tax_rates` (`id`, `organization_id`, `name`, `tax_type`, `rate`, `component`, `section`, `ledger_account_id`, `description`, `is_active`, `created_at`, `updated_at`) VALUES ('9', NULL, 'TDS u/s 194I - Rent for Land & Building (10%)', 'tds', '10.00', 'tds_194i', '194I', NULL, 'TDS on Rent for Office / Building premises', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22');
INSERT INTO `tax_rates` (`id`, `organization_id`, `name`, `tax_type`, `rate`, `component`, `section`, `ledger_account_id`, `description`, `is_active`, `created_at`, `updated_at`) VALUES ('10', NULL, 'TDS u/s 194I - Rent for Plant & Machinery (2%)', 'tds', '2.00', 'tds_194i', '194I', NULL, 'TDS on Rent for Equipment', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22');

-- -----------------------------------------------------
-- Default Seeds for `bank_accounts`
-- -----------------------------------------------------
INSERT INTO `bank_accounts` (`id`, `organization_id`, `account_name`, `bank_name`, `account_number_masked`, `ifsc`, `branch`, `opening_balance`, `current_balance`, `ledger_account_id`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1', NULL, 'HDFC Bank Primary Current A/c', 'HDFC Bank Ltd', 'XXXX-XXXX-4589', 'HDFC0001234', 'Connaught Place, New Delhi', '0.00', '1103000.00', '5', '1', '2026-09-08 09:30:22', '2026-09-08 09:43:09', NULL);
INSERT INTO `bank_accounts` (`id`, `organization_id`, `account_name`, `bank_name`, `account_number_masked`, `ifsc`, `branch`, `opening_balance`, `current_balance`, `ledger_account_id`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES ('2', NULL, 'Main Cash Register', 'Petty Cash', 'CASH-MAIN', NULL, 'Head Office', '0.00', '0.00', '3', '1', '2026-09-08 09:30:22', '2026-09-08 09:30:22', NULL);

SET FOREIGN_KEY_CHECKS = 1;
