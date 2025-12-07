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
                <div>Customer: {{ optional($trx->customer)->name ?: '-' }}</div>
                <div>Vendor: {{ optional($trx->vendor)->name ?: '-' }}</div>
                <div>COA: {{ optional($trx->accountCoa)->code }} {{ optional($trx->accountCoa)->name }}</div>
            </div>
        </x-card>
    </div>
@endsection

