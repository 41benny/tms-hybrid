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

    <x-card :noPadding="true" class="mt-6">
        <form method="get" id="filter-form">
        <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="text-left border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50">
                {{-- Row 1: Column Headers --}}
                <tr class="text-slate-600 dark:text-slate-400 text-xs uppercase bg-slate-50 dark:bg-slate-800/50">
                    <th class="px-3 py-3 rounded-tl-xl" style="width: 80px">Type</th>
                    <th class="px-3 py-3" style="width: 140px">Nomor</th>
                    <th class="px-3 py-3" style="width: 150px">Job Order</th>
                    <th class="px-3 py-3" style="width: 180px">Customer</th>
                    <th class="px-3 py-3" style="width: 110px">Tanggal</th>
                    <th class="px-3 py-3 text-right" style="width: 120px">Total</th>
                    <th class="px-3 py-3" style="width: 110px">Status Payment</th>
                    <th class="px-3 py-3" style="width: 130px">Status Approval</th>
                    <th class="px-3 py-3" style="width: 130px">Faktur Pajak</th>
                    <th class="px-3 py-3 text-center rounded-tr-xl" style="width: 150px">Aksi</th>
                </tr>
                {{-- Row 2: Filters --}}
                <tr class="bg-slate-100 dark:bg-slate-900/50 text-xs">
                    <th class="px-1 py-1">
                        <select name="transaction_type" class="w-full px-1 py-1 text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500" onchange="this.form.submit()">
                            <option value="">All</option>
                            <option value="cash" @selected(request('transaction_type')=='cash')>Cash</option>
                            <option value="credit" @selected(request('transaction_type')=='credit')>Credit</option>
                        </select>
                    </th>
                    <th class="px-1 py-1">
                        <input type="text" name="invoice_number" value="{{ request('invoice_number') }}" placeholder="Cari..." class="w-full px-2 py-1 text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500" onkeypress="if(event.keyCode==13){this.form.submit()}">
                    </th>
                    <th class="px-1 py-1">
                        <input type="text" name="job_order" value="{{ request('job_order') }}" placeholder="Cari JO..." class="w-full px-2 py-1 text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500" onkeypress="if(event.keyCode==13){this.form.submit()}">
                    </th>
                    <th class="px-1 py-1">
                        <select name="customer_id" class="w-full px-1 py-1 text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500" onchange="this.form.submit()">
                            <option value="">All</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" @selected(request('customer_id')==$c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </th>
                    <th class="px-1 py-1">
                        <input type="date" name="date" value="{{ request('date') }}" class="w-full px-1 py-1 text-[10px] rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500" onchange="this.form.submit()">
                    </th>
                    <th class="px-1 py-1">
                        <input type="number" name="min_amount" value="{{ request('min_amount') }}" placeholder="Min..." class="w-full px-2 py-1 text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500" onkeypress="if(event.keyCode==13){this.form.submit()}">
                    </th>
                    <th class="px-1 py-1">
                        <select name="status" class="w-full px-1 py-1 text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500" onchange="this.form.submit()">
                            <option value="">All</option>
                            @foreach(['draft','pending','sent','partial','paid','overdue','cancelled'] as $st)
                                <option value="{{ $st }}" @selected(request('status')===$st)>{{ ucfirst(str_replace('_',' ', $st)) }}</option>
                            @endforeach
                        </select>
                    </th>
                    <th class="px-1 py-1">
                        <select name="approval_status" class="w-full px-1 py-1 text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500" onchange="this.form.submit()">
                            <option value="">All</option>
                            @foreach(['draft','pending_approval','approved','rejected'] as $as)
                                <option value="{{ $as }}" @selected(request('approval_status')===$as)>{{ ucfirst(str_replace('_',' ', $as)) }}</option>
                            @endforeach
                        </select>
                    </th>
                    <th class="px-1 py-1">
                        <select name="tax_invoice_status" class="w-full px-1 py-1 text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500" onchange="this.form.submit()">
                            <option value="">All</option>
                            @foreach(['none','requested','completed'] as $tis)
                                <option value="{{ $tis }}" @selected(request('tax_invoice_status')===$tis)>{{ ucfirst($tis) }}</option>
                            @endforeach
                        </select>
                    </th>
                    <th class="px-1 py-1 text-center">
                        <a href="{{ route('invoices.index') }}" class="text-xs text-red-600 hover:text-red-800" title="Reset Filter">x</a>
                    </th>
                </tr>
            </thead>
            <tbody>
            @foreach($invoices as $inv)
                <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                    <td class="px-3 py-3">
                        <x-badge :variant="$inv->transaction_type === 'cash' ? 'success' : 'info'">
                            {{ strtoupper($inv->transaction_type) }}
                        </x-badge>
                    </td>
                    <td class="px-3 py-3">
                        <div class="font-medium text-blue-600 dark:text-blue-400">{{ $inv->invoice_number }}</div>
                    </td>
                    <td class="px-3 py-3">
                        @php
                            $jobNumbers = $inv->items->pluck('jobOrder.job_number')->filter()->unique()->values();
                            $jobNumbersStr = $jobNumbers->join(', ');
                        @endphp
                        @if($jobNumbers->isNotEmpty())
                            <div class="truncate max-w-[130px]" title="{{ $jobNumbersStr }}">
                                {{ $jobNumbersStr }}
                            </div>
                        @else
                            <span class="text-slate-400 text-xs">-</span>
                        @endif
                    </td>
                    <td class="px-3 py-3">
                        <div class="truncate max-w-[160px]" title="{{ $inv->customer->name ?? '-' }}">
                            {{ $inv->customer->name ?? '-' }}
                        </div>
                    </td>
                    <td class="px-3 py-3 whitespace-nowrap">{{ $inv->invoice_date->format('d/m/Y') }}</td>
                    <td class="px-3 py-3 text-right font-medium">Rp {{ number_format($inv->total_amount, 0, ',', '.') }}</td>
                    <td class="px-3 py-3">
                        <x-badge>{{ ucfirst(str_replace('_',' ', $inv->status)) }}</x-badge>
                    </td>
                    <td class="px-3 py-3">
                        <x-badge :variant="match($inv->approval_status) {
                            'draft' => 'secondary',
                            'pending_approval' => 'warning',
                            'approved' => 'success',
                            'rejected' => 'danger',
                            default => 'secondary'
                        }">{{ ucfirst(str_replace('_',' ', $inv->approval_status)) }}</x-badge>
                    </td>
                    <td class="px-3 py-3">
                        @if($inv->tax_invoice_status === 'none')
                            <span class="text-slate-400">-</span>
                        @elseif($inv->tax_invoice_status === 'requested')
                            <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full">Requested</span>
                        @elseif($inv->tax_invoice_status === 'completed')
                            <div class="text-xs">
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full">Completed</span>
                                <div class="mt-1 font-mono text-[10px] truncate max-w-[110px]" title="{{ $inv->tax_invoice_number }}">{{ $inv->tax_invoice_number }}</div>
                            </div>
                        @endif
                    </td>
                    <td class="px-3 py-3">
                        <div class="flex items-center gap-2 justify-center">
                            <a class="inline-flex items-center px-2 py-1 text-xs text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 rounded" href="{{ route('invoices.pdf', $inv) }}" target="_blank" title="Preview PDF">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                            </a>
                            <a class="inline-flex items-center px-2 py-1 text-xs text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded" href="{{ route('invoices.show',$inv) }}" title="Lihat Detail">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                            </a>
                            @if($canUpdateInvoice && $inv->canBeEdited())
                                <a class="inline-flex items-center px-2 py-1 text-xs text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded" href="{{ route('invoices.edit',$inv) }}" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </a>
                            @endif
                            @if($canManageInvoiceStatus)
                                <a class="inline-flex items-center px-2 py-1 text-xs text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded" href="{{ route('cash-banks.create', ['sumber'=>'customer_payment','invoice_id'=>$inv->id,'amount'=>$inv->total_amount]) }}" title="Terima Pembayaran">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>
        </form>
    </x-card>

    <div class="mt-4">{{ $invoices->links() }}</div>

    <style>
        table {
            table-layout: fixed;
            width: 100%;
        }

        table td {
            white-space: normal;
        }

        /* Resizable columns */
        table th {
            position: relative;
            user-select: none;
            overflow: visible;
        }

        table th .resizer {
            position: absolute;
            top: 0;
            right: -3px;
            width: 6px;
            cursor: col-resize;
            user-select: none;
            height: 100%;
            z-index: 1;
        }

        table th .resizer:hover {
            background-color: rgba(99, 102, 241, 0.5);
        }

        table th .resizer.resizing {
            background-color: rgba(99, 102, 241, 0.7);
        }

        @media print {
            .no-print, button, form, nav, aside {
                display: none !important;
            }
            
            table {
                font-size: 10px;
            }
            
            thead {
                position: static !important;
            }
            
            .resizer {
                display: none !important;
            }
        }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            const table = document.querySelector('table');
            if (!table) return;
            
            // Only target the first row headers for resizing
            const cols = table.querySelectorAll('thead tr:first-child th');
            
            cols.forEach((col, index) => {
                // Set initial width
                if (!col.style.width) {
                    col.style.width = (col.offsetWidth + 20) + 'px'; // Add buffer
                }
                
                const resizer = document.createElement('div');
                resizer.classList.add('resizer');
                col.appendChild(resizer);
                
                let startX, startWidth;
                
                resizer.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    startX = e.pageX;
                    startWidth = col.offsetWidth;
                    
                    resizer.classList.add('resizing');
                    document.body.style.cursor = 'col-resize';
                    document.body.style.userSelect = 'none';
                    
                    const mouseMoveHandler = function(e) {
                        e.preventDefault();
                        const width = startWidth + (e.pageX - startX);
                        if (width > 50) { // minimum width
                            col.style.width = width + 'px';
                        }
                    };
                    
                    const mouseUpHandler = function() {
                        resizer.classList.remove('resizing');
                        document.body.style.cursor = '';
                        document.body.style.userSelect = '';
                        document.removeEventListener('mousemove', mouseMoveHandler);
                        document.removeEventListener('mouseup', mouseUpHandler);
                    };
                    
                    document.addEventListener('mousemove', mouseMoveHandler);
                    document.addEventListener('mouseup', mouseUpHandler);
                });
            });
        }, 100);
    });
    </script>
@endsection
