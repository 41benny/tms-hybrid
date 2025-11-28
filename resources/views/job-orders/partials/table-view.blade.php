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
            <div class="text-3xl mb-1">📭</div>
            <p class="text-sm">Belum ada job order</p>
        </div>
    @endforelse
</div>

{{-- Desktop table view --}}
<div class="hidden md:block overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200 dark:divide-[#2d2d2d]">
        <thead class="bg-slate-50 dark:bg-[#252525]">
            <tr>
                <th class="px-4 py-3 text-left text-[10px] font-medium uppercase tracking-wider text-slate-600 dark:text-slate-400">Job Number</th>
                <th class="px-4 py-3 text-left text-[10px] font-medium uppercase tracking-wider text-slate-600 dark:text-slate-400">Customer</th>
                <th class="px-4 py-3 text-left text-[10px] font-medium uppercase tracking-wider text-slate-600 dark:text-slate-400">Sales</th>
                <th class="px-4 py-3 text-left text-[10px] font-medium uppercase tracking-wider text-slate-600 dark:text-slate-400">Legs</th>
                <th class="px-4 py-3 text-left text-[10px] font-medium uppercase tracking-wider text-slate-600 dark:text-slate-400">Status Job Order</th>
                <th class="px-4 py-3 text-left text-[10px] font-medium uppercase tracking-wider text-slate-600 dark:text-slate-400">Status Invoice</th>
                <th class="px-4 py-3 text-left text-[10px] font-medium uppercase tracking-wider text-slate-600 dark:text-slate-400">Aksi</th>
            </tr>
        </thead>
        <tbody class="bg-white dark:bg-[#1e1e1e] divide-y divide-slate-200 dark:divide-[#2d2d2d]">
            @forelse($orders as $order)
                <tr class="hover:bg-slate-50 dark:hover:bg-[#252525] transition-colors">
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="text-xs text-slate-900 dark:text-slate-100">{{ $order->job_number }}</div>
                        <div class="text-[10px] text-slate-500">{{ $order->order_date->format('d M Y') }}</div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="text-xs text-slate-900 dark:text-slate-100">{{ $order->customer->name }}</div>
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-600 dark:text-slate-400">
                        {{ $order->sales?->name ?? '-' }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-xs text-slate-600 dark:text-slate-400">{{ $order->shipmentLegs->count() }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <x-badge :variant="match($order->status) {
                            'draft' => 'default',
                            'confirmed' => 'default',
                            'in_progress' => 'warning',
                            'completed' => 'success',
                            'cancelled' => 'danger',
                        }" size="sm">
                            {{ strtoupper(str_replace('_', ' ', $order->status)) }}
                        </x-badge>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        @php $invoiceStatus = $order->invoice_status; @endphp
                        @if($invoiceStatus === 'not_invoiced')
                            <div class="flex flex-col gap-1">
                                <x-badge variant="danger" size="sm">
                                    <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    Belum Invoice
                                </x-badge>
                                @if(in_array($order->status, ['completed', 'in_progress']))
                                    <a href="{{ route('invoices.create', ['customer_id' => $order->customer_id, 'job_order_ids[]' => $order->id]) }}"
                                       class="text-[10px] text-blue-600 dark:text-blue-400 hover:underline">
                                        + Buat Invoice
                                    </a>
                                @endif
                            </div>
                        @elseif($invoiceStatus === 'partially_invoiced')
                            <div class="flex flex-col gap-1">
                                <x-badge variant="warning" size="sm">
                                    <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Sebagian
                                </x-badge>
                                <div class="text-[10px] text-slate-600 dark:text-slate-400">
                                    Rp {{ number_format($order->total_invoiced, 0, ',', '.') }}
                                </div>
                            </div>
                        @else
                            <x-badge variant="success" size="sm">
                                <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Sudah Invoice
                            </x-badge>
                        @endif
                    </td>
                    <td class="px-4 py-3">
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
                    <td colspan="8" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">
                        <div class="flex flex-col items-center gap-2">
                            <span class="text-4xl">dY"<</span>
                            <p class="text-sm">Belum ada job order</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
