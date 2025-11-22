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

        $ar = $this->map('ar');
        $revenue = $this->map('revenue');
        $vatOut = $this->map('vat_out');

        $lines = [];

        // Dr. Piutang Usaha (total yang akan diterima dari customer)
        $lines[] = ['account_code' => $ar, 'debit' => $totalReceivable, 'credit' => 0, 'desc' => 'Piutang Invoice '.$invoice->invoice_number, 'customer_id' => $invoice->customer_id];

        // Cr. Pendapatan (DPP)
        $lines[] = ['account_code' => $revenue, 'debit' => 0, 'credit' => $dpp, 'desc' => 'Pendapatan Invoice '.$invoice->invoice_number, 'customer_id' => $invoice->customer_id];

        // Cr. PPN Keluaran (jika ada PPN)
        if ($ppn > 0) {
            $lines[] = ['account_code' => $vatOut, 'debit' => 0, 'credit' => $ppn, 'desc' => 'PPN Keluaran Invoice '.$invoice->invoice_number, 'customer_id' => $invoice->customer_id];
        }

        \Log::info('Invoice Journal Breakdown', [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
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

        // Balik jurnal biaya dimuka untuk shipment legs yang terkait invoice ini
        $this->reversePrepaidsForInvoice($invoice);

        return $journal;
    }

    public function postCustomerPayment(CashBankTransaction $trx): Journal
    {
        if ($j = $this->alreadyPosted('customer_payment', $trx->id)) {
            return $j;
        }
        $cash = $this->map('cash');
        $ar = $this->map('ar');
        $cashReceived = (float) $trx->amount;
        $withholding = (float) ($trx->withholding_pph23 ?? 0);
        $totalApplied = $cashReceived + $withholding;

        $lines = [
            ['account_code' => $cash, 'debit' => $cashReceived, 'credit' => 0, 'desc' => 'Penerimaan invoice '.$trx->invoice?->invoice_number, 'customer_id' => $trx->customer_id],
        ];

        if ($withholding > 0) {
            $pph23Claim = $this->map('pph23_claim');
            $lines[] = ['account_code' => $pph23Claim, 'debit' => $withholding, 'credit' => 0, 'desc' => 'Piutang PPh 23 invoice '.$trx->invoice?->invoice_number, 'customer_id' => $trx->customer_id];
        }

        $lines[] = ['account_code' => $ar, 'debit' => 0, 'credit' => $totalApplied, 'desc' => 'Pelunasan invoice '.$trx->invoice?->invoice_number, 'customer_id' => $trx->customer_id];

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
        $bill->load('items');

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

        if ($hasShipmentLeg) {
            // Untuk vendor bill shipment leg: Dr Biaya Dimuka
            $prepaid = $this->map('prepaid_expense');
            $lines[] = ['account_code' => $prepaid, 'debit' => $dpp, 'credit' => 0, 'desc' => 'Biaya dimuka vendor bill '.$bill->vendor_bill_number, 'vendor_id' => $bill->vendor_id];
        } else {
            // Untuk vendor bill non-shipment: Dr Beban Vendor (langsung expense)
            $exp = $this->map('expense_vendor');
            $lines[] = ['account_code' => $exp, 'debit' => $dpp, 'credit' => 0, 'desc' => 'Biaya vendor bill '.$bill->vendor_bill_number, 'vendor_id' => $bill->vendor_id];
        }

        // Dr PPN Masukan (jika ada PPN)
        if ($ppn > 0) {
            $vatIn = $this->map('vat_in');
            $lines[] = ['account_code' => $vatIn, 'debit' => $ppn, 'credit' => 0, 'desc' => 'PPN Masukan vendor bill '.$bill->vendor_bill_number, 'vendor_id' => $bill->vendor_id];
        }

        // Cr Hutang PPh 23 (jika ada potongan PPh 23)
        if ($pph23 > 0) {
            $pph23Payable = $this->map('pph23');
            $lines[] = ['account_code' => $pph23Payable, 'debit' => 0, 'credit' => $pph23, 'desc' => 'PPh 23 dipotong vendor bill '.$bill->vendor_bill_number, 'vendor_id' => $bill->vendor_id];
        }

        // Cr Hutang Usaha (net payable)
        $lines[] = ['account_code' => $ap, 'debit' => 0, 'credit' => $netPayable, 'desc' => 'Hutang vendor bill '.$bill->vendor_bill_number, 'vendor_id' => $bill->vendor_id];

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

        return $this->posting->postGeneral([
            'journal_date' => $bill->bill_date->toDateString(),
            'source_type' => 'vendor_bill',
            'source_id' => $bill->id,
            'memo' => 'Vendor bill '.$bill->vendor_bill_number,
        ], $lines);
    }

    public function postVendorPayment(CashBankTransaction $trx): Journal
    {
        if ($j = $this->alreadyPosted('vendor_payment', $trx->id)) {
            return $j;
        }
        $ap = $this->map('ap');
        $cash = $this->map('cash');
        $amt = (float) $trx->amount;
        $lines = [
            ['account_code' => $ap, 'debit' => $amt, 'credit' => 0, 'desc' => 'Pelunasan hutang vendor', 'vendor_id' => $trx->vendor_id],
            ['account_code' => $cash, 'debit' => 0, 'credit' => $amt, 'desc' => 'Pembayaran hutang vendor', 'vendor_id' => $trx->vendor_id],
        ];

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
        $cash = $this->map('cash');
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

        return true;
    }

    protected function reversePrepaidsForInvoice(Invoice $invoice): void
    {
        // Ambil transport IDs dari items
        $transportIds = $invoice->items()->whereNotNull('transport_id')->pluck('transport_id')->unique();
        
        if ($transportIds->isEmpty()) {
            return;
        }

        $transports = Transport::with('shipmentLegs.vendorBill')->whereIn('id', $transportIds)->get();

        foreach ($transports as $transport) {

        // Ambil semua shipment legs yang terkait transport ini
        $legs = $transport->shipmentLegs ?? collect();

        foreach ($legs as $leg) {
            // Cek apakah leg ini punya vendor bill
            $vendorBill = $leg->vendorBill;
            if (! $vendorBill) {
                continue;
            }

            // Cek apakah sudah ada jurnal untuk vendor bill ini
            $vendorBillJournal = Journal::where('source_type', 'vendor_bill')
                ->where('source_id', $vendorBill->id)
                ->first();

            if (! $vendorBillJournal) {
                continue;
            }

            // Cek apakah jurnal tersebut menggunakan biaya dimuka (1400)
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

            // Buat jurnal pembalik: Dr Beban Vendor, Cr Biaya Dimuka
            $expense = $this->map('expense_vendor');
            $amt = (float) $vendorBill->total_amount;

            $lines = [
                ['account_code' => $expense, 'debit' => $amt, 'credit' => 0, 'desc' => 'Pengakuan beban vendor bill '.$vendorBill->vendor_bill_number.' untuk invoice '.$invoice->invoice_number, 'vendor_id' => $vendorBill->vendor_id],
                ['account_code' => $prepaidCode, 'debit' => 0, 'credit' => $amt, 'desc' => 'Pembalikan biaya dimuka vendor bill '.$vendorBill->vendor_bill_number, 'vendor_id' => $vendorBill->vendor_id],
            ];

            try {
                $this->posting->postGeneral([
                    'journal_date' => $invoice->invoice_date->toDateString(),
                    'source_type' => $reverseKey,
                    'source_id' => 0,
                    'memo' => 'Pembalikan biaya dimuka untuk invoice '.$invoice->invoice_number,
                ], $lines);
            } catch (\Exception $e) {
                // Log error but don't fail the transaction
                \Log::warning('Failed to reverse prepaid for invoice '.$invoice->invoice_number.': '.$e->getMessage());
            }
        }
        }
    }
}
