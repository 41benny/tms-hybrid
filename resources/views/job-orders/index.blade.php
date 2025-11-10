@extends('layouts.app', ['title' => 'Job Orders'])

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold">Job Orders</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Daftar pesanan customer</p>
        </div>
        <a href="{{ route('job-orders.create') }}" class="px-3 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-500">+ Job Order</a>
    </div>

    <x-card>
        <form method="get" class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <select name="status" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
                <option value="">Status</option>
                @foreach(['draft','confirmed','in_progress','completed','cancelled'] as $st)
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
            <button class="px-3 py-2 rounded bg-slate-200 dark:bg-slate-800">Filter</button>
        </form>
    </x-card>

    <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/80">
        <table class="min-w-full text-sm">
            <thead class="text-left border-b border-slate-200 dark:border-slate-800">
                <tr class="text-slate-500">
                    <th class="px-4 py-2">No</th>
                    <th class="px-4 py-2">Customer</th>
                    <th class="px-4 py-2">Service</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Tanggal</th>
                    <th class="px-4 py-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @foreach($orders as $o)
                <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                    <td class="px-4 py-2 font-medium">{{ $o->job_number }}</td>
                    <td class="px-4 py-2">{{ $o->customer->name ?? '-' }}</td>
                    <td class="px-4 py-2">{{ strtoupper(str_replace('_',' ', $o->service_type)) }}</td>
                    <td class="px-4 py-2"><x-badge>{{ ucfirst(str_replace('_',' ', $o->status)) }}</x-badge></td>
                    <td class="px-4 py-2">{{ $o->order_date->format('d M Y') }}</td>
                    <td class="px-4 py-2 flex gap-3">
                        <a class="underline" href="{{ route('job-orders.show',$o) }}" title="Lihat">👁️</a>
                        <a class="underline" href="{{ route('job-orders.edit',$o) }}" title="Edit">✎</a>
                        <a class="underline" href="{{ route('transports.create', ['job_order_id'=>$o->id]) }}" title="Buat Transport">🚚</a>
                        <a class="underline" href="{{ route('invoices.create', ['customer_id'=>$o->customer_id]) }}" title="Buat Invoice">🧾</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
@endsection
