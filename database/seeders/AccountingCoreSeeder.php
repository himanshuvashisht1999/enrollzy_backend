<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AccountingCoreSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Permissions
        $permissions = [
            'Accounting Dashboard' => [
                'accounting-dashboard-view',
            ],
            'Chart of Accounts' => [
                'accounting-coa-browse',
                'accounting-coa-read',
                'accounting-coa-add',
                'accounting-coa-edit',
                'accounting-coa-delete',
            ],
            'Accounting Parties' => [
                'accounting-parties-browse',
                'accounting-parties-read',
                'accounting-parties-add',
                'accounting-parties-edit',
                'accounting-parties-delete',
            ],
            'Sales Invoices & Receivables' => [
                'accounting-invoices-browse',
                'accounting-invoices-read',
                'accounting-invoices-add',
                'accounting-invoices-edit',
                'accounting-invoices-delete',
                'accounting-invoices-post',
                'accounting-invoices-receive-payment',
            ],
            'Vendor Bills & Payables' => [
                'accounting-bills-browse',
                'accounting-bills-read',
                'accounting-bills-add',
                'accounting-bills-edit',
                'accounting-bills-delete',
                'accounting-bills-post',
                'accounting-bills-make-payment',
            ],
            'Expenses Management' => [
                'accounting-expenses-browse',
                'accounting-expenses-read',
                'accounting-expenses-add',
                'accounting-expenses-edit',
                'accounting-expenses-delete',
                'accounting-expenses-submit',
                'accounting-expenses-approve',
                'accounting-expenses-post',
            ],
            'Funding & Capital' => [
                'accounting-funding-browse',
                'accounting-funding-read',
                'accounting-funding-add',
                'accounting-funding-edit',
                'accounting-funding-delete',
                'accounting-funding-post',
            ],
            'Banking & Cash' => [
                'accounting-banking-browse',
                'accounting-banking-read',
                'accounting-banking-add',
                'accounting-banking-edit',
                'accounting-banking-reconcile',
                'accounting-banking-transfer',
            ],
            'Journal Vouchers' => [
                'accounting-journals-browse',
                'accounting-journals-read',
                'accounting-journals-add',
                'accounting-journals-edit',
                'accounting-journals-post',
                'accounting-journals-reverse',
            ],
            'Tax Management' => [
                'accounting-taxes-browse',
                'accounting-taxes-read',
                'accounting-taxes-add',
                'accounting-taxes-edit',
            ],
            'Financial Reports' => [
                'accounting-reports-general-ledger',
                'accounting-reports-trial-balance',
                'accounting-reports-pnl',
                'accounting-reports-balance-sheet',
                'accounting-reports-ageing',
                'accounting-reports-tax',
                'accounting-reports-export',
            ],
        ];

        foreach ($permissions as $moduleTitle => $perms) {
            foreach ($perms as $permName) {
                Permission::firstOrCreate(
                    ['name' => $permName, 'guard_name' => 'admin'],
                    ['module_title' => $moduleTitle]
                );
            }
        }

        // Sync to roles
        $superAdminRole = Role::where('name', 'superadmin')->where('guard_name', 'admin')->first();
        if ($superAdminRole) {
            $allPerms = Permission::where('guard_name', 'admin')->get();
            $superAdminRole->syncPermissions($allPerms);
        }

        $adminRole = Role::where('name', 'admin')->where('guard_name', 'admin')->first();
        if ($adminRole) {
            $allPerms = Permission::where('guard_name', 'admin')->get();
            $adminRole->syncPermissions($allPerms);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Seed Standard Chart of Accounts (COA)
        $coaAccounts = [
            // ASSETS (1000s)
            ['account_code' => '1000', 'name' => 'Assets', 'account_type' => 'asset', 'parent_id' => null, 'is_system_account' => true],
            ['account_code' => '1100', 'name' => 'Current Assets', 'account_type' => 'asset', 'parent_id' => '1000', 'is_system_account' => true],
            ['account_code' => '1110', 'name' => 'Cash on Hand', 'account_type' => 'asset', 'parent_id' => '1100', 'is_system_account' => true],
            ['account_code' => '1120', 'name' => 'Bank Accounts', 'account_type' => 'asset', 'parent_id' => '1100', 'is_system_account' => true],
            ['account_code' => '1121', 'name' => 'HDFC Current Bank Account', 'account_type' => 'asset', 'parent_id' => '1120', 'is_system_account' => false],
            ['account_code' => '1122', 'name' => 'SBI Bank Account', 'account_type' => 'asset', 'parent_id' => '1120', 'is_system_account' => false],
            ['account_code' => '1130', 'name' => 'Accounts Receivable (Debtors)', 'account_type' => 'asset', 'parent_id' => '1100', 'is_system_account' => true],
            ['account_code' => '1140', 'name' => 'Prepaid Expenses', 'account_type' => 'asset', 'parent_id' => '1100', 'is_system_account' => true],
            ['account_code' => '1150', 'name' => 'GST Input Tax Credit (ITC)', 'account_type' => 'asset', 'parent_id' => '1100', 'is_system_account' => true],
            ['account_code' => '1151', 'name' => 'Input CGST', 'account_type' => 'asset', 'parent_id' => '1150', 'is_system_account' => true],
            ['account_code' => '1152', 'name' => 'Input SGST', 'account_type' => 'asset', 'parent_id' => '1150', 'is_system_account' => true],
            ['account_code' => '1153', 'name' => 'Input IGST', 'account_type' => 'asset', 'parent_id' => '1150', 'is_system_account' => true],
            ['account_code' => '1160', 'name' => 'TDS Receivable', 'account_type' => 'asset', 'parent_id' => '1100', 'is_system_account' => true],
            
            ['account_code' => '1200', 'name' => 'Fixed Assets', 'account_type' => 'asset', 'parent_id' => '1000', 'is_system_account' => true],
            ['account_code' => '1210', 'name' => 'Computer & IT Equipment', 'account_type' => 'asset', 'parent_id' => '1200', 'is_system_account' => false],
            ['account_code' => '1220', 'name' => 'Office Furniture & Fixtures', 'account_type' => 'asset', 'parent_id' => '1200', 'is_system_account' => false],
            ['account_code' => '1290', 'name' => 'Accumulated Depreciation', 'account_type' => 'asset', 'parent_id' => '1200', 'is_system_account' => true],

            // LIABILITIES (2000s)
            ['account_code' => '2000', 'name' => 'Liabilities', 'account_type' => 'liability', 'parent_id' => null, 'is_system_account' => true],
            ['account_code' => '2100', 'name' => 'Current Liabilities', 'account_type' => 'liability', 'parent_id' => '2000', 'is_system_account' => true],
            ['account_code' => '2110', 'name' => 'Accounts Payable (Creditors)', 'account_type' => 'liability', 'parent_id' => '2100', 'is_system_account' => true],
            ['account_code' => '2120', 'name' => 'GST Output Tax Payable', 'account_type' => 'liability', 'parent_id' => '2100', 'is_system_account' => true],
            ['account_code' => '2121', 'name' => 'Output CGST', 'account_type' => 'liability', 'parent_id' => '2120', 'is_system_account' => true],
            ['account_code' => '2122', 'name' => 'Output SGST', 'account_type' => 'liability', 'parent_id' => '2120', 'is_system_account' => true],
            ['account_code' => '2123', 'name' => 'Output IGST', 'account_type' => 'liability', 'parent_id' => '2120', 'is_system_account' => true],
            ['account_code' => '2130', 'name' => 'TDS Payable', 'account_type' => 'liability', 'parent_id' => '2100', 'is_system_account' => true],
            ['account_code' => '2131', 'name' => 'TDS Payable - 194C (Contractors)', 'account_type' => 'liability', 'parent_id' => '2130', 'is_system_account' => true],
            ['account_code' => '2132', 'name' => 'TDS Payable - 194J (Professional Fees)', 'account_type' => 'liability', 'parent_id' => '2130', 'is_system_account' => true],
            ['account_code' => '2133', 'name' => 'TDS Payable - 194I (Rent)', 'account_type' => 'liability', 'parent_id' => '2130', 'is_system_account' => true],
            ['account_code' => '2140', 'name' => 'Salaries & Wages Payable', 'account_type' => 'liability', 'parent_id' => '2100', 'is_system_account' => true],
            ['account_code' => '2150', 'name' => 'Accrued Expenses', 'account_type' => 'liability', 'parent_id' => '2100', 'is_system_account' => false],
            
            ['account_code' => '2200', 'name' => 'Non-Current Liabilities', 'account_type' => 'liability', 'parent_id' => '2000', 'is_system_account' => true],
            ['account_code' => '2210', 'name' => 'Long Term Bank Loans', 'account_type' => 'liability', 'parent_id' => '2200', 'is_system_account' => false],
            ['account_code' => '2220', 'name' => 'Unsecured Loans & Convertible Notes', 'account_type' => 'liability', 'parent_id' => '2200', 'is_system_account' => false],

            // EQUITY (3000s)
            ['account_code' => '3000', 'name' => 'Equity & Capital', 'account_type' => 'equity', 'parent_id' => null, 'is_system_account' => true],
            ['account_code' => '3100', 'name' => 'Founder Capital Accounts', 'account_type' => 'equity', 'parent_id' => '3000', 'is_system_account' => true],
            ['account_code' => '3110', 'name' => 'Founder 1 Capital Account', 'account_type' => 'equity', 'parent_id' => '3100', 'is_system_account' => false],
            ['account_code' => '3120', 'name' => 'Founder 2 Capital Account', 'account_type' => 'equity', 'parent_id' => '3100', 'is_system_account' => false],
            ['account_code' => '3130', 'name' => 'Founder 3 Capital Account', 'account_type' => 'equity', 'parent_id' => '3100', 'is_system_account' => false],
            ['account_code' => '3140', 'name' => 'Founder Current / Drawings Account', 'account_type' => 'equity', 'parent_id' => '3100', 'is_system_account' => false],
            
            ['account_code' => '3200', 'name' => 'Investor Share Capital', 'account_type' => 'equity', 'parent_id' => '3000', 'is_system_account' => true],
            ['account_code' => '3210', 'name' => 'Equity Share Capital', 'account_type' => 'equity', 'parent_id' => '3200', 'is_system_account' => true],
            ['account_code' => '3220', 'name' => 'Preference Share Capital', 'account_type' => 'equity', 'parent_id' => '3200', 'is_system_account' => false],
            ['account_code' => '3230', 'name' => 'Securities Premium Reserve', 'account_type' => 'equity', 'parent_id' => '3200', 'is_system_account' => false],
            
            ['account_code' => '3300', 'name' => 'Retained Earnings', 'account_type' => 'equity', 'parent_id' => '3000', 'is_system_account' => true],
            ['account_code' => '3310', 'name' => 'Retained Earnings / Current Period Profit', 'account_type' => 'equity', 'parent_id' => '3300', 'is_system_account' => true],

            // REVENUE (4000s)
            ['account_code' => '4000', 'name' => 'Revenue / Income', 'account_type' => 'revenue', 'parent_id' => null, 'is_system_account' => true],
            ['account_code' => '4100', 'name' => 'Operating Revenue', 'account_type' => 'revenue', 'parent_id' => '4000', 'is_system_account' => true],
            ['account_code' => '4110', 'name' => 'University / Institution Commission', 'account_type' => 'revenue', 'parent_id' => '4100', 'is_system_account' => false],
            ['account_code' => '4120', 'name' => 'Student Processing & Application Fees', 'account_type' => 'revenue', 'parent_id' => '4100', 'is_system_account' => false],
            ['account_code' => '4130', 'name' => 'Advisory & Consultation Services', 'account_type' => 'revenue', 'parent_id' => '4100', 'is_system_account' => false],
            ['account_code' => '4140', 'name' => 'Marketing & Sponsorship Revenue', 'account_type' => 'revenue', 'parent_id' => '4100', 'is_system_account' => false],
            
            ['account_code' => '4200', 'name' => 'Other Income', 'account_type' => 'revenue', 'parent_id' => '4000', 'is_system_account' => true],
            ['account_code' => '4210', 'name' => 'Interest Income', 'account_type' => 'revenue', 'parent_id' => '4200', 'is_system_account' => false],
            ['account_code' => '4220', 'name' => 'Foreign Exchange Gain/Loss', 'account_type' => 'revenue', 'parent_id' => '4200', 'is_system_account' => false],
            ['account_code' => '4290', 'name' => 'Miscellaneous Income', 'account_type' => 'revenue', 'parent_id' => '4200', 'is_system_account' => false],

            // EXPENSES (5000s)
            ['account_code' => '5000', 'name' => 'Expenses', 'account_type' => 'expense', 'parent_id' => null, 'is_system_account' => true],
            ['account_code' => '5100', 'name' => 'Direct / Cost of Services', 'account_type' => 'expense', 'parent_id' => '5000', 'is_system_account' => true],
            ['account_code' => '5110', 'name' => 'Commission Paid to Sub-Agents', 'account_type' => 'expense', 'parent_id' => '5100', 'is_system_account' => false],
            ['account_code' => '5120', 'name' => 'University Application Fees Paid', 'account_type' => 'expense', 'parent_id' => '5100', 'is_system_account' => false],
            
            ['account_code' => '5200', 'name' => 'Employee & Staff Costs', 'account_type' => 'expense', 'parent_id' => '5000', 'is_system_account' => true],
            ['account_code' => '5210', 'name' => 'Staff Salaries & Wages', 'account_type' => 'expense', 'parent_id' => '5200', 'is_system_account' => false],
            ['account_code' => '5220', 'name' => 'Staff Incentives & Bonuses', 'account_type' => 'expense', 'parent_id' => '5200', 'is_system_account' => false],
            ['account_code' => '5230', 'name' => 'Staff Welfare & Training', 'account_type' => 'expense', 'parent_id' => '5200', 'is_system_account' => false],
            
            ['account_code' => '5300', 'name' => 'Administrative & Office Expenses', 'account_type' => 'expense', 'parent_id' => '5000', 'is_system_account' => true],
            ['account_code' => '5310', 'name' => 'Office Rent', 'account_type' => 'expense', 'parent_id' => '5300', 'is_system_account' => false],
            ['account_code' => '5320', 'name' => 'Electricity & Utilities', 'account_type' => 'expense', 'parent_id' => '5300', 'is_system_account' => false],
            ['account_code' => '5330', 'name' => 'Office Supplies, Printing & Stationery', 'account_type' => 'expense', 'parent_id' => '5300', 'is_system_account' => false],
            ['account_code' => '5340', 'name' => 'Internet & Telecommunication', 'account_type' => 'expense', 'parent_id' => '5300', 'is_system_account' => false],
            
            ['account_code' => '5400', 'name' => 'Technology & Cloud Infrastructure', 'account_type' => 'expense', 'parent_id' => '5000', 'is_system_account' => true],
            ['account_code' => '5410', 'name' => 'Server Hosting & AWS/GCP Cloud', 'account_type' => 'expense', 'parent_id' => '5400', 'is_system_account' => false],
            ['account_code' => '5420', 'name' => 'SaaS Software Subscriptions & Licenses', 'account_type' => 'expense', 'parent_id' => '5400', 'is_system_account' => false],
            ['account_code' => '5430', 'name' => 'Software Development & IT Support', 'account_type' => 'expense', 'parent_id' => '5400', 'is_system_account' => false],
            
            ['account_code' => '5500', 'name' => 'Marketing & Advertising', 'account_type' => 'expense', 'parent_id' => '5000', 'is_system_account' => true],
            ['account_code' => '5510', 'name' => 'Digital Ads (Google, Meta, LinkedIn)', 'account_type' => 'expense', 'parent_id' => '5500', 'is_system_account' => false],
            ['account_code' => '5520', 'name' => 'Education Fairs & Student Seminars', 'account_type' => 'expense', 'parent_id' => '5500', 'is_system_account' => false],
            ['account_code' => '5530', 'name' => 'Promotional Materials & Branding', 'account_type' => 'expense', 'parent_id' => '5500', 'is_system_account' => false],
            
            ['account_code' => '5600', 'name' => 'Legal & Professional Fees', 'account_type' => 'expense', 'parent_id' => '5000', 'is_system_account' => true],
            ['account_code' => '5610', 'name' => 'CA, Audit & Accounting Fees', 'account_type' => 'expense', 'parent_id' => '5600', 'is_system_account' => false],
            ['account_code' => '5620', 'name' => 'Legal & Compliance Charges', 'account_type' => 'expense', 'parent_id' => '5600', 'is_system_account' => false],
            
            ['account_code' => '5700', 'name' => 'Travel & Conveyance', 'account_type' => 'expense', 'parent_id' => '5000', 'is_system_account' => true],
            ['account_code' => '5710', 'name' => 'Business Travel & Hotel Accommodation', 'account_type' => 'expense', 'parent_id' => '5700', 'is_system_account' => false],
            ['account_code' => '5720', 'name' => 'Local Conveyance & Food Expenses', 'account_type' => 'expense', 'parent_id' => '5700', 'is_system_account' => false],
            
            ['account_code' => '5800', 'name' => 'Financial & Depreciation Charges', 'account_type' => 'expense', 'parent_id' => '5000', 'is_system_account' => true],
            ['account_code' => '5810', 'name' => 'Bank & Payment Gateway Processing Fees', 'account_type' => 'expense', 'parent_id' => '5800', 'is_system_account' => false],
            ['account_code' => '5820', 'name' => 'Depreciation & Amortization Expense', 'account_type' => 'expense', 'parent_id' => '5800', 'is_system_account' => false],

            // TAXES (6000s)
            ['account_code' => '6000', 'name' => 'Taxes & Statutory Expenses', 'account_type' => 'tax', 'parent_id' => null, 'is_system_account' => true],
            ['account_code' => '6100', 'name' => 'Direct Taxes', 'account_type' => 'tax', 'parent_id' => '6000', 'is_system_account' => true],
            ['account_code' => '6110', 'name' => 'Income Tax / Corporate Tax Expense', 'account_type' => 'tax', 'parent_id' => '6100', 'is_system_account' => false],
            ['account_code' => '6120', 'name' => 'Advance Tax Paid', 'account_type' => 'tax', 'parent_id' => '6100', 'is_system_account' => false],
        ];

        // Map code to ID
        $codeToId = [];
        foreach ($coaAccounts as $acc) {
            $parentId = null;
            if (!empty($acc['parent_id']) && isset($codeToId[$acc['parent_id']])) {
                $parentId = $codeToId[$acc['parent_id']];
            }

            $record = DB::table('chart_of_accounts')->where('account_code', $acc['account_code'])->first();
            if (!$record) {
                $id = DB::table('chart_of_accounts')->insertGetId([
                    'account_code' => $acc['account_code'],
                    'name' => $acc['name'],
                    'account_type' => $acc['account_type'],
                    'parent_id' => $parentId,
                    'is_system_account' => $acc['is_system_account'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $codeToId[$acc['account_code']] = $id;
            } else {
                $codeToId[$acc['account_code']] = $record->id;
            }
        }

        // 3. Seed Tax Rates (GST & TDS)
        $taxRates = [
            ['name' => 'GST 18% (9% CGST + 9% SGST / 18% IGST)', 'tax_type' => 'gst', 'rate' => 18.00, 'component' => 'gst', 'description' => 'Standard GST Rate for Educational & Advisory Services'],
            ['name' => 'GST 5%', 'tax_type' => 'gst', 'rate' => 5.00, 'component' => 'gst', 'description' => 'Reduced GST Rate 5%'],
            ['name' => 'GST 12%', 'tax_type' => 'gst', 'rate' => 12.00, 'component' => 'gst', 'description' => 'GST Rate 12%'],
            ['name' => 'GST 28%', 'tax_type' => 'gst', 'rate' => 28.00, 'component' => 'gst', 'description' => 'Luxury / Higher Bracket GST Rate 28%'],
            ['name' => 'GST 0% / Exempt', 'tax_type' => 'gst', 'rate' => 0.00, 'component' => 'gst', 'description' => 'Nil Rated or Exempt Services'],
            
            // TDS Components
            ['name' => 'TDS u/s 194C - Contractor (1% / 2%)', 'tax_type' => 'tds', 'rate' => 2.00, 'component' => 'tds_194c', 'section' => '194C', 'description' => 'TDS on Payments to Contractors & Sub-contractors'],
            ['name' => 'TDS u/s 194J - Professional & Technical Fees (10%)', 'tax_type' => 'tds', 'rate' => 10.00, 'component' => 'tds_194j', 'section' => '194J', 'description' => 'TDS on Fees for Professional or Technical Services'],
            ['name' => 'TDS u/s 194J - Tech Subscriptions / Call Center (2%)', 'tax_type' => 'tds', 'rate' => 2.00, 'component' => 'tds_194j', 'section' => '194J', 'description' => 'TDS on Technical Services / Software Royalty (2%)'],
            ['name' => 'TDS u/s 194I - Rent for Land & Building (10%)', 'tax_type' => 'tds', 'rate' => 10.00, 'component' => 'tds_194i', 'section' => '194I', 'description' => 'TDS on Rent for Office / Building premises'],
            ['name' => 'TDS u/s 194I - Rent for Plant & Machinery (2%)', 'tax_type' => 'tds', 'rate' => 2.00, 'component' => 'tds_194i', 'section' => '194I', 'description' => 'TDS on Rent for Equipment'],
        ];

        foreach ($taxRates as $tax) {
            $existing = DB::table('tax_rates')->where('name', $tax['name'])->first();
            if (!$existing) {
                DB::table('tax_rates')->insert([
                    'name' => $tax['name'],
                    'tax_type' => $tax['tax_type'],
                    'rate' => $tax['rate'],
                    'component' => $tax['component'],
                    'section' => $tax['section'] ?? null,
                    'description' => $tax['description'] ?? null,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 4. Seed Default Bank & Cash Accounts
        $bankLedgerId = $codeToId['1121'] ?? null;
        if ($bankLedgerId && !DB::table('bank_accounts')->where('account_name', 'HDFC Bank Primary Current A/c')->exists()) {
            DB::table('bank_accounts')->insert([
                'account_name' => 'HDFC Bank Primary Current A/c',
                'bank_name' => 'HDFC Bank Ltd',
                'account_number_masked' => 'XXXX-XXXX-4589',
                'ifsc' => 'HDFC0001234',
                'branch' => 'Connaught Place, New Delhi',
                'opening_balance' => 0.00,
                'current_balance' => 0.00,
                'ledger_account_id' => $bankLedgerId,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $cashLedgerId = $codeToId['1110'] ?? null;
        if ($cashLedgerId && !DB::table('bank_accounts')->where('account_name', 'Main Cash Register')->exists()) {
            DB::table('bank_accounts')->insert([
                'account_name' => 'Main Cash Register',
                'bank_name' => 'Petty Cash',
                'account_number_masked' => 'CASH-MAIN',
                'ifsc' => null,
                'branch' => 'Head Office',
                'opening_balance' => 0.00,
                'current_balance' => 0.00,
                'ledger_account_id' => $cashLedgerId,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 5. Seed Sample Unified Parties (Customer, Vendor, Founder, Investor)
        $sampleParties = [
            [
                'name' => 'Global Education University UK',
                'party_type' => 'customer',
                'is_customer' => true,
                'is_vendor' => false,
                'is_investor' => false,
                'is_founder' => false,
                'email' => 'accounts@globaledu.ac.uk',
                'phone' => '+44 20 7946 0991',
                'gstin' => '07AAAAA0000A1Z5',
                'pan' => 'AAAAA0000A',
                'state' => 'Delhi',
                'state_code' => '07',
                'billing_address' => 'Floor 4, International Education Plaza, New Delhi',
            ],
            [
                'name' => 'AWS Cloud Services & Infra',
                'party_type' => 'vendor',
                'is_customer' => false,
                'is_vendor' => true,
                'is_investor' => false,
                'is_founder' => false,
                'email' => 'billing@aws.amazon.in',
                'phone' => '+91 80 4000 5000',
                'gstin' => '27AAACA1234A1Z9',
                'pan' => 'AAACA1234A',
                'state' => 'Maharashtra',
                'state_code' => '27',
                'billing_address' => 'Amazon Web Services India Pvt Ltd, Mumbai, Maharashtra',
            ],
            [
                'name' => 'Himanshu Vashisht (Founder 1)',
                'party_type' => 'founder',
                'is_customer' => false,
                'is_vendor' => false,
                'is_investor' => false,
                'is_founder' => true,
                'email' => 'founder1@enrollzy.com',
                'phone' => '+91 98765 43210',
                'pan' => 'ABCDE1234F',
                'state' => 'Delhi',
                'state_code' => '07',
                'billing_address' => 'Enrollzy HQ, New Delhi',
            ],
            [
                'name' => 'Angel Ventures Fund India',
                'party_type' => 'investor',
                'is_customer' => false,
                'is_vendor' => false,
                'is_investor' => true,
                'is_founder' => false,
                'email' => 'investments@angelventures.in',
                'phone' => '+91 11 2345 6789',
                'gstin' => '07AABCA5678A1Z2',
                'pan' => 'AABCA5678A',
                'state' => 'Delhi',
                'state_code' => '07',
                'billing_address' => 'Barakhamba Road, New Delhi',
            ]
        ];

        foreach ($sampleParties as $party) {
            if (!DB::table('parties')->where('name', $party['name'])->exists()) {
                DB::table('parties')->insert(array_merge($party, [
                    'opening_balance' => 0.00,
                    'current_balance' => 0.00,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }
}
