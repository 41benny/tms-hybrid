@forelse($jobOrders as $jo)
    @php
        $mainItem  = $jo->items->first();
        $equipment = $mainItem?->equipment?->name ?? $mainItem?->cargo_type;
        $qty       = $mainItem?->quantity;
        $qtyText   = $qty !== null ? ((float) $qty + 0).' unit' : null;
        $firstLeg  = $jo->shipmentLegs->sortBy('load_date')->first();
    @endphp

    <label class="flex items-start gap-3 p-3 hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer">
        <input type="checkbox"
               name="job_order_ids[]"
               value="{{ $jo->id }}"
               class="mt-1 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
               @checked(in_array($jo->id, $selectedJobOrderIds ?? []))>

        <div class="text-sm">
            <div class="font-semibold text-slate-900 dark:text-slate-100">
                {{ $jo->job_number }}
            </div>

            {{-- Baris rute --}}
            <div class="text-xs text-slate-500 dark:text-slate-400">
                {{ $jo->origin }} → {{ $jo->destination }}
            </div>

            {{-- Baris unit + qty + tgl muat --}}
            @if($equipment || $qtyText || ($firstLeg && $firstLeg->load_date))
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    @if($equipment)
                        <span>{{ $equipment }}</span>
                    @endif

                    @if($qtyText)
                        @if($equipment)
                            <span class="mx-1">•</span>
                        @endif
                        <span>{{ $qtyText }}</span>
                    @endif

                    @if($firstLeg && $firstLeg->load_date)
                        @if($equipment || $qtyText)
                            <span class="mx-1">•</span>
                        @endif
                        <span>Load: {{ $firstLeg->load_date->format('d M Y') }}</span>
                    @endif
                </div>
            @endif

            {{-- Baris status + nilai tagihan --}}
            <div class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                Status: {{ ucfirst(str_replace('_', ' ', $jo->status)) }}
                • Nilai Tagihan: Rp {{ number_format((float) ($jo->invoice_amount ?? 0), 0, ',', '.') }}
            </div>
        </div>
    </label>
@empty
    <div class="p-4 text-sm text-slate-500 dark:text-slate-400">
        Tidak ada Job Order yang bisa diinvoice untuk customer ini.
    </div>
@endforelse
