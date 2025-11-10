<?php

namespace App\Services\Accounting;

use App\Models\Accounting\Journal;
use App\Models\Finance\CashBankTransaction;
use App\Models\Finance\Invoice;
use App\Models\Finance\VendorBill;
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
        $total = (float) $invoice->total_amount;
        if ($total <= 0) {
            throw new InvalidArgumentException('Total invoice 0');
        }

        $ar = $this->map('ar');
        $revenue = $this->map('revenue');

        $lines = [
            ['account_code' => $ar, 'debit' => $total, 'credit' => 0, 'desc' => 'Piutang Invoice '.$invoice->invoice_number, 'customer_id' => $invoice->customer_id],
            ['account_code' => $revenue, 'debit' => 0, 'credit' => $total, 'desc' => 'Pendapatan Invoice '.$invoice->invoice_number, 'customer_id' => $invoice->customer_id],
        ];

        return $this->posting->postGeneral([
            'journal_date' => $invoice->invoice_date->toDateString(),
            'source_type' => 'invoice',
            'source_id' => $invoice->id,
            'memo' => 'Invoice '.$invoice->invoice_number,
        ], $lines);
    }

    public function postCustomerPayment(CashBankTransaction $trx): Journal
    {
        if ($j = $this->alreadyPosted('customer_payment', $trx->id)) {
            return $j;
        }
        $cash = $this->map('cash');
        $ar = $this->map('ar');
        $amt = (float) $trx->amount;
        $lines = [
            ['account_code' => $cash, 'debit' => $amt, 'credit' => 0, 'desc' => 'Penerimaan invoice '.$trx->invoice?->invoice_number, 'customer_id' => $trx->customer_id],
            ['account_code' => $ar, 'debit' => 0, 'credit' => $amt, 'desc' => 'Pelunasan invoice '.$trx->invoice?->invoice_number, 'customer_id' => $trx->customer_id],
        ];

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
        $ap = $this->map('ap');
        $exp = $this->map('expense_vendor');
        $amt = (float) $bill->total_amount;
        $lines = [
            ['account_code' => $exp, 'debit' => $amt, 'credit' => 0, 'desc' => 'Biaya vendor bill '.$bill->vendor_bill_number, 'vendor_id' => $bill->vendor_id],
            ['account_code' => $ap, 'debit' => 0, 'credit' => $amt, 'desc' => 'Hutang vendor bill '.$bill->vendor_bill_number, 'vendor_id' => $bill->vendor_id],
        ];

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
}
