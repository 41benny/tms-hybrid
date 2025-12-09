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

{{-- Desktop table view --}}
<div class="hidden md:block overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200 dark:divide-[#2d2d2d] resizable-table">
        <thead class="bg-slate-50 dark:bg-[#252525]">
            <tr>
                @php
                    $sortBy = request('sort_by', 'created_at');
                    $sortOrder = request('sort_order', 'desc');
                    
                    function getSortUrl($column) {
                        $currentSort = request('sort_by', 'created_at');
                        $currentOrder = request('sort_order', 'desc');
                        $newOrder = ($currentSort === $column && $currentOrder === 'asc') ? 'desc' : 'asc';
                        return request()->fullUrlWithQuery(['sort_by' => $column, 'sort_order' => $newOrder]);
                    }
                    
                    function getSortIcon($column) {
                        $currentSort = request('sort_by', 'created_at');
                        $currentOrder = request('sort_order', 'desc');
                        if ($currentSort !== $column) {
                            return '<svg class="w-3 h-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>';
                        }
                        return $currentOrder === 'asc' 
                            ? '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>'
                            : '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>';
                    }
                @endphp
                
                <th class="px-4 py-3 text-left text-[10px] font-medium uppercase tracking-wider text-slate-600 dark:text-slate-400 cursor-pointer hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors resize-handle" onclick="window.location.href='{{ getSortUrl('job_number') }}'">
                    <div class="flex items-center gap-1">
                        Job Number
                        {!! getSortIcon('job_number') !!}
                    </div>
                </th>
                <th class="px-4 py-3 text-left text-[10px] font-medium uppercase tracking-wider text-slate-600 dark:text-slate-400 cursor-pointer hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors resize-handle" onclick="window.location.href='{{ getSortUrl('order_date') }}'">
                    <div class="flex items-center gap-1">
                        Order Date
                        {!! getSortIcon('order_date') !!}
                    </div>
                </th>
                <th class="px-4 py-3 text-left text-[10px] font-medium uppercase tracking-wider text-slate-600 dark:text-slate-400 resize-handle">Customer</th>
                <th class="px-4 py-3 text-left text-[10px] font-medium uppercase tracking-wider text-slate-600 dark:text-slate-400 resize-handle">Cargo Unit</th>
                <th class="px-4 py-3 text-left text-[10px] font-medium uppercase tracking-wider text-slate-600 dark:text-slate-400 resize-handle">Sales</th>
                <th class="px-4 py-3 text-left text-[10px] font-medium uppercase tracking-wider text-slate-600 dark:text-slate-400 resize-handle">Legs</th>
                <th class="px-4 py-3 text-left text-[10px] font-medium uppercase tracking-wider text-slate-600 dark:text-slate-400 cursor-pointer hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors resize-handle" onclick="window.location.href='{{ getSortUrl('status') }}'">
                    <div class="flex items-center gap-1">
                        Status JO
                        {!! getSortIcon('status') !!}
                    </div>
                </th>
                <th class="px-4 py-3 text-left text-[10px] font-medium uppercase tracking-wider text-slate-600 dark:text-slate-400 resize-handle">Status Inv</th>
                <th class="px-4 py-3 text-left text-[10px] font-medium uppercase tracking-wider text-slate-600 dark:text-slate-400 cursor-pointer hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors resize-handle" onclick="window.location.href='{{ getSortUrl('created_at') }}'">
                    <div class="flex items-center gap-1">
                        Created At
                        {!! getSortIcon('created_at') !!}
                    </div>
                </th>
                <th class="px-4 py-3 text-left text-[10px] font-medium uppercase tracking-wider text-slate-600 dark:text-slate-400">Aksi</th>
            </tr>
        </thead>
        <tbody class="bg-white dark:bg-[#1e1e1e] divide-y divide-slate-200 dark:divide-[#2d2d2d]">
            @forelse($orders as $order)
                <tr class="hover:bg-slate-50 dark:hover:bg-[#252525] transition-colors">
                    <td class="px-4 py-2 whitespace-nowrap">
                        <div class="text-xs font-medium text-slate-900 dark:text-slate-100">
                            {{ $order->job_number }}
                        </div>
                    </td>
                    <td class="px-4 py-2 whitespace-nowrap">
                        <div class="text-xs text-slate-600 dark:text-slate-400">
                            {{ $order->order_date->format('d M Y') }}
                        </div>
                    </td>
                    <td class="px-4 py-2">
                        <div class="text-xs text-slate-900 dark:text-slate-100 max-w-[200px] truncate" data-tooltip="{{ $order->customer->name }}">
                            {{ $order->customer->name }}
                        </div>
                    </td>
                    <td class="px-4 py-2">
                        @if($order->items->count() > 0)
                            <div class="text-xs text-slate-600 dark:text-slate-400 max-w-[150px] truncate">
                                @foreach($order->items->take(2) as $index => $item)
                                    @if($index > 0), @endif
                                    {{ $item->equipment?->name ?? $item->cargo_type }}
                                @endforeach
                                @if($order->items->count() > 2)
                                    <span class="text-[10px] text-slate-500">+{{ $order->items->count() - 2 }}</span>
                                @endif
                            </div>
                        @else
                            <span class="text-xs text-slate-400 dark:text-slate-500">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-2">
                        <div class="text-xs text-slate-600 dark:text-slate-400 max-w-[120px] truncate" data-tooltip="{{ $order->sales?->name }}">
                            {{ $order->sales?->name ?? '-' }}
                        </div>
                    </td>
                    <td class="px-4 py-2 text-center">
                        <span class="text-xs text-slate-600 dark:text-slate-400">{{ $order->shipmentLegs->count() }}</span>
                    </td>
                    <td class="px-4 py-2">
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
                    <td class="px-4 py-2 whitespace-nowrap">
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
                    <td class="px-4 py-2 whitespace-nowrap">
                        <div class="text-xs text-slate-500 dark:text-slate-400">
                            {{ $order->created_at->format('d M Y H:i') }}
                        </div>
                    </td>
                    <td class="px-4 py-2">
                        <div class="flex items-center gap-1">
                            <x-button :href="route('job-orders.show', [$order, 'view' => 'table'])" variant="ghost" size="sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </x-button>
                            <x-button :href="route('job-orders.edit', [$order, 'view' => 'table'])" variant="ghost" size="sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </x-button>
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
</div>

<style>
/* Custom tooltip styling - clean theme-aware tooltips */
[data-tooltip] {
    position: relative;
    display: inline-block;
}

/* Custom tooltip on hover */
[data-tooltip]:hover::after {
    content: attr(data-tooltip);
    position: absolute;
    bottom: calc(100% + 4px);
    left: 50%;
    transform: translateX(-50%);
    padding: 6px 12px;
    background: rgba(255, 255, 255, 0.98);
    color: #1e293b;
    font-size: 11px;
    font-weight: 500;
    border-radius: 6px;
    white-space: nowrap;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    z-index: 1000;
    pointer-events: none;
    border: 1px solid rgba(148, 163, 184, 0.2);
    opacity: 1;
    visibility: visible;
}

/* Dark mode tooltip */
:is(.dark) [data-tooltip]:hover::after {
    background: rgba(51, 65, 85, 0.98);
    color: #f1f5f9;
    border-color: rgba(100, 116, 139, 0.3);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

/* Arrow for tooltip */
[data-tooltip]:hover::before {
    content: '';
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    border: 6px solid transparent;
    border-top-color: rgba(255, 255, 255, 0.98);
    z-index: 1000;
    pointer-events: none;
    opacity: 1;
    visibility: visible;
}

/* Dark mode arrow */
:is(.dark) [data-tooltip]:hover::before {
    border-top-color: rgba(51, 65, 85, 0.98);
}
</style>


