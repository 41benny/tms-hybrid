@extends('layouts.app', ['title' => 'Invoices'])

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <div>
            <div class="text-xl font-semibold">Invoices</div>
            <p class="text-sm text-slate-500 dark:text-slate-400">Tagihan ke customer</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('tax-invoices.create') }}" class="px-3 py-2 rounded bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Request Faktur Pajak
            </a>
            <a href="{{ route('invoices.create') }}" class="px-3 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-500">+ Invoice</a>
        </div>
    </div>

    <x-card>
        <form method="get" class="grid grid-cols-1 md:grid-cols-6 gap-3">
            <select name="status" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
                <option value="">Status</option>
                @foreach(['draft','pending','sent','partial','paid','overdue','cancelled'] as $st)
                    <option value="{{ $st }}" @selected(request('status')===$st)>{{ ucfirst(str_replace('_',' ', $st)) }}</option>
                @endforeach
            </select>
            <select name="customer_id" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
                <option value="">Customer</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}" @selected(request('customer_id')==$c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ request('from') }}" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" />
            <input type="date" name="to" value="{{ request('to') }}" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" />
            <div></div>
            <button class="px-3 py-2 rounded bg-slate-200 dark:bg-slate-800">Filter</button>
        </form>
    </x-card>

    <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/80">
        <table class="min-w-full text-sm">
            <thead class="text-left border-b border-slate-200 dark:border-slate-800">
                <tr class="text-slate-500">
                    <th class="px-4 py-2">Nomor</th>
                    <th class="px-4 py-2">Customer</th>
                    <th class="px-4 py-2">Tanggal</th>
                    <th class="px-4 py-2">Jatuh Tempo</th>
                    <th class="px-4 py-2">Total</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Faktur Pajak</th>
                    <th class="px-4 py-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @foreach($invoices as $inv)
                <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                    <td class="px-4 py-2 font-medium">{{ $inv->invoice_number }}</td>
                    <td class="px-4 py-2">{{ $inv->customer->name ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $inv->invoice_date->format('d M Y') }}</td>
                    <td class="px-4 py-2">{{ optional($inv->due_date)->format('d M Y') }}</td>
                    <td class="px-4 py-2">{{ number_format($inv->total_amount, 2, ',', '.') }}</td>
                    <td class="px-4 py-2"><x-badge>{{ ucfirst(str_replace('_',' ', $inv->status)) }}</x-badge></td>
                    <td class="px-4 py-2">
                        @if($inv->tax_invoice_status === 'none')
                            <span class="text-slate-400">-</span>
                        @elseif($inv->tax_invoice_status === 'requested')
                            <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full">Requested</span>
                        @elseif($inv->tax_invoice_status === 'completed')
                            <div class="text-xs">
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full">Completed</span>
                                <div class="mt-1 font-mono text-[10px]">{{ $inv->tax_invoice_number }}</div>
                            </div>
                        @endif
                    </td>
                    <td class="px-4 py-2 flex gap-3">
                        <a class="underline" href="{{ route('invoices.show',$inv) }}" title="Lihat">👁️</a>
                        <a class="underline" href="{{ route('invoices.edit',$inv) }}" title="Edit">✎</a>
                        <a class="underline text-emerald-600" href="{{ route('cash-banks.create', ['sumber'=>'customer_payment','invoice_id'=>$inv->id,'amount'=>$inv->total_amount]) }}" title="Terima Pembayaran">💰</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $invoices->links() }}</div>
@endsection
