<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200 dark:divide-[#2d2d2d] text-xs">
        <thead class="bg-slate-50 dark:bg-[#252525]">
            <tr>
                <th class="px-6 py-4 text-left text-xs text-slate-600 dark:text-slate-400">Job Number</th>
                <th class="px-6 py-4 text-left text-xs text-slate-600 dark:text-slate-400">Customer</th>
                <th class="px-6 py-4 text-left text-xs text-slate-600 dark:text-slate-400">Sales</th>
                <th class="px-6 py-4 text-left text-xs text-slate-600 dark:text-slate-400">Service</th>
                <th class="px-6 py-4 text-left text-xs text-slate-600 dark:text-slate-400">Legs</th>
                <th class="px-6 py-4 text-left text-xs text-slate-600 dark:text-slate-400">Status</th>
                <th class="px-6 py-4 text-left text-xs text-slate-600 dark:text-slate-400">Invoice</th>
                <th class="px-6 py-4 text-left text-xs text-slate-600 dark:text-slate-400">Aksi</th>
            </tr>
        </thead>
        <tbody class="bg-white dark:bg-[#1e1e1e] divide-y divide-slate-200 dark:divide-[#2d2d2d]">
            @forelse($orders as $order)
                <tr class="hover:bg-slate-50 dark:hover:bg-[#252525] transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-slate-900 dark:text-slate-100">{{ $order->job_number }}</div>
                        <div class="text-xs text-slate-500">{{ $order->order_date->format('d M Y') }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-slate-900 dark:text-slate-100" style="text-transform:none; font-weight:400;">{{ $order->customer->name }}</div>
                    </td>
                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                        {{ $order->sales?->name ?? '-' }}
                    </td>
                    <td class="px-6 py-4">
                        <x-badge variant="{{ $order->service_type == 'multimoda' ? 'warning' : 'default' }}">
                            {{ $order->service_type }}
                        </x-badge>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm text-slate-600 dark:text-slate-400">{{ $order->shipmentLegs->count() }} Legs</span>
                    </td>
                    <td class="px-6 py-4">
                        <x-badge :variant="match($order->status) {
                            'draft' => 'default',
                            'confirmed' => 'default',
                            'in_progress' => 'warning',
                            'completed' => 'success',
                            'cancelled' => 'danger',
                        }">
                            {{ ucfirst($order->status) }}
                        </x-badge>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $invoiceStatus = $order->invoice_status;
                        @endphp

                        @if($invoiceStatus === 'not_invoiced')
                            <div class="flex flex-col gap-1">
                                <x-badge variant="danger" class="text-xs">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    Belum Invoice
                                </x-badge>
                                @if(in_array($order->status, ['completed', 'in_progress']))
                                <a href="{{ route('invoices.create', ['customer_id' => $order->customer_id, 'job_order_ids[]' => $order->id]) }}"
                                   class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                    + Buat Invoice
                                </a>
                                @endif
                            </div>
                        @elseif($invoiceStatus === 'partially_invoiced')
                            <div class="flex flex-col gap-1">
                                <x-badge variant="warning" class="text-xs">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Sebagian
                                </x-badge>
                                <div class="text-xs text-slate-600 dark:text-slate-400">
                                    Rp {{ number_format($order->total_invoiced, 0, ',', '.') }} / {{ number_format($order->invoice_amount + $order->total_billable, 0, ',', '.') }}
                                </div>
                            </div>
                        @else
                            <x-badge variant="success" class="text-xs">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Sudah Invoice
                            </x-badge>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <x-button :href="route('job-orders.show', [$order, 'view' => 'table'])" variant="ghost" size="sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </x-button>
                            <x-button :href="route('job-orders.edit', [$order, 'view' => 'table'])" variant="ghost" size="sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </x-button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                        <div class="flex flex-col items-center gap-2">
                            <span class="text-4xl">📋</span>
                            <p class="text-sm">Belum ada job order</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

