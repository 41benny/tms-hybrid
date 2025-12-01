<?php

return [
    // Mapping akun default (gunakan CODE COA). Sesuaikan via env/config sesuai COA Anda.

    // === ASET ===
    'ar' => env('ACC_AR_CODE', '1200'),            // Piutang Usaha
    'cash' => env('ACC_CASH_CODE', '1110'),        // Kas Besar (default)
    'bank' => env('ACC_BANK_CODE', '1120'),        // Bank Operasional (default)
    'inventory' => env('ACC_INVENTORY_CODE', '1400'), // Persediaan Sparepart
    'prepaid_expense' => env('ACC_PREPAID_CODE', '1500'), // Uang Muka & Biaya Dimuka
    'pph23_claim' => env('ACC_PPH23_CLAIM_CODE', '1530'), // Piutang PPh 23 Dipotong

    // === KEWAJIBAN ===
    'ap' => env('ACC_AP_CODE', '2100'),            // Hutang Usaha
    'vat_out' => env('ACC_VAT_OUT_CODE', '2210'),  // PPN Keluaran
    'vat_in' => env('ACC_VAT_IN_CODE', '2220'),    // PPN Masukan
    // PPN non kreditabel (dibebankan langsung)
    'vat_in_noncreditable' => env('ACC_VAT_IN_NONCREDIT_CODE', '5220'),
    'pph21' => env('ACC_PPH21_CODE', '2230'),      // PPh 21
    'pph23' => env('ACC_PPH23_CODE', '2240'),      // PPh 23
    'accrued_salary' => env('ACC_ACCRUED_SALARY_CODE', '2310'), // BYMH - Gaji
    'accrued_provision' => env('ACC_ACCRUED_PROVISION_CODE', '2320'), // BYMH - Provisi Bank
    'customer_deposit' => env('ACC_CUSTOMER_DEPOSIT_CODE', '2150'), // Hutang Uang Muka Customer (DP)
    'driver_payable' => env('ACC_DRIVER_PAYABLE_CODE', '2155'), // Hutang Uang Jalan Supir
    'driver_savings' => env('ACC_DRIVER_SAVINGS_CODE', '2160'), // Hutang Tabungan Supir
    'driver_guarantee' => env('ACC_DRIVER_GUARANTEE_CODE', '2170'), // Hutang Jaminan Supir
    'suspense' => env('ACC_SUSPENSE_CODE', '2900'), // Akun Sementara (Ayat Silang)

    // === EKUITAS ===
    'retained_earnings' => env('ACC_RETAINED_EARNINGS_CODE', '3200'), // Laba Ditahan

    // === PENDAPATAN USAHA (Kepala 4) ===
    'revenue' => env('ACC_REVENUE_CODE', '4100'),  // Pendapatan Jasa Angkutan

    // === HPP / BEBAN POKOK (Kepala 5) ===
    'expense_fuel' => env('ACC_EXP_FUEL_CODE', '5100'),       // Beban BBM
    'expense_toll' => env('ACC_EXP_TOLL_CODE', '5110'),       // Beban Tol
    'expense_meal' => env('ACC_EXP_MEAL_CODE', '5120'),       // Beban Uang Jalan/Makan Driver
    'expense_vendor' => env('ACC_EXP_VENDOR_CODE', '5200'),   // Beban Vendor/Borongan
    'expense_maintenance' => env('ACC_EXP_MAINTENANCE_CODE', '5210'), // Beban Maintenance Kendaraan
    'expense_sparepart' => env('ACC_EXP_SPAREPART_CODE', '5220'), // Beban Sparepart
    'expense_operational' => env('ACC_EXP_OPERATIONAL_CODE', '5300'), // Beban Operasional Langsung Lainnya

    // === BEBAN ADMINISTRASI & UMUM (Kepala 6) ===
    'expense_salary' => env('ACC_EXP_SALARY_CODE', '6100'),   // Beban Gaji & Upah Karyawan
    'expense_tax' => env('ACC_EXP_TAX_CODE', '6110'),         // Beban Pajak & Perizinan
    'expense_stationery' => env('ACC_EXP_STATIONERY_CODE', '6120'), // Beban ATK & Perlengkapan Kantor
    'expense_utility' => env('ACC_EXP_UTILITY_CODE', '6140'), // Beban Listrik & Air
    'expense_communication' => env('ACC_EXP_COMMUNICATION_CODE', '6150'), // Beban Telepon & Internet
    'expense_rent' => env('ACC_EXP_RENT_CODE', '6160'),       // Beban Sewa Kantor
    'expense_general' => env('ACC_EXP_GENERAL_CODE', '6200'), // Beban Umum Lainnya

    // === PENDAPATAN & BEBAN LAIN-LAIN (Kepala 7) ===
    'income_interest' => env('ACC_INCOME_INTEREST_CODE', '7110'),   // Pendapatan Bunga Bank
    'income_forex_gain' => env('ACC_INCOME_FOREX_GAIN_CODE', '7120'), // Pendapatan Selisih Kurs
    'income_other' => env('ACC_INCOME_OTHER_CODE', '7190'),         // Pendapatan Lain-lain Lainnya

    'expense_loan_interest' => env('ACC_EXP_LOAN_INTEREST_CODE', '7210'), // Beban Bunga Pinjaman
    'expense_bank_provision' => env('ACC_EXP_BANK_PROVISION_CODE', '7220'), // Beban Provisi Bank
    'expense_bank_admin' => env('ACC_EXP_BANK_ADMIN_CODE', '7230'), // Beban Administrasi Bank
    'expense_bank_tax' => env('ACC_EXP_BANK_TAX_CODE', '7240'),     // Beban Pajak Bank
    'expense_forex_loss' => env('ACC_EXP_FOREX_LOSS_CODE', '7250'), // Beban Selisih Kurs
    'expense_other' => env('ACC_EXP_OTHER_CODE', '7290'),           // Beban Lain-lain Lainnya
];
