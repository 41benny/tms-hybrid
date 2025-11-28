<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-4">
    @forelse($orders as $order)
        @php
            $items = $order->items;
            $mainItem = $items->first();
            $cargoTitle = $mainItem?->cargo_type ?: $mainItem?->equipment?->name;
            $itemCount = $items->count();
            $additionalItems = max($itemCount - 1, 0);
            $totalQuantity = $items->sum('quantity');
            $totalQuantityFormatted = null;
            if ($totalQuantity > 0) {
                $totalQuantityFormatted = ($totalQuantity == floor($totalQuantity))
                    ? number_format($totalQuantity, 0)
                    : number_format($totalQuantity, 2);
                $totalQuantityFormatted = rtrim(rtrim($totalQuantityFormatted, '0'), '.');
            }
        @endphp
        <div 
            class="theme-panel rounded-2xl shadow-lg hover:shadow-2xl transition-all cursor-pointer flex flex-col hover:scale-[1.02]"
            onclick="window.location.href='{{ route('job-orders.show', $order) }}'"
            role="link"
            tabindex="0"
        >
            <!-- Header -->
            <div class="px-4 pt-4 pb-3 flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <a href="{{ route('job-orders.show', $order) }}" class="text-sm font-semibold theme-text-primary hover:underline">
                        {{ $order->job_number }}
                    </a>
                    <p class="mt-1 text-sm font-semibold text-slate-100 dark:text-slate-200 truncate">
                        {{ $order->customer->name }}
                    </p>
                </div>
                <div class="flex flex-col items-end gap-1">
                    <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wide
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
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ $order->order_date->format('d/m/Y') }}
                    </p>
                </div>
            </div>

            <div class="border-t theme-border"></div>

            <!-- Body -->
            <div class="px-4 py-4 space-y-3 flex-1">
                <!-- Sales (label kanan-kiri) -->
                <div class="flex items-center gap-2 text-xs theme-text-muted">
                    <svg class="w-4 h-4 theme-text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A4 4 0 019 15h6a4 4 0 013.879 2.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="shrink-0 theme-text-muted">Sales:</span>
                    <span class="font-medium text-slate-100 dark:text-slate-200 truncate">
                        {{ $order->sales?->name ?? '-' }}
                    </span>
                </div>

                <!-- Cargo summary (label kanan-kiri) -->
                @if($cargoTitle)
                    <div class="flex items-center gap-2 text-xs theme-text-muted">
                        <svg class="w-4 h-4 theme-text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7l9-4 9 4-9 4-9-4z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 17l9 4 9-4M3 12l9 4 9-4" />
                        </svg>
                        <span class="shrink-0 theme-text-muted">Cargo:</span>
                        <span class="font-semibold text-slate-100 dark:text-slate-200 truncate">
                            {{ $cargoTitle }}
                            @if($additionalItems > 0)
                                <span class="font-normal text-xs theme-text-muted">
                                    (+{{ $additionalItems }} item{{ $additionalItems > 1 ? 's' : '' }})
                                </span>
                            @endif
                            @if($totalQuantityFormatted)
                                <span class="font-normal text-xs theme-text-muted">
                                    &mdash; {{ $totalQuantityFormatted }} units total
                                </span>
                            @endif
                        </span>
                    </div>
                @endif

                <!-- Route (From / To dalam satu baris) -->
                <div class="flex items-center gap-2 text-xs theme-text-muted">
                    <span class="shrink-0 theme-text-muted">From:</span>
                    <span class="font-medium text-slate-100 dark:text-slate-200 truncate max-w-[35%]">
                        {{ $order->origin ?: '-' }}
                    </span>
                    <span class="shrink-0 theme-text-muted ml-2">To:</span>
                    <span class="font-medium text-slate-100 dark:text-slate-200 truncate max-w-[35%]">
                        {{ $order->destination ?: '-' }}
                    </span>
                </div>

                <!-- Legs only -->
                <div class="flex items-center justify-end text-xs">
                    <span class="theme-text-muted">
                        {{ $order->shipmentLegs->count() }} legs
                    </span>
                </div>

                <!-- Ordered date -->
                <div class="flex items-center gap-2 pt-0.5 text-xs theme-text-muted">
                    <svg class="w-4 h-4 theme-text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2m-9 4h10m-9 4h6m5-9H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2v-9a2 2 0 00-2-2z" />
                    </svg>
                    <span>Ordered on {{ $order->order_date->format('m/d/Y') }}</span>
                </div>
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
