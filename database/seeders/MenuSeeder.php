<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            [
                'slug' => 'dashboard',
                'label' => 'Dashboard',
                'route_name' => 'dashboard',
                'section' => 'general',
                'is_default' => true,
            ],
            [
                'slug' => 'customers',
                'label' => 'Customers',
                'route_name' => 'customers.index',
                'section' => 'master',
            ],
            [
                'slug' => 'vendors',
                'label' => 'Vendors',
                'route_name' => 'vendors.index',
                'section' => 'master',
            ],
            [
                'slug' => 'trucks',
                'label' => 'Trucks',
                'route_name' => 'trucks.index',
                'section' => 'master',
            ],
            [
                'slug' => 'drivers',
                'label' => 'Drivers',
                'route_name' => 'drivers.index',
                'section' => 'master',
            ],
            [
                'slug' => 'sales',
                'label' => 'Sales',
                'route_name' => 'sales.index',
                'section' => 'master',
            ],
            [
                'slug' => 'equipment',
                'label' => 'Equipment',
                'route_name' => 'equipment.index',
                'section' => 'master',
            ],
            [
                'slug' => 'job-orders',
                'label' => 'Job Orders',
                'route_name' => 'job-orders.index',
                'section' => 'operations',
            ],
            [
                'slug' => 'hutang',
                'label' => 'Dashboard Hutang',
                'route_name' => 'hutang.dashboard',
                'section' => 'finance',
            ],
            [
                'slug' => 'invoices',
                'label' => 'Invoices',
                'route_name' => 'invoices.index',
                'section' => 'finance',
            ],
            [
                'slug' => 'payment-requests',
                'label' => 'Pengajuan Pembayaran',
                'route_name' => 'payment-requests.index',
                'section' => 'finance',
            ],
            [
                'slug' => 'cash-banks',
                'label' => 'Cash/Bank',
                'route_name' => 'cash-banks.index',
                'section' => 'finance',
            ],
            [
                'slug' => 'accounting.journals',
                'label' => 'Jurnal',
                'route_name' => 'journals.index',
                'section' => 'accounting',
            ],
            [
                'slug' => 'accounting.coa',
                'label' => 'Chart of Accounts',
                'route_name' => 'chart-of-accounts.index',
                'section' => 'accounting',
            ],
            [
                'slug' => 'accounting.general-ledger',
                'label' => 'General Ledger',
                'route_name' => 'reports.general-ledger',
                'section' => 'accounting',
            ],
            [
                'slug' => 'accounting.periods',
                'label' => 'Accounting Periods',
                'route_name' => 'accounting.periods.index',
                'section' => 'accounting',
            ],
            [
                'slug' => 'accounting.trial-balance',
                'label' => 'Trial Balance',
                'route_name' => 'reports.trial-balance',
                'section' => 'accounting',
            ],
            [
                'slug' => 'accounting.profit-loss',
                'label' => 'Profit & Loss',
                'route_name' => 'reports.profit-loss',
                'section' => 'accounting',
            ],
            [
                'slug' => 'accounting.balance-sheet',
                'label' => 'Balance Sheet',
                'route_name' => 'reports.balance-sheet',
                'section' => 'accounting',
            ],
            [
                'slug' => 'ai-assistant',
                'label' => 'AI Assistant',
                'route_name' => 'ai-assistant.index',
                'section' => 'ai',
            ],
            [
                'slug' => 'admin.users',
                'label' => 'Manajemen User',
                'route_name' => 'admin.users.index',
                'section' => 'admin',
            ],
        ];

        foreach ($menus as $index => $menu) {
            Menu::updateOrCreate(
                ['slug' => $menu['slug']],
                array_merge($menu, ['sort' => $index + 1])
            );
        }
    }
}
