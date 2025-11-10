@extends('layouts.app', ['title' => 'Detail Invoice'])

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold">{{ $invoice->invoice_number }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $invoice->customer->name ?? '-' }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('cash-banks.create', ['sumber'=>'customer_payment','invoice_id'=>$invoice->id,'amount'=>$invoice->total_amount]) }}" class="px-3 py-2 rounded bg-emerald-600 text-white">Terima Pembayaran</a>
            <form method="post" action="{{ route('invoices.mark-sent', $invoice) }}">@csrf<button class="px-3 py-2 rounded bg-slate-200 dark:bg-slate-800">Mark Sent</button></form>
            <form method="post" action="{{ route('invoices.mark-paid', $invoice) }}" class="ml-2">@csrf<button class="px-3 py-2 rounded bg-slate-200 dark:bg-slate-800">Mark Paid</button></form>
            <a href="{{ route('invoices.edit', $invoice) }}" class="px-3 py-2 rounded bg-slate-200 dark:bg-slate-800">Edit</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-card title="Ringkasan">
            <div class="space-y-1 text-sm">
                <div>Tanggal: {{ $invoice->invoice_date->format('d M Y') }}</div>
                <div>Jatuh Tempo: {{ optional($invoice->due_date)->format('d M Y') ?: '-' }}</div>
                <div>Status: <x-badge>{{ ucfirst(str_replace('_',' ', $invoice->status)) }}</x-badge></div>
                <div>Total: <b>{{ number_format($invoice->total_amount, 2, ',', '.') }}</b></div>
                <div>Catatan: {{ $invoice->notes ?: '-' }}</div>
            </div>
        </x-card>
        <x-card title="Items">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-slate-500">
                            <th class="px-2 py-1">Deskripsi</th>
                            <th class="px-2 py-1">Qty</th>
                            <th class="px-2 py-1">Harga</th>
                            <th class="px-2 py-1">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $it)
                            <tr class="border-t border-slate-200 dark:border-slate-800">
                                <td class="px-2 py-1">{{ $it->description }}</td>
                                <td class="px-2 py-1">{{ $it->qty }}</td>
                                <td class="px-2 py-1">{{ number_format($it->unit_price, 2, ',', '.') }}</td>
                                <td class="px-2 py-1">{{ number_format($it->subtotal, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
@endsection
