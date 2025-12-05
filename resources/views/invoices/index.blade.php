@extends('layouts.app', ['title' => 'Invoices'])

@section('content')
    @php
        $user = auth()->user();
        $canCreateInvoice = $user?->hasPermission('invoices.create');
        $canUpdateInvoice = $user?->hasPermission('invoices.update');
        $canManageInvoiceStatus = $user?->hasPermission('invoices.manage_status');
    @endphp
    <x-card>
        <x-slot:header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-end gap-4">
                <div class="flex gap-2 invoice-header-actions">
                    <a href="{{ route('tax-invoices.create') }}" class="px-3 py-2 rounded-lg bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-300 dark:border-slate-600 flex items-center gap-2 text-xs font-medium shadow-sm transition-all btn-tax-invoice">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Request Faktur Pajak
                    </a>
                    @if($canCreateInvoice)
                        <a href="{{ route('invoices.create') }}" class="px-3 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 flex items-center gap-2 text-xs font-bold uppercase tracking-wider shadow-lg shadow-indigo-500/30 transition-all btn-create-invoice">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Buat Invoice
                        </a>
                    @endif
                </div>
            </div>
        </x-slot:header>
    </x-card>

    <x-card class="mt-6">
        <form method="get" class="flex flex-wrap gap-2 items-center">
            <select name="status" class="rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-3 py-2 text-xs text-slate-900 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                <option value="">Status</option>
                @foreach(['draft','pending','sent','partial','paid','overdue','cancelled'] as $st)
                    <option value="{{ $st }}" @selected(request('status')===$st)>{{ ucfirst(str_replace('_',' ', $st)) }}</option>
                @endforeach
            </select>
            <select name="customer_id" class="rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-3 py-2 text-xs text-slate-900 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                <option value="">Customer</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}" @selected(request('customer_id')==$c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ request('from') }}" class="rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-3 py-2 text-xs text-slate-900 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm" />
            <input type="date" name="to" value="{{ request('to') }}" class="rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-3 py-2 text-xs text-slate-900 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm" />
            <x-button type="submit" variant="primary" size="sm">
                Filter
            </x-button>
            @if(request()->hasAny(['status', 'customer_id', 'from', 'to']))
                <a href="{{ route('invoices.index') }}" class="px-3 py-2 rounded-lg bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-slate-700 text-xs font-medium shadow-sm transition-all">Reset</a>
            @endif
        </form>
    </x-card>

    <x-card :noPadding="true" class="mt-6">
        <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="text-left border-b border-slate-200 dark:border-slate-800">
                <tr class="text-slate-500">
                    <th class="px-4 py-2">Type</th>
                    <th class="px-4 py-2">Nomor</th>
                    <th class="px-4 py-2">Job Order</th>
                    <th class="px-4 py-2">Customer</th>
                    <th class="px-4 py-2">Tanggal</th>
                    <th class="px-4 py-2">Total</th>
                    <th class="px-4 py-2">Status Payment</th>
                    <th class="px-4 py-2">Status Approval</th>
                    <th class="px-4 py-2">Faktur Pajak</th>
                    <th class="px-4 py-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @foreach($invoices as $inv)
                <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                    <td class="px-4 py-2">
                        <x-badge :variant="$inv->transaction_type === 'cash' ? 'success' : 'info'">
                            {{ strtoupper($inv->transaction_type) }}
                        </x-badge>
                    </td>
                    <td class="px-4 py-2 font-medium">{{ $inv->invoice_number }}</td>
                    <td class="px-4 py-2">
                        @php
                            $jobNumbers = $inv->items->pluck('jobOrder.job_number')->filter()->unique()->values();
                        @endphp
                        @if($jobNumbers->isNotEmpty())
                            <div class="space-y-1">
                                @foreach($jobNumbers as $jn)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200 text-xs font-medium">{{ $jn }}</span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-slate-400 text-xs">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-2">{{ $inv->customer->name ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $inv->invoice_date->format('d M Y') }}</td>
                    <td class="px-4 py-2">{{ number_format($inv->total_amount, 2, ',', '.') }}</td>
                    <td class="px-4 py-2"><x-badge>{{ ucfirst(str_replace('_',' ', $inv->status)) }}</x-badge></td>
                    <td class="px-4 py-2">
                        <x-badge :variant="match($inv->approval_status) {
                            'draft' => 'secondary',
                            'pending_approval' => 'warning',
                            'approved' => 'success',
                            'rejected' => 'danger',
                            default => 'secondary'
                        }">{{ ucfirst(str_replace('_',' ', $inv->approval_status)) }}</x-badge>
                    </td>
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
                        <a class="text-slate-600 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-300 transition-colors" href="{{ route('invoices.pdf', $inv) }}" target="_blank" title="Preview PDF">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                        </a>
                        <a class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition-colors" href="{{ route('invoices.show',$inv) }}" title="Lihat Detail">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        </a>
                        @if($canUpdateInvoice && $inv->canBeEdited())
                            <a class="text-amber-600 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300 transition-colors" href="{{ route('invoices.edit',$inv) }}" title="Edit">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </a>
                        @endif
                        @if($canManageInvoiceStatus)
                            <a class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300 transition-colors" href="{{ route('cash-banks.create', ['sumber'=>'customer_payment','invoice_id'=>$inv->id,'amount'=>$inv->total_amount]) }}" title="Terima Pembayaran">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </a>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
    </x-card>

    <div class="mt-4">{{ $invoices->links() }}</div>
@endsection
