<?php

use App\Models\User;

return [
    /*
    |--------------------------------------------------------------------------
    | Role based permissions
    |--------------------------------------------------------------------------
    |
    | Simple static mapping for the core actions we need to guard. Adjust this
    | map if you introduce new roles or want to extend an existing role's
    | abilities. Super Admin will bypass these checks automatically.
    |
    */
    'role_permissions' => [
        User::ROLE_SUPER_ADMIN => [
            'invoices.view',
            'invoices.create',
            'invoices.update',
            'invoices.submit',
            'invoices.approve',
            'invoices.manage_status',
            'invoices.cancel',
        ],
        User::ROLE_ADMIN => [
            'invoices.view',
            'invoices.create',
            'invoices.update',
            'invoices.submit',
            'invoices.manage_status',
            'invoices.cancel',
        ],
        User::ROLE_SALES => [
            'invoices.view',
            'invoices.create',
            'invoices.submit',
        ],
        User::ROLE_ACCOUNTING => [
            // Read-only access to all operational modules
            'dashboard.view',
            'customers.view',
            'vendors.view',
            'trucks.view',
            'drivers.view',
            'sales.view',
            'equipment.view',
            'job_orders.view',
            'shipment_legs.view',
            'hutang.view',
            'invoices.view',
            'payment_requests.view',
            'cash_banks.view',
            
            // Full access to accounting modules
            'accounting.journals.view',
            'accounting.journals.create',
            'accounting.journals.update',
            'accounting.journals.delete',
            'accounting.coa.view',
            'accounting.coa.create',
            'accounting.coa.update',
            'accounting.coa.delete',
            'accounting.general_ledger.view',
            'accounting.general_ledger.export',
            'accounting.periods.view',
            'accounting.periods.manage',
            'accounting.trial_balance.view',
            'accounting.trial_balance.export',
            'accounting.profit_loss.view',
            'accounting.profit_loss.export',
            'accounting.balance_sheet.view',
            'accounting.balance_sheet.export',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Checklist permission options (per user)
    |--------------------------------------------------------------------------
    |
    | These are the granular actions that can be assigned to users individually
    | from the User form. Group them by section to render in the UI.
    |
    */
    'available_permissions' => [
        'dashboard' => [
            'label' => 'Dashboard',
            'items' => [
                'dashboard.view' => 'Access Dashboard',
            ],
        ],
        'customers' => [
            'label' => 'Customers',
            'items' => [
                'customers.view' => 'View Customer',
                'customers.create' => 'Create Customer',
                'customers.update' => 'Edit Customer',
                'customers.delete' => 'Delete Customer',
            ],
        ],
        'vendors' => [
            'label' => 'Vendors',
            'items' => [
                'vendors.view' => 'View Vendor',
                'vendors.create' => 'Create Vendor',
                'vendors.update' => 'Edit Vendor',
                'vendors.delete' => 'Delete Vendor',
            ],
        ],
        'trucks' => [
            'label' => 'Trucks',
            'items' => [
                'trucks.view' => 'View Truck',
                'trucks.create' => 'Create Truck',
                'trucks.update' => 'Edit Truck',
                'trucks.delete' => 'Delete Truck',
            ],
        ],
        'drivers' => [
            'label' => 'Drivers',
            'items' => [
                'drivers.view' => 'View Driver',
                'drivers.create' => 'Create Driver',
                'drivers.update' => 'Edit Driver',
                'drivers.delete' => 'Delete Driver',
            ],
        ],
        'sales' => [
            'label' => 'Sales',
            'items' => [
                'sales.view' => 'View Sales',
                'sales.create' => 'Create Sales',
                'sales.update' => 'Edit Sales',
                'sales.delete' => 'Delete Sales',
            ],
        ],
        'equipment' => [
            'label' => 'Equipment',
            'items' => [
                'equipment.view' => 'View Equipment',
                'equipment.create' => 'Create Equipment',
                'equipment.update' => 'Edit Equipment',
                'equipment.delete' => 'Delete Equipment',
            ],
        ],
        'job_orders' => [
            'label' => 'Job Orders',
            'items' => [
                'job_orders.view' => 'Lihat Job Order',
                'job_orders.create' => 'Tambah Job Order',
                'job_orders.update' => 'Edit Job Order',
                'job_orders.delete' => 'Hapus Job Order',
                'job_orders.export' => 'Export Job Order',
            ],
        ],
        'shipment_legs' => [
            'label' => 'Shipment Legs',
            'items' => [
                'shipment_legs.view' => 'Lihat Shipment Leg',
                'shipment_legs.create' => 'Tambah Shipment Leg',
                'shipment_legs.update' => 'Edit Shipment Leg',
                'shipment_legs.delete' => 'Hapus Shipment Leg',
            ],
        ],
        'hutang' => [
            'label' => 'Dashboard Hutang',
            'items' => [
                'hutang.view' => 'View Payable Dashboard',
            ],
        ],
        'invoices' => [
            'label' => 'Invoices',
            'items' => [
                'invoices.view' => 'View Invoice',
                'invoices.create' => 'Create Invoice',
                'invoices.update' => 'Edit Invoice',
                'invoices.submit' => 'Submit for Approval',
                'invoices.approve' => 'Approve / Reject',
                'invoices.manage_status' => 'Manage Status (post, mark as sent/paid)',
                'invoices.cancel' => 'Cancel Invoice',
            ],
        ],
        'payment_requests' => [
            'label' => 'Pengajuan Pembayaran',
            'items' => [
                'payment_requests.view' => 'View Request',
                'payment_requests.create' => 'Create Request',
                'payment_requests.update' => 'Edit Request',
                'payment_requests.approve' => 'Approve Request',
                'payment_requests.delete' => 'Delete Request',
            ],
        ],
        'cash_banks' => [
            'label' => 'Cash & Bank',
            'items' => [
                'cash_banks.view' => 'View Transaction',
                'cash_banks.create' => 'Create Transaction',
                'cash_banks.update' => 'Edit Transaction',
                'cash_banks.approve' => 'Approve Transaction',
                'cash_banks.delete' => 'Void / Delete Transaction',
            ],
        ],
        'accounting.journals' => [
            'label' => 'Jurnal Umum',
            'items' => [
                'accounting.journals.view' => 'View Journal',
                'accounting.journals.create' => 'Create Manual Journal',
                'accounting.journals.update' => 'Edit Journal',
                'accounting.journals.delete' => 'Delete Journal',
            ],
        ],
        'accounting.coa' => [
            'label' => 'Chart of Accounts',
            'items' => [
                'accounting.coa.view' => 'View Account (COA)',
                'accounting.coa.create' => 'Create Account',
                'accounting.coa.update' => 'Edit Account',
                'accounting.coa.delete' => 'Delete Account',
            ],
        ],
        'accounting.general-ledger' => [
            'label' => 'General Ledger',
            'items' => [
                'accounting.general_ledger.view' => 'Access General Ledger',
                'accounting.general_ledger.export' => 'Export General Ledger',
            ],
        ],
        'accounting.periods' => [
            'label' => 'Accounting Periods',
            'items' => [
                'accounting.periods.view' => 'View Periods',
                'accounting.periods.manage' => 'Open/Close Periods',
            ],
        ],
        'accounting.trial-balance' => [
            'label' => 'Trial Balance',
            'items' => [
                'accounting.trial_balance.view' => 'Access Trial Balance',
                'accounting.trial_balance.export' => 'Export Trial Balance',
            ],
        ],
        'accounting.profit-loss' => [
            'label' => 'Profit & Loss',
            'items' => [
                'accounting.profit_loss.view' => 'Access Profit & Loss',
                'accounting.profit_loss.export' => 'Export Profit & Loss',
            ],
        ],
        'accounting.balance-sheet' => [
            'label' => 'Balance Sheet',
            'items' => [
                'accounting.balance_sheet.view' => 'Access Balance Sheet',
                'accounting.balance_sheet.export' => 'Export Balance Sheet',
            ],
        ],
        'ai-assistant' => [
            'label' => 'AI Assistant',
            'items' => [
                'ai_assistant.view' => 'Access AI Assistant',
                'ai_assistant.chat' => 'Chat with AI',
            ],
        ],
        'admin.users' => [
            'label' => 'Manajemen User',
            'items' => [
                'admin.users.view' => 'View User',
                'admin.users.create' => 'Create User',
                'admin.users.update' => 'Edit User',
                'admin.users.delete' => 'Delete User',
                'admin.users.manage_permissions' => 'Manage Permissions',
            ],
        ],
    ],
];
