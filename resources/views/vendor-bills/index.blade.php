@extends('layouts.app', ['title' => 'Vendor Bills'])

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold">Vendor Bills</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Tagihan dari vendor</p>
        </div>
        <a href="{{ route('vendor-bills.create') }}" class="px-3 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-500">+ Vendor Bill</a>
    </div>

    <x-card>
        <form method="get" class="grid grid-cols-1 md:grid-cols-6 gap-3">
            <select name="status" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
                <option value="">Status</option>
                @foreach(['draft','received','partially_paid','paid','cancelled'] as $st)
                    <option value="{{ $st }}" @selected(request('status')===$st)>{{ ucfirst(str_replace('_',' ', $st)) }}</option>
                @endforeach
            </select>
            <select name="vendor_id" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
                <option value="">Vendor</option>
                @foreach($vendors as $v)
                    <option value="{{ $v->id }}" @selected(request('vendor_id')==$v->id)>{{ $v->name }}</option>
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
                    <th class="px-4 py-2">Vendor</th>
                    <th class="px-4 py-2">Tanggal</th>
                    <th class="px-4 py-2">Jatuh Tempo</th>
                    <th class="px-4 py-2">Total</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @foreach($bills as $b)
                <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                    <td class="px-4 py-2 font-medium">{{ $b->vendor_bill_number }}</td>
                    <td class="px-4 py-2">{{ $b->vendor->name ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $b->bill_date->format('d M Y') }}</td>
                    <td class="px-4 py-2">{{ optional($b->due_date)->format('d M Y') }}</td>
                    <td class="px-4 py-2">{{ number_format($b->total_amount, 2, ',', '.') }}</td>
                    <td class="px-4 py-2"><x-badge>{{ ucfirst(str_replace('_',' ', $b->status)) }}</x-badge></td>
                    <td class="px-4 py-2 flex gap-3">
                        <a class="underline" href="{{ route('vendor-bills.show',$b) }}" title="Lihat">👁️</a>
                        <a class="underline" href="{{ route('vendor-bills.edit',$b) }}" title="Edit">✎</a>
                        <a class="underline text-rose-600" href="{{ route('cash-banks.create', ['sumber'=>'vendor_payment','vendor_bill_id'=>$b->id,'amount'=>$b->total_amount]) }}" title="Bayar Vendor">💸</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $bills->links() }}</div>
@endsection
