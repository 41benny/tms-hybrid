<?php

namespace App\Services;

use App\Models\Finance\CashBankTransaction;
use App\Models\Finance\CashBankAccount;
use Carbon\Carbon;

class VoucherNumberService
{
    /**
     * Generate voucher number dengan format: [BANK_CODE][M/K]YYMM001
     * 
     * @param int $cashBankAccountId
     * @param string $jenis (cash_in | cash_out)
     * @param Carbon|null $tanggal
     * @return string
     */
    public function generate(int $cashBankAccountId, string $jenis, ?Carbon $tanggal = null): string
    {
        $tanggal = $tanggal ?? now();
        $account = CashBankAccount::findOrFail($cashBankAccountId);
        
        // Get bank code (prioritize 'code' field, then 'bank_code', default to 'KSB' if not set)
        $bankCode = $account->code ?? $account->bank_code ?? 'KSB';
        
        // M untuk Masuk (cash_in), K untuk Keluar (cash_out)
        $direction = $jenis === 'cash_in' ? 'M' : 'K';
        
        // YYMM format
        $yearMonth = $tanggal->format('ym');
        
        // Prefix: MDMM2511
        $prefix = $bankCode . $direction . $yearMonth;
        
        // Get last number for this month
        $lastVoucher = CashBankTransaction::where('voucher_number', 'LIKE', $prefix . '%')
            ->whereYear('tanggal', $tanggal->year)
            ->whereMonth('tanggal', $tanggal->month)
            ->orderBy('voucher_number', 'desc')
            ->first();
        
        if ($lastVoucher) {
            // Extract number dari voucher terakhir
            $lastNumber = (int) substr($lastVoucher->voucher_number, -3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        // Format: 001, 002, 003, ...
        $sequence = str_pad($newNumber, 3, '0', STR_PAD_LEFT);
        
        return $prefix . $sequence;
    }
    
    /**
     * Check if voucher number already exists
     * 
     * @param string $voucherNumber
     * @return bool
     */
    public function exists(string $voucherNumber): bool
    {
        return CashBankTransaction::where('voucher_number', $voucherNumber)->exists();
    }
}
