@extends('layouts.app', ['title' => 'Detail Transaksi Kas/Bank'])

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <div>
            <div class="text-xl font-semibold">Transaksi #{{ $trx->id }}</div>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $trx->tanggal->format('d M Y') }} • {{ $trx->account->name ?? '-' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-card title="Ringkasan">
            <div class="space-y-1 text-sm">
                <div>Jenis: {{ $trx->jenis }}</div>
                <div>Sumber: {{ str_replace('_',' ', $trx->sumber) }}</div>
                <div>Nominal: <b>{{ number_format($trx->amount, 2, ',', '.') }}</b></div>
                <div>Potongan PPh 23: <b>{{ number_format($trx->withholding_pph23 ?? 0, 2, ',', '.') }}</b></div>
                <div>Ref: {{ $trx->reference_number ?: '-' }}</div>
                <div>Deskripsi: {{ $trx->description ?: '-' }}</div>
            </div>
        </x-card>
        <x-card title="Relasi">
            <div class="space-y-1 text-sm">
                @php
                    // Get invoices from many-to-many relationship
                    $invoices = $trx->invoicePayments()->with('invoice')->get()->pluck('invoice')->filter();
                    // Fallback to direct invoice relationship if exists
                    if ($invoices->isEmpty() && $trx->invoice) {
                        $invoices = collect([$trx->invoice]);
                    }

                    // Get Job Orders from various sources
                    $jobOrders = collect();
                    
                    // From invoices
                    foreach($invoices as $invoice) {
                        foreach($invoice->items as $item) {
                            if($item->job_order_id) {
                                $jobOrders->push($item->jobOrder);
                            }
                        }
                    }
                    
                    // From vendor bill
                    if($trx->vendorBill) {
                        foreach($trx->vendorBill->vendorBillItems as $item) {
                            if($item->shipmentLeg && $item->shipmentLeg->jobOrder) {
                                $jobOrders->push($item->shipmentLeg->jobOrder);
                            }
                        }
                    }
                    
                    // From driver advance payments
                    foreach($trx->driverAdvancePayments as $payment) {
                        if($payment->driverAdvance && $payment->driverAdvance->shipmentLeg && $payment->driverAdvance->shipmentLeg->jobOrder) {
                            $jobOrders->push($payment->driverAdvance->shipmentLeg->jobOrder);
                        }
                    }
                    
                    $jobOrders = $jobOrders->unique('id');
                @endphp
                
                <div>Invoice: 
                    @if($invoices->isNotEmpty())
                        @foreach($invoices as $invoice)
                            <a href="{{ route('invoices.show', $invoice) }}" class="text-blue-600 hover:underline">
                                {{ $invoice->invoice_number }}
                            </a>@if(!$loop->last), @endif
                        @endforeach
                    @else
                        -
                    @endif
                </div>
                
                <div>Vendor Bill: {{ optional($trx->vendorBill)->vendor_bill_number ?: '-' }}</div>
                
                <div>Job Order:
                    @if($jobOrders->isNotEmpty())
                        @foreach($jobOrders as $jo)
                            <a href="{{ route('job-orders.show', $jo) }}" class="text-blue-600 hover:underline">
                                {{ $jo->job_number }}
                            </a>@if(!$loop->last), @endif
                        @endforeach
                    @else
                        -
                    @endif
                </div>
                
                <div>Customer: {{ optional($trx->customer)->name ?: '-' }}</div>
                <div>Vendor: {{ optional($trx->vendor)->name ?: '-' }}</div>
                <div>COA: {{ optional($trx->accountCoa)->code }} {{ optional($trx->accountCoa)->name }}</div>
            </div>
        </x-card>
    </div>
@endsection

