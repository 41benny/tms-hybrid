<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Finance\CashBankTransaction;
use App\Services\VoucherNumberService;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Generate voucher numbers for existing transactions
        $voucherService = new VoucherNumberService();
        
        CashBankTransaction::whereNull('voucher_number')
            ->orderBy('tanggal')
            ->orderBy('id')
            ->chunk(100, function ($transactions) use ($voucherService) {
                foreach ($transactions as $transaction) {
                    try {
                        $voucherNumber = $voucherService->generate(
                            $transaction->cash_bank_account_id,
                            $transaction->jenis,
                            $transaction->tanggal
                        );
                        
                        $transaction->update(['voucher_number' => $voucherNumber]);
                    } catch (\Exception $e) {
                        // Skip if error
                        \Log::error('Failed to generate voucher number for transaction ' . $transaction->id . ': ' . $e->getMessage());
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set all voucher numbers to null
        CashBankTransaction::query()->update(['voucher_number' => null]);
    }
};
