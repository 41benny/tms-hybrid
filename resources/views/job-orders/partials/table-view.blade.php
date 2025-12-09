{{-- Mobile card view (Sales friendly) --}}
<div class="space-y-3 md:hidden">
    @forelse($orders as $order)
        <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#111827] p-4 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-xs font-semibold text-slate-900 dark:text-slate-100">
                        {{ $order->job_number }}
                    </div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400">
                        {{ $order->order_date->format('d M Y') }}
                    </div>
                    <div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                        {{ $order->customer->name }}
                    </div>
                    @if($order->sales?->name)
                        <div class="text-[11px] text-slate-500 dark:text-slate-400">
                            Sales: <span class="font-medium">{{ $order->sales?->name }}</span>
                        </div>
                    @endif
                </div>
                <div class="flex flex-col items-end gap-1">
                    <x-badge :variant="match($order->status) {
                        'draft' => 'default',
                        'confirmed' => 'default',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                    }" size="sm">
                        {{ strtoupper(str_replace('_', ' ', $order->status)) }}
                    </x-badge>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400">
                        {{ $order->shipmentLegs->count() }} leg
                    </div>
                </div>
            </div>

            @php $invoiceStatus = $order->invoice_status; @endphp
            <div class="mt-3 flex items-center justify-between text-[11px]">
                <div class="flex items-center gap-1">
                    @if($invoiceStatus === 'not_invoiced')
                        <x-badge variant="danger" size="xs">
                            Belum Invoice
                        </x-badge>
                    @elseif($invoiceStatus === 'partially_invoiced')
                        <x-badge variant="warning" size="xs">
                            Sebagian
                        </x-badge>
                    @else
                        <x-badge variant="success" size="xs">
                            Sudah Invoice
                        </x-badge>
                    @endif
                </div>
                @if($invoiceStatus === 'not_invoiced' && in_array($order->status, ['completed', 'in_progress']))
                    <a href="{{ route('invoices.create', ['customer_id' => $order->customer_id, 'job_order_ids[]' => $order->id]) }}"
                       class="text-[11px] text-blue-600 dark:text-blue-400 hover:underline">
                        + Buat Invoice
                    </a>
                @endif
            </div>

            <div class="mt-3 flex items-center justify-end gap-2">
                <a href="{{ route('job-orders.show', [$order, 'view' => 'table']) }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-[11px] text-slate-700 dark:text-slate-200">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Detail
                </a>
                <a href="{{ route('job-orders.edit', [$order, 'view' => 'table']) }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-600 text-[11px] text-white">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </a>
            </div>
        </div>
    @empty
        <div class="rounded-xl border border-dashed border-slate-300 dark:border-slate-700 p-6 text-center text-slate-500 dark:text-slate-400">
            <div class="mb-2 flex justify-center">
                <svg class="w-10 h-10 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M9 8h.01M7 20h10a2 2 0 002-2V6a2 2 0 00-2-2H9.5L7 6.5V18a2 2 0 002 2z" />
                </svg>
            </div>
            <p class="text-sm">Belum ada job order</p>
        </div>
    @endforelse
</div>

{{-- Desktop table view with resizable columns and filters --}}
<div class="hidden md:block overflow-x-auto">
    <form method="get" id="filter-form">
        {{-- Preserve view mode and sorting --}}
        <input type="hidden" name="view" value="{{ request('view', 'table') }}">
        <input type="hidden" name="sort_by" id="sort_by" value="{{ request('sort_by', 'created_at') }}">
        <input type="hidden" name="sort_order" id="sort_order" value="{{ request('sort_order', 'desc') }}">
        
        <table class="min-w-full text-sm" id="job-orders-table">
            <thead class="text-left border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50">
                {{-- Row 1: Column Headers (Sortable) --}}
                @php
                    $sortBy = request('sort_by', 'created_at');
                    $sortOrder = request('sort_order', 'desc');
                @endphp
                <tr class="text-slate-600 dark:text-slate-400 text-xs uppercase bg-slate-50 dark:bg-slate-800/50">
                    <th class="px-3 py-3 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors rounded-tl-xl" style="width: 130px" onclick="sortTable('job_number')">
                        <div class="flex items-center gap-1">
                            Job Number
                            @if($sortBy === 'job_number')
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @if($sortOrder === 'asc')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    @endif
                                </svg>
                            @else
                                <svg class="w-3 h-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                </svg>
                            @endif
                        </div>
                    </th>
                    <th class="px-3 py-3 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" style="width: 100px" onclick="sortTable('order_date')">
                        <div class="flex items-center gap-1">
                            Order Date
                            @if($sortBy === 'order_date')
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @if($sortOrder === 'asc')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    @endif
                                </svg>
                            @else
                                <svg class="w-3 h-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                </svg>
                            @endif
                        </div>
                    </th>
                    <th class="px-3 py-3" style="width: 180px">Customer</th>
                    <th class="px-3 py-3" style="width: 120px">Cargo Unit</th>
                    <th class="px-3 py-3" style="width: 100px">Sales</th>
                    <th class="px-3 py-3" style="width: 50px">Legs</th>
                    <th class="px-3 py-3 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" style="width: 90px" onclick="sortTable('status')">
                        <div class="flex items-center gap-1">
                            Status JO
                            @if($sortBy === 'status')
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @if($sortOrder === 'asc')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    @endif
                                </svg>
                            @else
                                <svg class="w-3 h-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                </svg>
                            @endif
                        </div>
                    </th>
                    <th class="px-3 py-3" style="width: 90px">Status Inv</th>
                    <th class="px-3 py-3 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" style="width: 120px" onclick="sortTable('created_at')">
                        <div class="flex items-center gap-1">
                            Created At
                            @if($sortBy === 'created_at')
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @if($sortOrder === 'asc')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    @endif
                                </svg>
                            @else
                                <svg class="w-3 h-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                </svg>
                            @endif
                        </div>
                    </th>
                    <th class="px-3 py-3 text-center rounded-tr-xl" style="width: 80px">Aksi</th>
                </tr>
                {{-- Row 2: Filters --}}
                <tr class="bg-slate-100 dark:bg-slate-900/50 text-xs">
                    <th class="px-1 py-1">
                        <input type="text" name="job_number" value="{{ request('job_number') }}" placeholder="Cari..." class="w-full px-2 py-1 text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500" onkeypress="if(event.keyCode==13){this.form.submit()}">
                    </th>
                    <th class="px-1 py-1">
                        <input type="date" name="order_date" value="{{ request('order_date') }}" class="w-full px-1 py-1 text-[10px] rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500" onchange="this.form.submit()">
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
                        <input type="text" name="cargo_unit" value="{{ request('cargo_unit') }}" placeholder="Cari..." class="w-full px-2 py-1 text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500" onkeypress="if(event.keyCode==13){this.form.submit()}">
                    </th>
                    <th class="px-1 py-1">
                        <select name="sales_id" class="w-full px-1 py-1 text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500" onchange="this.form.submit()">
                            <option value="">All</option>
                            @foreach($salesList as $s)
                                <option value="{{ $s->id }}" @selected(request('sales_id')==$s->id)>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </th>
                    <th class="px-1 py-1">
                        {{-- Legs: no filter --}}
                    </th>
                    <th class="px-1 py-1">
                        <select name="status" class="w-full px-1 py-1 text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500" onchange="this.form.submit()">
                            <option value="">All</option>
                            @foreach(['draft' => 'Draft', 'confirmed' => 'Confirmed', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $val => $label)
                                <option value="{{ $val }}" @selected(request('status')===$val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </th>
                    <th class="px-1 py-1">
                        <select name="invoice_status" class="w-full px-1 py-1 text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500" onchange="this.form.submit()">
                            <option value="">All</option>
                            <option value="not_invoiced" @selected(request('invoice_status')==='not_invoiced')>Belum</option>
                            <option value="invoiced" @selected(request('invoice_status')==='invoiced')>Sudah</option>
                        </select>
                    </th>
                    <th class="px-1 py-1">
                        <input type="date" name="created_at" value="{{ request('created_at') }}" class="w-full px-1 py-1 text-[10px] rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500" onchange="this.form.submit()">
                    </th>
                    <th class="px-1 py-1 text-center">
                        <a href="{{ route('job-orders.index', ['view' => request('view', 'table')]) }}" class="text-xs text-red-600 hover:text-red-800" title="Reset Filter">✕</a>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <td class="px-3 py-3">
                            <div class="font-medium text-blue-600 dark:text-blue-400">{{ $order->job_number }}</div>
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap">
                            <div class="text-slate-600 dark:text-slate-400">{{ $order->order_date->format('d/m/Y') }}</div>
                        </td>
                        <td class="px-3 py-3">
                            <div class="truncate max-w-[160px]" title="{{ $order->customer->name }}">{{ $order->customer->name }}</div>
                        </td>
                        <td class="px-3 py-3">
                            @if($order->items->count() > 0)
                                <div class="text-slate-600 dark:text-slate-400 truncate max-w-[100px]">
                                    @foreach($order->items->take(2) as $index => $item)
                                        @if($index > 0), @endif
                                        {{ $item->equipment?->name ?? $item->cargo_type }}
                                    @endforeach
                                    @if($order->items->count() > 2)
                                        <span class="text-[10px] text-slate-500">+{{ $order->items->count() - 2 }}</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-3 py-3">
                            <div class="text-slate-600 dark:text-slate-400 truncate max-w-[80px]" title="{{ $order->sales?->name }}">
                                {{ $order->sales?->name ?? '-' }}
                            </div>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <span class="text-slate-600 dark:text-slate-400">{{ $order->shipmentLegs->count() }}</span>
                        </td>
                        <td class="px-3 py-3">
                            <x-badge :variant="match($order->status) {
                                'draft' => 'default',
                                'confirmed' => 'default',
                                'in_progress' => 'warning',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                            }" size="sm">
                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                            </x-badge>
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap">
                            @php $invoiceStatus = $order->invoice_status; @endphp
                            @if($invoiceStatus === 'not_invoiced')
                                <x-badge variant="danger" size="sm">
                                    <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    Belum
                                </x-badge>
                            @elseif($invoiceStatus === 'partially_invoiced')
                                <x-badge variant="warning" size="sm">
                                    <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Sebagian
                                </x-badge>
                            @else
                                <x-badge variant="success" size="sm">
                                    <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Sudah
                                </x-badge>
                            @endif
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap">
                            <div class="text-slate-500 dark:text-slate-400 text-xs">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                        </td>
                        <td class="px-3 py-3">
                            <div class="flex items-center gap-2 justify-center">
                                <a class="inline-flex items-center px-2 py-1 text-xs text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded" href="{{ route('job-orders.show', [$order, 'view' => 'table']) }}" title="Lihat Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                </a>
                                <a class="inline-flex items-center px-2 py-1 text-xs text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded" href="{{ route('job-orders.edit', [$order, 'view' => 'table']) }}" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-12 h-12 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M9 8h.01M7 20h10a2 2 0 002-2V6a2 2 0 00-2-2H9.5L7 6.5V18a2 2 0 002 2z" />
                                </svg>
                                <p class="text-sm">Belum ada job order</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </form>
</div>

<style>
    #job-orders-table {
        table-layout: fixed;
        width: 100%;
    }

    #job-orders-table td {
        white-space: normal;
    }

    /* Resizable columns */
    #job-orders-table th {
        position: relative;
        user-select: none;
        overflow: visible;
    }

    #job-orders-table th .resizer {
        position: absolute;
        top: 0;
        right: -3px;
        width: 6px;
        cursor: col-resize;
        user-select: none;
        height: 100%;
        z-index: 1;
    }

    #job-orders-table th .resizer:hover {
        background-color: rgba(99, 102, 241, 0.5);
    }

    #job-orders-table th .resizer.resizing {
        background-color: rgba(99, 102, 241, 0.7);
    }

    @media print {
        .no-print, button, form, nav, aside {
            display: none !important;
        }
        
        #job-orders-table {
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
    // Sorting function
    function sortTable(column) {
        const sortByInput = document.getElementById('sort_by');
        const sortOrderInput = document.getElementById('sort_order');
        
        const currentSort = sortByInput.value;
        const currentOrder = sortOrderInput.value;
        
        if (currentSort === column) {
            sortOrderInput.value = currentOrder === 'asc' ? 'desc' : 'asc';
        } else {
            sortByInput.value = column;
            sortOrderInput.value = 'desc';
        }
        
        document.getElementById('filter-form').submit();
    }

    // Resizable columns
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            const table = document.getElementById('job-orders-table');
            if (!table) return;
            
            // Only target the first row headers for resizing
            const cols = table.querySelectorAll('thead tr:first-child th');
            
            cols.forEach((col, index) => {
                // Set initial width
                if (!col.style.width) {
                    col.style.width = (col.offsetWidth + 20) + 'px';
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
