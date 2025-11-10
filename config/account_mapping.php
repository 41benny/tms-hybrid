<?php

return [
    // Mapping akun default (gunakan CODE COA). Sesuaikan via env/config sesuai COA Anda.
    'ar' => env('ACC_AR_CODE', '1200'),            // Piutang Usaha
    'ap' => env('ACC_AP_CODE', '2100'),            // Hutang Usaha
    'revenue' => env('ACC_REVENUE_CODE', '4100'),  // Pendapatan Jasa Angkut
    'vat_out' => env('ACC_VAT_OUT_CODE', '2210'),  // PPN Keluaran
    'vat_in' => env('ACC_VAT_IN_CODE', '2220'),    // PPN Masukan
    'cash' => env('ACC_CASH_CODE', '1100'),        // Kas
    'bank' => env('ACC_BANK_CODE', '1110'),        // Bank

    // Beban
    'expense_vendor' => env('ACC_EXP_VENDOR_CODE', '5200'),
    'expense_fuel' => env('ACC_EXP_FUEL_CODE', '5100'),
    'expense_toll' => env('ACC_EXP_TOLL_CODE', '5110'),
    'expense_meal' => env('ACC_EXP_MEAL_CODE', '5120'),
    'expense_other' => env('ACC_EXP_OTHER_CODE', '5300'),
];
