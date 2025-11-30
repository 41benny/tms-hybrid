<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetTransactionalDataSeeder extends Seeder
{
    /**
     * Reset all transactional data while preserving master data and users.
     * 
     * PRESERVED DATA:
     * - users
     * - customers, vendors, drivers, trucks, routes, equipment, sales
     * - chart_of_accounts
     * - cash_bank_accounts
     * - fiscal_periods
     * - parts (master data)
     * - fixed_assets (master data)
     * - menus
     * 
     * DELETED DATA:
     * - All operational/transactional records
     * - All financial transactions
     * - All inventory movements
     */
    public function run(): void
    {
        $this->command->warn('⚠️  WARNING: This will DELETE ALL TRANSACTIONAL DATA!');
        $this->command->warn('Master data and users will be preserved.');
        
        if (!$this->command->confirm('Have you backed up your database?', false)) {
            $this->command->error('❌ Backup required! Please backup your database first.');
            return;
        }

        if (!$this->command->confirm('Are you absolutely sure you want to continue?', false)) {
            $this->command->info('Operation cancelled.');
            return;
        }

        $this->command->info('🔄 Starting transactional data reset...');
        
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        try {
            $deletedTables = [];
            
            // 1. Accounting & Journals (delete first due to dependencies)
            $this->truncateTable('journal_lines', $deletedTables);
            $this->truncateTable('journals', $deletedTables);
            $this->truncateTable('opening_balances', $deletedTables);
            
            // 2. Finance - Invoice related
            $this->truncateTable('invoice_transaction_payments', $deletedTables);
            $this->truncateTable('invoice_items', $deletedTables);
            $this->truncateTable('invoices', $deletedTables);
            $this->truncateTable('payment_receipts', $deletedTables);
            
            // 3. Finance - Vendor Bill related
            $this->truncateTable('vendor_bill_payments', $deletedTables);
            $this->truncateTable('vendor_bill_items', $deletedTables);
            $this->truncateTable('vendor_bills', $deletedTables);
            
            // 4. Finance - Cash Bank
            $this->truncateTable('cash_bank_transactions', $deletedTables);
            
            // 5. Tax
            $this->truncateTable('tax_invoice_requests', $deletedTables);
            
            // 6. Notifications (clear all notifications related to deleted data)
            $this->truncateTable('notifications', $deletedTables);
            
            // 7. Operations - Driver Advances & Payment Requests
            $this->truncateTable('driver_advance_payments', $deletedTables);
            $this->truncateTable('driver_advances', $deletedTables);
            $this->truncateTable('payment_requests', $deletedTables);
            
            // 8. Operations - Shipment Legs & Costs
            $this->truncateTable('leg_additional_costs', $deletedTables);
            $this->truncateTable('leg_main_costs', $deletedTables);
            $this->truncateTable('shipment_legs', $deletedTables);
            
            // 9. Operations - Job Orders
            $this->truncateTable('job_order_items', $deletedTables);
            $this->truncateTable('job_orders', $deletedTables);
            
            // 10. Operations - Transports
            $this->truncateTable('transport_costs', $deletedTables);
            $this->truncateTable('transports', $deletedTables);
            
            // 11. Inventory - Part Movements
            $this->truncateTable('part_usage_items', $deletedTables);
            $this->truncateTable('part_usages', $deletedTables);
            $this->truncateTable('part_purchase_items', $deletedTables);
            $this->truncateTable('part_purchases', $deletedTables);
            $this->truncateTable('part_stocks', $deletedTables);
            
            // 12. Fixed Assets - Transactions only
            $this->truncateTable('asset_depreciations', $deletedTables);
            $this->truncateTable('asset_disposals', $deletedTables);
            
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            $this->command->newLine();
            $this->command->info('✅ Transactional data reset completed successfully!');
            $this->command->newLine();
            $this->command->info('📊 Summary:');
            $this->command->info('   Tables cleared: ' . count($deletedTables));
            $this->command->newLine();
            $this->command->table(['Cleared Tables'], array_map(fn($t) => [$t], $deletedTables));
            $this->command->newLine();
            $this->command->info('✓ Master data preserved: customers, vendors, drivers, trucks, routes, equipment, sales, parts, fixed_assets');
            $this->command->info('✓ System data preserved: users, chart_of_accounts, cash_bank_accounts, fiscal_periods, menus');
            
        } catch (\Exception $e) {
            // Re-enable foreign key checks even on error
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            $this->command->error('❌ Error occurred: ' . $e->getMessage());
            $this->command->error('Database may be in an inconsistent state. Please restore from backup.');
            throw $e;
        }
    }
    
    /**
     * Truncate a table if it exists
     */
    private function truncateTable(string $tableName, array &$deletedTables): void
    {
        if (Schema::hasTable($tableName)) {
            try {
                DB::table($tableName)->truncate();
                $deletedTables[] = $tableName;
                $this->command->info("   ✓ Cleared: {$tableName}");
            } catch (\Exception $e) {
                $this->command->warn("   ⚠ Failed to clear {$tableName}: " . $e->getMessage());
            }
        } else {
            $this->command->comment("   - Skipped: {$tableName} (table does not exist)");
        }
    }
}
