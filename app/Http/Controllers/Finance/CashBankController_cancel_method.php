
    /**
     * Cancel/Void transaction with full rollback
     */
    public function cancel(CashBankTransaction $cashBankTransaction)
    {
        try {
            \DB::beginTransaction();
            
            // 1. Delete journal entries
            if (class_exists('App\Models\Accounting\Journal')) {
                $sourceType = $this->mapSourceTypeForJournal($cashBankTransaction->sumber);
                \App\Models\Accounting\Journal::where('source_type', $sourceType)
                    ->where('source_id', $cashBankTransaction->id)
                    ->delete();
            }
            
            // 2. Get all payment records
            $payments = \App\Models\Finance\VendorBillPayment::where('cash_bank_transaction_id', $cashBankTransaction->id)->get();
            
            // 3. Rollback vendor bill status and amount_paid
            foreach ($payments as $payment) {
                $vendorBill = $payment->vendorBill;
                if ($vendorBill) {
                    $newAmountPaid = $vendorBill->amount_paid - $payment->amount_paid;
                    
                    // Determine new status
                    $newStatus = 'pending';
                    if ($newAmountPaid > 0) {
                        $newStatus = 'partially_paid';
                    }
                    
                    $vendorBill->update([
                        'amount_paid' => max(0, $newAmountPaid),
                        'status' => $newStatus
                    ]);
                }
            }
            
            // 4. Delete payment records
            \App\Models\Finance\VendorBillPayment::where('cash_bank_transaction_id', $cashBankTransaction->id)->delete();
            
            // 5. Rollback invoice status if customer payment
            if ($cashBankTransaction->invoice_id) {
                $invoice = Invoice::find($cashBankTransaction->invoice_id);
                if ($invoice) {
                    // Recalculate total paid after removing this transaction
                    $totalPaid = CashBankTransaction::where('invoice_id', $invoice->id)
                        ->where('id', '!=', $cashBankTransaction->id)
                        ->selectRaw('SUM(amount + COALESCE(withholding_pph23, 0)) as total')
                        ->value('total') ?? 0;
                    
                    if ($totalPaid >= $invoice->total_amount) {
                        $invoice->update(['status' => 'paid']);
                    } elseif ($totalPaid > 0) {
                        $invoice->update(['status' => 'partial']);
                    } else {
                        $invoice->update(['status' => 'sent']);
                    }
                }
            }
            
            // 6. Delete the transaction
            $cashBankTransaction->delete();
            
            \DB::commit();
            
            return redirect()->route('cash-banks.index')->with('success', 'Transaksi berhasil dibatalkan dan di-rollback.');
            
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membatalkan transaksi: ' . $e->getMessage());
        }
    }
    
    /**
     * Map source type to journal source type
     */
    private function mapSourceTypeForJournal($sumber)
    {
        $map = [
            'customer_payment' => 'customer_payment',
            'vendor_payment' => 'vendor_payment',
            'expense' => 'expense',
            'other_in' => 'other_in',
            'other_out' => 'other_out',
        ];
        
        return $map[$sumber] ?? $sumber;
    }
