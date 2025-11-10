@extends('layouts.app', ['title' => 'Detail Vendor Bill'])

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold">{{ $bill->vendor_bill_number }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $bill->vendor->name ?? '-' }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('cash-banks.create', ['sumber'=>'vendor_payment','vendor_bill_id'=>$bill->id,'amount'=>$bill->total_amount]) }}" class="px-3 py-2 rounded bg-rose-600 text-white">Bayar Vendor</a>
            <form method="post" action="{{ route('vendor-bills.mark-received', $bill) }}">@csrf<button class="px-3 py-2 rounded bg-slate-200 dark:bg-slate-800">Mark Received</button></form>
            <form method="post" action="{{ route('vendor-bills.mark-paid', $bill) }}" class="ml-2">@csrf<button class="px-3 py-2 rounded bg-slate-200 dark:bg-slate-800">Mark Paid</button></form>
            <a href="{{ route('vendor-bills.edit', $bill) }}" class="px-3 py-2 rounded bg-slate-200 dark:bg-slate-800">Edit</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-card title="Ringkasan">
            <div class="space-y-1 text-sm">
                <div>Tanggal: {{ $bill->bill_date->format('d M Y') }}</div>
                <div>Jatuh Tempo: {{ optional($bill->due_date)->format('d M Y') ?: '-' }}</div>
                <div>Status: <x-badge>{{ ucfirst(str_replace('_',' ', $bill->status)) }}</x-badge></div>
                <div>Total: <b>{{ number_format($bill->total_amount, 2, ',', '.') }}</b></div>
                <div>Catatan: {{ $bill->notes ?: '-' }}</div>
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
                        @foreach($bill->items as $it)
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
