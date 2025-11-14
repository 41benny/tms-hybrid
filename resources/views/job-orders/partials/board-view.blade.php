<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-4">
    @forelse($orders as $order)
        @php
            $mainItem = $order->items->first();
            $cargoTitle = $mainItem?->cargo_type ?: $mainItem?->equipment?->name;
            $cargoDetail = null;
            if ($mainItem) {
                $qty = $mainItem->quantity;
                $cargoDetail = trim(
                    ($qty ? ($qty + 0) . ' units' : '') .
                    ($mainItem->serial_numbers ? ' (S/N: ' . $mainItem->serial_numbers . ')' : '')
                );
            }
        @endphp
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md transition-shadow">
            <!-- Header -->
            <div class="px-4 pt-4 pb-3 flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <a href="{{ route('job-orders.show', $order) }}" class="text-sm font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                        {{ $order->job_number }}
                    </a>
                    <p class="mt-1 text-base font-semibold text-slate-900 dark:text-slate-100 truncate">
                        {{ $order->customer->name }}
                    </p>
                </div>
                <div class="flex flex-col items-end gap-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold
                        {{ match($order->status) {
                            'draft' => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
                            'confirmed' => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
                            'in_progress' => 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300',
                            'completed' => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
                            'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
                            default => 'bg-slate-100 text-slate-700'
                        } }}">
                        {{ strtoupper(str_replace('_', ' ', $order->status)) }}
                    </span>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                        {{ $order->order_date->format('d/m/Y') }}
                    </p>
                </div>
            </div>

            <div class="border-t border-slate-200 dark:border-slate-700"></div>

            <!-- Body -->
            <div class="px-4 py-3 space-y-2.5">
                <!-- Sales (label kanan-kiri) -->
                <div class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A4 4 0 019 15h6a4 4 0 013.879 2.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="shrink-0 text-slate-500 dark:text-slate-400">Sales:</span>
                    <span class="font-medium text-slate-800 dark:text-slate-200 truncate">
                        {{ $order->sales?->name ?? '-' }}
                    </span>
                </div>

                <!-- Cargo summary (label kanan-kiri) -->
                @if($cargoTitle)
                    <div class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7l9-4 9 4-9 4-9-4z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 17l9 4 9-4M3 12l9 4 9-4" />
                        </svg>
                        <span class="shrink-0 text-slate-500 dark:text-slate-400">Cargo:</span>
                        <span class="font-semibold text-slate-900 dark:text-slate-100 truncate">
                            {{ $cargoTitle }}
                            @if($cargoDetail)
                                <span class="font-normal text-xs text-slate-500 dark:text-slate-400">
                                    &mdash; {{ $cargoDetail }}
                                </span>
                            @endif
                        </span>
                    </div>
                @endif

                <!-- Route (From / To dalam satu baris) -->
                <div class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300">
                    <span class="shrink-0 text-slate-500 dark:text-slate-400">From:</span>
                    <span class="font-medium text-slate-900 dark:text-slate-100 truncate max-w-[35%]">
                        {{ $order->origin ?: '-' }}
                    </span>
                    <span class="shrink-0 text-slate-500 dark:text-slate-400 ml-2">To:</span>
                    <span class="font-medium text-slate-900 dark:text-slate-100 truncate max-w-[35%]">
                        {{ $order->destination ?: '-' }}
                    </span>
                </div>

                <!-- Service type + legs -->
                <div class="flex items-center justify-between text-xs">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full font-medium
                        {{ $order->service_type == 'multimoda'
                            ? 'bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-300'
                            : 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300' }}">
                        {{ strtoupper($order->service_type) }}
                    </span>
                    <span class="text-slate-500 dark:text-slate-400">
                        {{ $order->shipmentLegs->count() }} legs
                    </span>
                </div>

                <!-- Ordered date -->
                <div class="flex items-center gap-2 pt-0.5 text-xs text-slate-500 dark:text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2m-9 4h10m-9 4h6m5-9H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2v-9a2 2 0 00-2-2z" />
                    </svg>
                    <span>Ordered on {{ $order->order_date->format('m/d/Y') }}</span>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="px-4 py-3 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700 flex items-center justify-end gap-2">
                <a href="{{ route('job-orders.show', $order) }}" class="p-1.5 text-slate-600 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </a>
                <a href="{{ route('job-orders.edit', $order) }}" class="p-1.5 text-slate-600 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </a>
            </div>
        </div>
    @empty
        <div class="col-span-full">
            <div class="flex flex-col items-center justify-center py-12 text-slate-500 dark:text-slate-400">
                <span class="text-4xl mb-2">dY"<</span>
                <p class="text-sm font-medium">Belum ada job order</p>
            </div>
        </div>
    @endforelse
</div>
