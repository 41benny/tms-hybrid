<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
        <thead class="bg-slate-50 dark:bg-slate-950">
            <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Job Number</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Customer</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Sales</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Service</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Legs</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Status</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="bg-white dark:bg-slate-900 divide-y divide-slate-200 dark:divide-slate-800">
            @forelse($orders as $order)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="font-medium text-slate-900 dark:text-slate-100">{{ $order->job_number }}</div>
                        <div class="text-xs text-slate-500">{{ $order->order_date->format('d M Y') }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-slate-900 dark:text-slate-100">{{ $order->customer->name }}</div>
                    </td>
                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                        {{ $order->sales?->name ?? '-' }}
                    </td>
                    <td class="px-6 py-4">
                        <x-badge variant="{{ $order->service_type == 'multimoda' ? 'warning' : 'default' }}">
                            {{ strtoupper($order->service_type) }}
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
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <x-button :href="route('job-orders.show', $order)" variant="ghost" size="sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </x-button>
                            <x-button :href="route('job-orders.edit', $order)" variant="ghost" size="sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </x-button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
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

