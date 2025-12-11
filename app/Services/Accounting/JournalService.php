<?php

namespace App\Services\Accounting;

use App\Models\Accounting\Journal;
use App\Models\Finance\CashBankTransaction;
use App\Models\Finance\Invoice;
use App\Models\Finance\VendorBill;
use App\Models\Inventory\PartPurchase;
use App\Models\Inventory\PartUsage;
use InvalidArgumentException;

class JournalService
{
    public function __construct(protected PostingService $posting) {}

    protected function map(string $key): string
    {
        $code = config('account_mapping.'.$key);
        if (! $code) {
            throw new InvalidArgumentException('Mapping akun tidak ditemukan: '.$key);
        }

        return (string) $code;
    }

    protected function alreadyPosted(string $sourceType, int $sourceId): ?Journal
    {
        return Journal::where('source_type', $sourceType)->where('source_id', $sourceId)->first();
    }

    public function postInvoice(Invoice $invoice): Journal
    {
        if ($j = $this->alreadyPosted('invoice', $invoice->id)) {
            return $j;
        }

        // Load items untuk breakdown DPP, PPN
        $invoice->load('items');

        // Gunakan tax_amount dari invoice untuk PPN
        $ppn = (float) ($invoice->tax_amount ?? 0);
        
        // DPP adalah subtotal (total amount - tax)
        $dpp = (float) $invoice->subtotal;
        
        // Jika subtotal kosong, hitung dari items
        if ($dpp == 0) {
            foreach ($invoice->items as $item) {
                $dpp += $item->amount;
            }
        }

        $totalReceivable = $invoice->total_amount;

        if ($totalReceivable <= 0) {
            throw new InvalidArgumentException('Total invoice 0');
        }

        // Extract job_order_id from first item (for tracking)
        $jobOrderId = $invoice->items->first()?->job_order_id;

        $ar = $this->map('ar');
        $vatOut = $this->map('vat_out');

        $lines = [];

        // Dr. Piutang Usaha (total yang akan diterima dari customer)
        $lines[] = ['account_code' => $ar, 'debit' => $totalReceivable, 'credit' => 0, 'desc' => 'Piutang Invoice '.$invoice->invoice_number, 'customer_id' => $invoice->customer_id, 'job_order_id' => $jobOrderId];

        // Cek apakah ini invoice DP atau Normal
        if ($invoice->invoice_type === 'down_payment') {
            // INVOICE DP: Cr. Hutang Uang Muka (bukan pendapatan)
            $customerDeposit = $this->map('customer_deposit');
            $lines[] = ['account_code' => $customerDeposit, 'debit' => 0, 'credit' => $dpp, 'desc' => 'Uang Muka Customer Invoice '.$invoice->invoice_number, 'customer_id' => $invoice->customer_id, 'job_order_id' => $jobOrderId];
        } else {
            // INVOICE NORMAL/FINAL: Cr. Pendapatan (DPP)
            $revenue = $this->map('revenue');
            $lines[] = ['account_code' => $revenue, 'debit' => 0, 'credit' => $dpp, 'desc' => 'Pendapatan Invoice '.$invoice->invoice_number, 'customer_id' => $invoice->customer_id, 'job_order_id' => $jobOrderId];
            
            // Jika invoice final dengan DP, balik hutang uang muka
            if ($invoice->invoice_type === 'final' && $invoice->related_invoice_id) {
                $relatedInvoice = Invoice::find($invoice->related_invoice_id);
                if ($relatedInvoice && $relatedInvoice->invoice_type === 'down_payment') {
                    $dpAmount = (float) $relatedInvoice->subtotal;
                    $customerDeposit = $this->map('customer_deposit');
                    
                    // Dr. Hutang Uang Muka (mengurangi hutang)
                    $lines[] = ['account_code' => $customerDeposit, 'debit' => $dpAmount, 'credit' => 0, 'desc' => 'Pembalikan DP dari Invoice '.$relatedInvoice->invoice_number, 'customer_id' => $invoice->customer_id, 'job_order_id' => $jobOrderId];
                    
                    // Cr. Pendapatan (mengakui pendapatan DP)
                    $lines[] = ['account_code' => $revenue, 'debit' => 0, 'credit' => $dpAmount, 'desc' => 'Pengakuan Pendapatan DP dari Invoice '.$relatedInvoice->invoice_number, 'customer_id' => $invoice->customer_id, 'job_order_id' => $jobOrderId];
                }
            }
        }

        // Cr. PPN Keluaran (jika ada PPN)
        if ($ppn > 0) {
            $lines[] = ['account_code' => $vatOut, 'debit' => 0, 'credit' => $ppn, 'desc' => 'PPN Keluaran Invoice '.$invoice->invoice_number, 'customer_id' => $invoice->customer_id, 'job_order_id' => $jobOrderId];
        }

        \Log::info('Invoice Journal Breakdown', [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'invoice_type' => $invoice->invoice_type,
            'total_amount' => $invoice->total_amount,
            'subtotal' => $invoice->subtotal,
            'tax_amount' => $invoice->tax_amount,
            'dpp' => $dpp,
            'ppn' => $ppn,
            'total_receivable' => $totalReceivable,
            'lines' => $lines
        ]);

        $journal = $this->posting->postGeneral([
            'journal_date' => $invoice->invoice_date->toDateString(),
            'source_type' => 'invoice',
            'source_id' => $invoice->id,
            'memo' => 'Invoice '.$invoice->invoice_number,
        ], $lines);

        // Balik jurnal biaya dimuka HANYA untuk invoice NORMAL atau FINAL (bukan DP)
        if ($invoice->invoice_type !== 'down_payment') {
            $this->reversePrepaidsForInvoice($invoice);
        }

        // Save journal_id to invoice for tracking
        $invoice->update(['journal_id' => $journal->id]);

        return $journal;
    }

    protected function getCashAccountCode(CashBankTransaction $trx): string
    {
        // Ensure account is loaded
        if (!$trx->relationLoaded('account')) {
            $trx->load('account.chartOfAccount');
        }

        // Try to get specific COA from CashBankAccount
        if ($trx->account && $trx->account->chartOfAccount) {
            return $trx->account->chartOfAccount->code;
        }
        
        // Fallback to global mapping
        return $this->map('cash');
    }

    public function postCustomerPayment(CashBankTransaction $trx): Journal
    {
        if ($j = $this->alreadyPosted('customer_payment', $trx->id)) {
            return $j;
        }
        $cash = $this->getCashAccountCode($trx);
        $ar = $this->map('ar');
        
        // Amount di sini adalah Total Invoice yang dilunasi (AR)
        $arAmount = (float) $trx->amount;
        $withholding = (float) ($trx->withholding_pph23 ?? 0);
        $adminFee = (float) ($trx->admin_fee ?? 0);
        
        // Uang yang masuk ke bank = AR - PPh23 - Admin Fee
        $cashReceived = $arAmount - $withholding - $adminFee;

        $lines = [
            ['account_code' => $cash, 'debit' => $cashReceived, 'credit' => 0, 'desc' => 'Penerimaan invoice '.$trx->invoice?->invoice_number, 'customer_id' => $trx->customer_id],
        ];

        if ($withholding > 0) {
            $pph23Claim = $this->map('pph23_claim');
            $lines[] = ['account_code' => $pph23Claim, 'debit' => $withholding, 'credit' => 0, 'desc' => 'Piutang PPh 23 invoice '.$trx->invoice?->invoice_number, 'customer_id' => $trx->customer_id];
        }
        
        if ($adminFee > 0) {
            $adminExp = $this->map('expense_bank_admin');
            $lines[] = ['account_code' => $adminExp, 'debit' => $adminFee, 'credit' => 0, 'desc' => 'Biaya admin bank', 'customer_id' => $trx->customer_id];
        }

        $lines[] = ['account_code' => $ar, 'debit' => 0, 'credit' => $arAmount, 'desc' => 'Pelunasan invoice '.$trx->invoice?->invoice_number, 'customer_id' => $trx->customer_id];

        return $this->posting->postGeneral([
            'journal_date' => $trx->tanggal->toDateString(),
            'source_type' => 'customer_payment',
            'source_id' => $trx->id,
            'memo' => 'Penerimaan pembayaran',
        ], $lines);
    }

    public function postVendorBill(VendorBill $bill): Journal
    {
        if ($j = $this->alreadyPosted('vendor_bill', $bill->id)) {
            return $j;
        }

        // Load items untuk breakdown DPP, PPN, PPh 23
        $bill->load('items.shipmentLeg');

        $dpp = 0;
        $ppn = 0;
        $pph23 = 0;

        // Hitung dari items
        foreach ($bill->items as $item) {
            $desc = strtolower($item->description);
            if (str_contains($desc, 'ppn')) {
                $ppn += $item->subtotal;
            } elseif (str_contains($desc, 'pph') || str_contains($desc, 'pph23')) {
                // PPh23 disimpan sebagai negatif, ambil nilai absolutnya untuk jurnal
                $pph23 += abs($item->subtotal);
            } else {
                // DPP adalah semua item selain PPN dan PPh
                $dpp += $item->subtotal;
            }
        }

        // Total payable = total_amount dari vendor bill
        // atau hitung manual: DPP + PPN - PPh23
        $netPayable = $bill->total_amount;

        $ap = $this->map('ap');
        $lines = [];

        // Cek apakah vendor bill terkait shipment leg (ada items dengan shipment_leg_id)
        $hasShipmentLeg = $bill->items()->whereNotNull('shipment_leg_id')->exists();
        
        // Extract job_order_id from first shipment leg item
        $jobOrderId = null;
        if ($hasShipmentLeg) {
            $firstLegItem = $bill->items->whereNotNull('shipment_leg_id')->first();
            if ($firstLegItem && $firstLegItem->shipmentLeg) {
                $jobOrderId = $firstLegItem->shipmentLeg->job_order_id;
            }
        }

        if ($hasShipmentLeg) {
            // Untuk vendor bill shipment leg: Dr Biaya Dimuka
            $prepaid = $this->map('prepaid_expense');
            $lines[] = ['account_code' => $prepaid, 'debit' => $dpp, 'credit' => 0, 'desc' => 'Biaya dimuka vendor bill '.$bill->vendor_bill_number, 'vendor_id' => $bill->vendor_id, 'job_order_id' => $jobOrderId];
        } else {
            // Untuk vendor bill non-shipment: Dr Beban Vendor (langsung expense)
            $exp = $this->map('expense_vendor');
            $lines[] = ['account_code' => $exp, 'debit' => $dpp, 'credit' => 0, 'desc' => 'Biaya vendor bill '.$bill->vendor_bill_number, 'vendor_id' => $bill->vendor_id, 'job_order_id' => $jobOrderId];
        }

        // Dr PPN Masukan (jika ada PPN)
        if ($ppn > 0) {
            $useNonCredit = (bool) ($bill->ppn_noncreditable ?? false);
            if ($useNonCredit) {
                try {
                    $vatAccount = $this->map('vat_in_noncreditable');
                } catch (\InvalidArgumentException $e) {
                    $vatAccount = $this->map('expense_vendor');
                }
            } else {
                $vatAccount = $this->map('vat_in');
            }

            $lines[] = ['account_code' => $vatAccount, 'debit' => $ppn, 'credit' => 0, 'desc' => 'PPN Masukan vendor bill '.$bill->vendor_bill_number, 'vendor_id' => $bill->vendor_id, 'job_order_id' => $jobOrderId];
        }

        // Cr Hutang PPh 23 (jika ada potongan PPh 23)
        if ($pph23 > 0) {
            $pph23Payable = $this->map('pph23');
            $lines[] = ['account_code' => $pph23Payable, 'debit' => 0, 'credit' => $pph23, 'desc' => 'PPh 23 dipotong vendor bill '.$bill->vendor_bill_number, 'vendor_id' => $bill->vendor_id, 'job_order_id' => $jobOrderId];
        }

        // Cr Hutang Usaha (net payable)
        $lines[] = ['account_code' => $ap, 'debit' => 0, 'credit' => $netPayable, 'desc' => 'Hutang vendor bill '.$bill->vendor_bill_number, 'vendor_id' => $bill->vendor_id, 'job_order_id' => $jobOrderId];

        \Log::info('Vendor Bill Journal Breakdown', [
            'vendor_bill_id' => $bill->id,
            'vendor_bill_number' => $bill->vendor_bill_number,
            'total_amount' => $bill->total_amount,
            'dpp' => $dpp,
            'ppn' => $ppn,
            'pph23' => $pph23,
            'net_payable' => $netPayable,
            'lines' => $lines
        ]);

        $journal = $this->posting->postGeneral([
            'journal_date' => $bill->bill_date->toDateString(),
            'source_type' => 'vendor_bill',
            'source_id' => $bill->id,
            'memo' => 'Vendor bill '.$bill->vendor_bill_number,
        ], $lines);

        // Save journal_id to vendor bill for tracking
        $bill->update(['journal_id' => $journal->id]);

        return $journal;
    }

    public function postVendorPayment(CashBankTransaction $trx): Journal
    {
        if ($j = $this->alreadyPosted('vendor_payment', $trx->id)) {
            return $j;
        }
        $ap = $this->map('ap');
        $cash = $this->getCashAccountCode($trx);
        
        // Amount di sini adalah Total Hutang yang dibayar (AP)
        $apAmount = (float) $trx->amount;
        $withholding = (float) ($trx->withholding_pph23 ?? 0);
        $adminFee = (float) ($trx->admin_fee ?? 0);
        
        // Uang yang keluar dari bank = AP - PPh23 + Admin Fee
        // Note: PPh23 di sini adalah PPh23 yang KITA potong saat bayar vendor (Hutang PPh23)
        // Tapi biasanya PPh23 sudah dicatat saat Vendor Bill (Accrual Basis).
        // Jika saat payment ada PPh23 lagi, berarti ada adjustment?
        // Asumsi: withholding_pph23 di payment adalah PPh23 yang baru dipotong saat payment (Cash Basis logic?)
        // ATAU pelunasan hutang PPh23?
        // Biasanya di sistem ini, PPh23 dicatat saat Bill. Jadi saat payment hanya bayar Net.
        // Tapi jika user input PPh23 saat payment, mungkin maksudnya koreksi atau pemotongan cash.
        
        // Mari ikuti logika form: Total Bank = Amount - PPh23 + Admin
        $cashPaid = $apAmount - $withholding + $adminFee;
        
        $lines = [
            ['account_code' => $ap, 'debit' => $apAmount, 'credit' => 0, 'desc' => 'Pelunasan hutang vendor', 'vendor_id' => $trx->vendor_id],
        ];
        
        if ($adminFee > 0) {
            $adminExp = $this->map('expense_bank_admin');
            $lines[] = ['account_code' => $adminExp, 'debit' => $adminFee, 'credit' => 0, 'desc' => 'Biaya admin bank', 'vendor_id' => $trx->vendor_id];
        }
        
        if ($withholding > 0) {
            // Jika ada PPh23 saat payment, anggap sebagai Hutang PPh23 (yang belum dicatat saat bill?)
            // Atau jika ini mengurangi cash yang dibayar, berarti kita menahan uangnya -> Hutang PPh23 bertambah
            $pph23Payable = $this->map('pph23');
            $lines[] = ['account_code' => $pph23Payable, 'debit' => 0, 'credit' => $withholding, 'desc' => 'PPh 23 dipotong saat pembayaran', 'vendor_id' => $trx->vendor_id];
        }
        
        $lines[] = ['account_code' => $cash, 'debit' => 0, 'credit' => $cashPaid, 'desc' => 'Pembayaran hutang vendor', 'vendor_id' => $trx->vendor_id];

        return $this->posting->postGeneral([
            'journal_date' => $trx->tanggal->toDateString(),
            'source_type' => 'vendor_payment',
            'source_id' => $trx->id,
            'memo' => 'Pembayaran vendor',
        ], $lines);
    }

    public function postExpense(CashBankTransaction $trx): Journal
    {
        if ($j = $this->alreadyPosted('expense', $trx->id)) {
            return $j;
        }
        $cash = $this->getCashAccountCode($trx);
        $expCode = $trx->accountCoa?->code ?? $this->map('expense_other');
        $amt = (float) $trx->amount;
        $lines = [
            ['account_code' => $expCode, 'debit' => $amt, 'credit' => 0, 'desc' => 'Pengeluaran biaya'],
            ['account_code' => $cash, 'debit' => 0, 'credit' => $amt, 'desc' => 'Pengeluaran kas/bank'],
        ];

        return $this->posting->postGeneral([
            'journal_date' => $trx->tanggal->toDateString(),
            'source_type' => 'expense',
            'source_id' => $trx->id,
            'memo' => 'Pengeluaran biaya',
        ], $lines);
    }

    public function postOtherIncome(CashBankTransaction $trx): Journal
    {
        if ($j = $this->alreadyPosted('other_in', $trx->id)) {
            return $j;
        }
        $cash = $this->getCashAccountCode($trx);
        $incomeCode = $trx->accountCoa?->code ?? $this->map('other_income');
        $amt = (float) $trx->amount;
        $lines = [
            ['account_code' => $cash, 'debit' => $amt, 'credit' => 0, 'desc' => 'Penerimaan kas/bank'],
            ['account_code' => $incomeCode, 'debit' => 0, 'credit' => $amt, 'desc' => 'Pendapatan lain-lain'],
        ];

        return $this->posting->postGeneral([
            'journal_date' => $trx->tanggal->toDateString(),
            'source_type' => 'other_in',
            'source_id' => $trx->id,
            'memo' => $trx->description ?? 'Pendapatan lain-lain',
        ], $lines);
    }

    public function postOtherExpense(CashBankTransaction $trx): Journal
    {
        if ($j = $this->alreadyPosted('other_out', $trx->id)) {
            return $j;
        }
        $cash = $this->getCashAccountCode($trx);
        $expenseCode = $trx->accountCoa?->code ?? $this->map('expense_other');
        $amt = (float) $trx->amount;
        $lines = [
            ['account_code' => $expenseCode, 'debit' => $amt, 'credit' => 0, 'desc' => 'Pengeluaran lain-lain'],
            ['account_code' => $cash, 'debit' => 0, 'credit' => $amt, 'desc' => 'Pengeluaran kas/bank'],
        ];

        return $this->posting->postGeneral([
            'journal_date' => $trx->tanggal->toDateString(),
            'source_type' => 'other_out',
            'source_id' => $trx->id,
            'memo' => $trx->description ?? 'Pengeluaran lain-lain',
        ], $lines);
    }

    /**
     * Post driver advance to journal (accrual basis)
     * Called when user clicks "Post" button on driver advance
     * 
     * Dr. Biaya Dimuka - Uang Jalan (gross)
     *   Cr. Hutang Uang Jalan Supir (gross)
     */
    public function postDriverAdvance(\App\Models\Operations\DriverAdvance $advance): Journal
    {
        // Check if already posted
        if ($advance->journal_status === 'posted' || $advance->journal_id) {
            throw new \Exception('Driver advance already posted to journal');
        }
        
        // Load relationships
        $advance->load(['shipmentLeg.jobOrder', 'shipmentLeg.mainCost', 'driver']);
        
        $leg = $advance->shipmentLeg;
        $mainCost = $leg->mainCost;
        
        if (!$mainCost) {
            throw new \Exception('Main cost not found for shipment leg');
        }
        
        // Calculate gross amount
        $grossAmount = (float) ($mainCost->uang_jalan ?? 0)
                     + (float) ($mainCost->bbm ?? 0)
                     + (float) ($mainCost->toll ?? 0)
                     + (float) ($mainCost->other_costs ?? 0);
        
        if ($grossAmount <= 0) {
            throw new \Exception('No uang jalan amount to post');
        }
        
        $prepaid = $this->map('prepaid_expense'); // 1500
        $driverPayable = $this->map('driver_payable'); // 2155
        
        // Get job_order_id for tracking
        $jobOrderId = $leg->job_order_id;
        
        $lines = [
            [
                'account_code' => $prepaid,
                'debit' => $grossAmount,
                'credit' => 0,
                'desc' => 'Biaya dimuka uang jalan - ' . $leg->jobOrder->job_number,
                'job_order_id' => $jobOrderId
            ],
            [
                'account_code' => $driverPayable,
                'debit' => 0,
                'credit' => $grossAmount,
                'desc' => 'Hutang uang jalan supir - ' . ($advance->driver->name ?? 'N/A'),
                'job_order_id' => $jobOrderId
            ],
        ];
        
        $journal = $this->posting->postGeneral([
            'journal_date' => $advance->advance_date,
            'source_type' => 'driver_advance',
            'source_id' => $advance->id,
            'memo' => 'Biaya dimuka uang jalan ' . $advance->advance_number,
        ], $lines);
        
        // Update driver advance status
        $advance->update([
            'journal_status' => 'posted',
            'journal_id' => $journal->id
        ]);
        
        return $journal;
    }

    /**
     * Repost Driver Advance journal when amount is updated.
     * Deletes old journal (if period is open) and creates new one with is_revision=true.
     */
    public function repostDriverAdvance(\App\Models\Operations\DriverAdvance $advance): Journal
    {
        // Load relationships
        $advance->load(['shipmentLeg.jobOrder', 'shipmentLeg.mainCost', 'driver']);

        $leg = $advance->shipmentLeg;
        $mainCost = $leg->mainCost;

        if (!$mainCost) {
            throw new \Exception('Main cost not found for shipment leg');
        }

        // Check if we have an existing journal
        $oldJournal = null;
        if ($advance->journal_id) {
            $oldJournal = Journal::find($advance->journal_id);
        }

        // Check period status
        if ($oldJournal && $oldJournal->period && $oldJournal->period->status !== 'open') {
            throw new \Exception("Tidak dapat melakukan repost karena periode akuntansi {$oldJournal->period->month}/{$oldJournal->period->year} sudah ditutup.");
        }

        // Delete old journal
        if ($oldJournal) {
            $oldJournal->lines()->delete();
            $oldJournal->delete();
        }

        // Reset journal status temporarily
        $advance->update(['journal_status' => 'unposted', 'journal_id' => null]);

        // Calculate new gross amount
        $grossAmount = (float) ($mainCost->uang_jalan ?? 0)
            + (float) ($mainCost->bbm ?? 0)
            + (float) ($mainCost->toll ?? 0)
            + (float) ($mainCost->other_costs ?? 0);

        if ($grossAmount <= 0) {
            throw new \Exception('No uang jalan amount to post');
        }

        $prepaid = $this->map('prepaid_expense');
        $driverPayable = $this->map('driver_payable');
        $jobOrderId = $leg->job_order_id;

        $lines = [
            [
                'account_code' => $prepaid,
                'debit' => $grossAmount,
                'credit' => 0,
                'desc' => 'Biaya dimuka uang jalan - ' . $leg->jobOrder->job_number . ' (Revisi)',
                'job_order_id' => $jobOrderId
            ],
            [
                'account_code' => $driverPayable,
                'debit' => 0,
                'credit' => $grossAmount,
                'desc' => 'Hutang uang jalan supir - ' . ($advance->driver->name ?? 'N/A') . ' (Revisi)',
                'job_order_id' => $jobOrderId
            ],
        ];

        $journal = $this->posting->postGeneral([
            'journal_date' => $advance->advance_date,
            'source_type' => 'driver_advance',
            'source_id' => $advance->id,
            'memo' => 'Biaya dimuka uang jalan ' . $advance->advance_number . ' (Revisi)',
            'is_revision' => true,
            'revised_at' => now(),
        ], $lines);

        // Update driver advance status
        $advance->update([
            'journal_status' => 'posted',
            'journal_id' => $journal->id
        ]);

        return $journal;
    }

    /**
     * Post driver advance payment journal
     * Pays down the liability created when shipment leg was created
     * 
     * DP Payment:
     *   Dr. Hutang Uang Jalan Supir (DP amount)
     *     Cr. Kas/Bank (DP amount)
     * 
     * Settlement:
     *   Dr. Hutang Uang Jalan Supir (gross)
     *     Cr. Kas/Bank (net)
     *     Cr. Hutang Tabungan (savings)
     *     Cr. Hutang Jaminan (guarantee)
     */
    public function postDriverAdvancePayment(CashBankTransaction $trx): Journal
    {
        if ($j = $this->alreadyPosted('uang_jalan', $trx->id)) {
            return $j;
        }
        
        $cash = $this->getCashAccountCode($trx);
        $driverPayable = $this->map('driver_payable'); // 2155 - Hutang Uang Jalan Supir
        
        $netAmount = (float) $trx->amount; // Net paid to driver
        $savingsDeduction = 0;
        $guaranteeDeduction = 0;
        $isSettlement = false;
        
        // Track job_order_id from first advance
        $jobOrderId = null;
        
        // Load driver advance payment records
        $driverAdvancePayments = \App\Models\Operations\DriverAdvancePayment::where('cash_bank_transaction_id', $trx->id)
            ->with(['driverAdvance.shipmentLeg.mainCost'])
            ->get();
        
        foreach ($driverAdvancePayments as $payment) {
            $advance = $payment->driverAdvance;
            if ($advance) {
                // Get job_order_id from first advance
                if (!$jobOrderId && $advance->shipmentLeg) {
                    $jobOrderId = $advance->shipmentLeg->job_order_id;
                }
                
                // Check if this is settlement
                if ($advance->dp_amount > 0 || $advance->status === 'dp_paid') {
                    $isSettlement = true;
                    
                    // Only add deductions during settlement
                    if ($advance->shipmentLeg && $advance->shipmentLeg->mainCost) {
                        $mainCost = $advance->shipmentLeg->mainCost;
                        $savingsDeduction += (float) ($mainCost->driver_savings_deduction ?? 0);
                        $guaranteeDeduction += (float) ($mainCost->driver_guarantee_deduction ?? 0);
                    }
                }
            }
        }
        
        // Gross amount = amount of liability being paid off
        $grossPayable = $netAmount;
        if ($isSettlement) {
            $grossPayable = $netAmount + $savingsDeduction + $guaranteeDeduction;
        }
        
        $lines = [];
        
        // Dr. Hutang Uang Jalan Supir (paying off liability)
        $lines[] = [
            'account_code' => $driverPayable,
            'debit' => $grossPayable,
            'credit' => 0,
            'desc' => 'Pembayaran hutang uang jalan - ' . ($isSettlement ? 'Pelunasan' : 'DP'),
            'job_order_id' => $jobOrderId
        ];
        
        // Cr. Kas/Bank
        $lines[] = [
            'account_code' => $cash,
            'debit' => 0,
            'credit' => $netAmount,
            'desc' => 'Pembayaran uang jalan driver',
            'job_order_id' => $jobOrderId
        ];
        
        // Settlement only: add deduction liabilities
        if ($isSettlement) {
            if ($savingsDeduction > 0) {
                $driverSavings = $this->map('driver_savings');
                $lines[] = [
                    'account_code' => $driverSavings,
                    'debit' => 0,
                    'credit' => $savingsDeduction,
                    'desc' => 'Potongan tabungan supir',
                    'job_order_id' => $jobOrderId
                ];
            }
            
            if ($guaranteeDeduction > 0) {
                $driverGuarantee = $this->map('driver_guarantee');
                $lines[] = [
                    'account_code' => $driverGuarantee,
                    'debit' => 0,
                    'credit' => $guaranteeDeduction,
                    'desc' => 'Potongan jaminan supir',
                    'job_order_id' => $jobOrderId
                ];
            }
        }
        
        return $this->posting->postGeneral([
            'journal_date' => $trx->tanggal->toDateString(),
            'source_type' => 'uang_jalan',
            'source_id' => $trx->id,
            'memo' => $trx->description ?? 'Pembayaran uang jalan driver',
        ], $lines);
    }

    public function postPartPurchase(PartPurchase $purchase): Journal
    {
        if ($j = $this->alreadyPosted('part_purchase', $purchase->id)) {
            return $j;
        }

        // Breakdown: DPP, PPN, PPh 23
        $dpp = (float) ($purchase->dpp ?? $purchase->total_amount);
        $ppn = (float) ($purchase->ppn ?? 0);
        $pph23 = (float) ($purchase->pph23 ?? 0);
        $netPayable = $dpp + $ppn - $pph23; // Total yang harus dibayar

        $ap = $this->map('ap');
        try {
            $inventory = $this->map('inventory');
        } catch (\InvalidArgumentException $e) {
            // Fallback jika inventory belum ada
            $inventory = $this->map('expense_vendor');
        }

        $lines = [];

        // Dr Inventory/Expense (DPP)
        $lines[] = ['account_code' => $inventory, 'debit' => $dpp, 'credit' => 0, 'desc' => 'Pembelian part '.$purchase->purchase_number, 'vendor_id' => $purchase->vendor_id];

        // Dr PPN Masukan (jika ada PPN)
        if ($ppn > 0) {
            $vatIn = $this->map('vat_in');
            $lines[] = ['account_code' => $vatIn, 'debit' => $ppn, 'credit' => 0, 'desc' => 'PPN Masukan pembelian part '.$purchase->purchase_number, 'vendor_id' => $purchase->vendor_id];
        }

        // Cr Hutang PPh 23 (jika ada potongan PPh 23)
        if ($pph23 > 0) {
            $pph23Payable = $this->map('pph23');
            $lines[] = ['account_code' => $pph23Payable, 'debit' => 0, 'credit' => $pph23, 'desc' => 'PPh 23 dipotong pembelian part '.$purchase->purchase_number, 'vendor_id' => $purchase->vendor_id];
        }

        // Cr Hutang Usaha (net payable)
        $lines[] = ['account_code' => $ap, 'debit' => 0, 'credit' => $netPayable, 'desc' => 'Hutang pembelian part '.$purchase->purchase_number, 'vendor_id' => $purchase->vendor_id];

        return $this->posting->postGeneral([
            'journal_date' => $purchase->purchase_date->toDateString(),
            'source_type' => 'part_purchase',
            'source_id' => $purchase->id,
            'memo' => 'Pembelian part '.$purchase->purchase_number,
        ], $lines);
    }

    public function postPartUsage(PartUsage $usage): Journal
    {
        if ($j = $this->alreadyPosted('part_usage', $usage->id)) {
            return $j;
        }

        // Ensure part is loaded
        if (! $usage->relationLoaded('part')) {
            $usage->load('part');
        }

        try {
            $inventory = $this->map('inventory');
        } catch (\InvalidArgumentException $e) {
            $inventory = $this->map('expense_vendor');
        }
        try {
            $expense = $this->map('expense_maintenance');
        } catch (\InvalidArgumentException $e) {
            $expense = $this->map('expense_other');
        }
        $total = (float) $usage->total_cost;

        $lines = [
            ['account_code' => $expense, 'debit' => $total, 'credit' => 0, 'desc' => 'Pemakaian part '.$usage->part->code.' - '.$usage->usage_type, 'truck_id' => $usage->truck_id],
            ['account_code' => $inventory, 'debit' => 0, 'credit' => $total, 'desc' => 'Pengeluaran stok part '.$usage->part->code, 'truck_id' => $usage->truck_id],
        ];

        return $this->posting->postGeneral([
            'journal_date' => $usage->usage_date->toDateString(),
            'source_type' => 'part_usage',
            'source_id' => $usage->id,
            'memo' => 'Pemakaian part '.$usage->usage_number,
        ], $lines);
    }

    /**
     * Unpost invoice (delete journal) if period is open.
     */
    public function unpostInvoice(Invoice $invoice): bool
    {
        $journal = Journal::where('source_type', 'invoice')
            ->where('source_id', $invoice->id)
            ->first();

        if (! $journal) {
            return true; // Already unposted
        }

        // Check period status
        if ($journal->period && $journal->period->status !== 'open') {
            throw new \Exception("Tidak dapat melakukan unpost karena periode akuntansi {$journal->period->month}/{$journal->period->year} sudah ditutup (Status: {$journal->period->status}).");
        }

        // Delete journal lines first (if cascade not set)
        $journal->lines()->delete();
        
        // Delete journal
        $journal->delete();

        // Clear journal_id from invoice
        $invoice->update(['journal_id' => null]);

        return true;
    }

    protected function reversePrepaidsForInvoice(Invoice $invoice): void
    {
        // Ambil shipment_leg_ids dari invoice items
        $shipmentLegIds = $invoice->items()
            ->whereNotNull('shipment_leg_id')
            ->pluck('shipment_leg_id')
            ->unique();
        
        if ($shipmentLegIds->isEmpty()) {
            // Jika tidak ada shipment_leg_id, coba via job_order_id
            $jobOrderIds = $invoice->items()
                ->whereNotNull('job_order_id')
                ->pluck('job_order_id')
                ->unique();
            
            if ($jobOrderIds->isEmpty()) {
                return;
            }
            
            // Ambil shipment legs via job orders
            $shipmentLegIds = \App\Models\Operations\ShipmentLeg::whereIn('job_order_id', $jobOrderIds)
                ->pluck('id');
            
            if ($shipmentLegIds->isEmpty()) {
                return;
            }
        }

        // Ambil vendor bills yang terkait dengan shipment legs ini
        $vendorBills = VendorBill::whereHas('items', function($q) use ($shipmentLegIds) {
            $q->whereIn('shipment_leg_id', $shipmentLegIds);
        })->with('items')->get();

        if ($vendorBills->isEmpty()) {
            return;
        }

        foreach ($vendorBills as $vendorBill) {
            // Cek apakah sudah ada jurnal untuk vendor bill ini
            $vendorBillJournal = Journal::where('source_type', 'vendor_bill')
                ->where('source_id', $vendorBill->id)
                ->first();

            if (! $vendorBillJournal) {
                continue;
            }

            // Cek apakah jurnal tersebut menggunakan biaya dimuka (1500)
            $prepaidCode = $this->map('prepaid_expense');
            $hasPrepaid = $vendorBillJournal->lines()
                ->whereHas('account', function ($q) use ($prepaidCode) {
                    $q->where('code', $prepaidCode);
                })
                ->exists();

            if (! $hasPrepaid) {
                continue;
            }

            // Cek apakah jurnal pembalik sudah dibuat
            $reverseKey = 'prepaid_reverse_vb'.$vendorBill->id.'_inv'.$invoice->id;
            if ($this->alreadyPosted($reverseKey, 0)) {
                continue;
            }

            // Hitung DPP dari vendor bill (total - PPN - PPh23)
            $dpp = 0;
            foreach ($vendorBill->items as $item) {
                $desc = strtolower($item->description);
                if (!str_contains($desc, 'ppn') && !str_contains($desc, 'pph')) {
                    $dpp += $item->subtotal;
                }
            }

            if ($dpp <= 0) {
                continue;
            }

            // Buat jurnal pembalik: Dr Beban Vendor, Cr Biaya Dimuka
            $expense = $this->map('expense_vendor');

            $lines = [
                ['account_code' => $expense, 'debit' => $dpp, 'credit' => 0, 'desc' => 'Pengakuan beban vendor bill '.$vendorBill->vendor_bill_number.' untuk invoice '.$invoice->invoice_number, 'vendor_id' => $vendorBill->vendor_id],
                ['account_code' => $prepaidCode, 'debit' => 0, 'credit' => $dpp, 'desc' => 'Pembalikan biaya dimuka vendor bill '.$vendorBill->vendor_bill_number, 'vendor_id' => $vendorBill->vendor_id],
            ];

            try {
                $this->posting->postGeneral([
                    'journal_date' => $invoice->invoice_date->toDateString(),
                    'source_type' => $reverseKey,
                    'source_id' => 0,
                    'memo' => 'Pembalikan biaya dimuka untuk invoice '.$invoice->invoice_number,
                ], $lines);
                
                \Log::info('Reversed prepaid expense for invoice', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'vendor_bill_id' => $vendorBill->id,
                    'vendor_bill_number' => $vendorBill->vendor_bill_number,
                    'amount' => $dpp
                ]);
            } catch (\Exception $e) {
                // Log error but don't fail the transaction
                \Log::warning('Failed to reverse prepaid for invoice '.$invoice->invoice_number.': '.$e->getMessage());
            }
        }
    }
}
