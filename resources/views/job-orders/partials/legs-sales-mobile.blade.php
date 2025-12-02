{{-- Shipment Legs – tampilan ringkas khusus Sales (mobile-first) --}}
<x-card>
    <x-slot:header>
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Shipment Legs</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">Ringkasan biaya per leg</p>
            </div>
            <x-button :href="route('job-orders.legs.create', $job)" variant="primary" size="sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </x-button>
        </div>
    </x-slot:header>

    @php
        $legCount = $job->shipmentLegs->count();
        $sliderId = 'legSlider-'.$job->id;
        // Slide 0: Job summary, Slide 1: Financial summary, Slide 2..N: Legs
        $totalSlides = $legCount + 2;
    @endphp

    @if($legCount === 0)
        <div class="text-center py-8 text-slate-500 dark:text-slate-400 text-sm">
            Belum ada shipment leg. Tambahkan leg baru untuk mulai mengatur transportasi.
        </div>
    @else
    <div class="relative" id="{{ $sliderId }}">
        <div class="flex overflow-x-auto snap-x snap-mandatory gap-4 pb-3 leg-track" style="-webkit-overflow-scrolling: touch;">
            {{-- Slide 0: Ringkasan Job --}}
            <div class="leg-slide snap-start w-full shrink-0 rounded-2xl border border-slate-700/70 bg-slate-900/80 px-4 py-4 space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="text-[11px] font-semibold text-cyan-300">Job Order</div>
                        <div class="text-base font-bold text-slate-50 leading-snug">{{ $job->job_number }}</div>
                        <div class="text-xs text-slate-400 truncate">{{ $job->customer->name }}</div>
                        <div class="mt-1 text-[11px] text-slate-400">
                            {{ $job->origin ?? '-' }} → {{ $job->destination ?? '-' }}
                        </div>
                    </div>
                    <div class="text-right shrink-0 space-y-1">
                        <x-badge :variant="match($job->status) {
                            'in_progress' => 'warning',
                            'completed' => 'success',
                            'cancelled' => 'danger',
                            default => 'default'
                        }" size="sm">
                            {{ strtoupper(str_replace('_', ' ', $job->status)) }}
                        </x-badge>
                        <div>
                            <x-badge :variant="match($job->invoice_status) {
                                'not_invoiced' => 'danger',
                                'partially_invoiced' => 'warning',
                                'invoiced' => 'success',
                                default => 'default'
                            }" size="sm">
                                {{ strtoupper(str_replace('_',' ', $job->invoice_status)) }}
                            </x-badge>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="rounded-xl bg-slate-800/70 border border-slate-700 p-3 space-y-1">
                        <div class="text-slate-400">Sales Agent</div>
                        <div class="text-sm font-semibold text-slate-100">
                            {{ $job->sales?->name ?? '-' }}
                        </div>
                    </div>
                    <div class="rounded-xl bg-slate-800/70 border border-slate-700 p-3 space-y-1">
                        <div class="text-slate-400">Service</div>
                        <div class="text-sm font-semibold text-slate-100">
                            {{ strtoupper($job->service_type) }}
                        </div>
                    </div>
                </div>

                <div class="flex gap-2 pt-1">
                    @if(!$job->isLocked())
                        <a href="{{ route('job-orders.edit', $job) }}" class="flex-1 inline-flex items-center justify-center rounded-lg border border-slate-700 bg-slate-800 text-slate-100 text-xs font-semibold py-2 hover:bg-slate-700 transition">
                            Edit
                        </a>
                        <button type="button" onclick="openCancelModal()" class="flex-1 inline-flex items-center justify-center rounded-lg border border-red-600/60 bg-red-600/15 text-red-100 text-xs font-semibold py-2 hover:bg-red-600/30 transition">
                            Cancel
                        </button>
                    @endif
                </div>
            </div>

            {{-- Slide 1: Financial Summary --}}
            <div class="leg-slide snap-start w-full shrink-0 rounded-2xl border border-slate-700/70 bg-slate-900/80 px-4 py-4 space-y-3">
                <div class="text-[11px] font-semibold text-cyan-300">Financial Summary</div>
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="rounded-xl bg-slate-800/70 border border-slate-700 p-3 space-y-1">
                        <div class="text-slate-400">Nilai Tagihan</div>
                        <div class="text-sm font-bold text-blue-200">
                            Rp {{ number_format($job->invoice_amount + $job->total_billable, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="rounded-xl bg-slate-800/70 border border-slate-700 p-3 space-y-1">
                        <div class="text-slate-400">Total Biaya (DPP)</div>
                        <div class="text-sm font-bold text-orange-200">
                            Rp {{ number_format($job->total_cost_dpp, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="col-span-2 rounded-xl bg-slate-800/70 border border-slate-700 p-3 space-y-1">
                        <div class="text-slate-400">Estimasi Margin</div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-bold text-green-200">Rp {{ number_format($job->margin, 0, ',', '.') }}</span>
                            <span class="text-xs font-semibold text-green-300">{{ number_format($job->margin_percentage, 1) }}%</span>
                        </div>
                    </div>
                </div>
                <div class="text-[11px] text-slate-400">
                    Base: Rp {{ number_format($job->invoice_amount, 0, ',', '.') }} | Billable: Rp {{ number_format($job->total_billable, 0, ',', '.') }}
                </div>
            </div>

            {{-- Slide Leg --}}
            @foreach($job->shipmentLegs as $leg)
                @php
                    $mainCost = $leg->mainCost;
                    $totalAmount = $leg->total_cost ?? 0;

                    $driverAdvance = $leg->driverAdvance;
                    $advanceStatus = $driverAdvance?->status;
                    $canRequestDp = $driverAdvance && $advanceStatus === 'pending';
                    $canRequestSettlement = $driverAdvance && $advanceStatus === 'dp_paid';

                    $canQuickVendor = in_array($leg->cost_category, ['vendor','pelayaran','asuransi','pic'], true)
                        && $leg->vendor_id
                        && $mainCost
                        && $totalAmount > 0
                        && $leg->vendorBillItems->isEmpty();
                @endphp

                <div class="leg-slide snap-start w-full shrink-0 rounded-2xl border border-slate-700/70 bg-slate-900/75 px-4 py-4 space-y-3">
                    {{-- Baris info utama --}}
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="text-[11px] font-semibold text-purple-400">
                                Leg #{{ $leg->leg_number }}
                            </div>
                            <div class="text-xs text-slate-500 truncate">
                                {{ $leg->leg_code }}
                            </div>
                            <div class="mt-1 text-xs text-slate-200">
                                @if($leg->cost_category === 'trucking' && $leg->truck)
                                    {{ $leg->truck->plate_number }} • {{ $leg->driver?->name ?? '-' }}
                                @elseif($leg->vendor)
                                    {{ $leg->vendor->name }}
                                @else
                                    {{ ucfirst($leg->cost_category) }}
                                @endif
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <div class="text-[11px] text-slate-400">Total</div>
                            <div class="text-base font-bold text-slate-50">
                                Rp {{ number_format($totalAmount, 0, ',', '.') }}
                            </div>
                            <x-badge :variant="$leg->status === 'completed' ? 'success' : 'default'" size="sm" class="mt-1">
                                {{ strtoupper($leg->status) }}
                            </x-badge>
                        </div>
                    </div>

                    {{-- Tombol aksi penting untuk sales --}}
                    <div class="flex flex-col gap-2">
                        @if($canRequestDp || $canRequestSettlement)
                            <a
                                href="{{ route('driver-advances.show', $driverAdvance) }}"
                                class="w-full inline-flex items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-[11px] font-semibold py-2 px-3 border border-slate-300 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                                {{ $canRequestDp ? 'Ajukan DP' : 'Ajukan Pelunasan' }}
                            </a>
                        @endif

                        @if($canQuickVendor)
                            <button
                                type="button"
                                class="w-full inline-flex items-center justify-center rounded-full bg-emerald-600 text-white text-[11px] font-semibold py-2 px-3 hover:bg-emerald-700 transition"
                                onclick="openGenerateBillModal({{ $leg->id }}, {{ $leg->additionalCosts->isNotEmpty() ? 'true' : 'false' }})">
                                Ajukan Vendor
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if($totalSlides > 1)
            {{-- Nav controls --}}
            <div class="flex items-center justify-between mt-2 text-slate-300 text-xs">
                <button type="button" class="px-3 py-1.5 rounded-full bg-slate-800/70 border border-slate-700 hover:bg-slate-700 transition" data-prev>
                    ‹ Prev
                </button>
                <div class="flex items-center gap-1" aria-label="Slide indicators">
                    @for($i = 0; $i < $totalSlides; $i++)
                        <span data-dot class="w-2 h-2 rounded-full bg-slate-500 opacity-50"></span>
                    @endfor
                </div>
                <button type="button" class="px-3 py-1.5 rounded-full bg-slate-800/70 border border-slate-700 hover:bg-slate-700 transition" data-next>
                    Next ›
                </button>
            </div>
        @endif
    </div>
    @endif
</x-card>

@push('scripts')
<script>
(function() {
    const slider = document.getElementById('{{ $sliderId ?? '' }}');
    if (!slider) return;
    const track = slider.querySelector('.leg-track');
    const slides = Array.from(slider.querySelectorAll('.leg-slide'));
    const dots = Array.from(slider.querySelectorAll('[data-dot]'));
    const prevBtn = slider.querySelector('[data-prev]');
    const nextBtn = slider.querySelector('[data-next]');

    if (!track || slides.length === 0) return;

    let idx = 0;

    const syncDots = () => dots.forEach((d, i) => d.classList.toggle('opacity-100', i === idx));

    const goTo = (i) => {
        idx = Math.max(0, Math.min(i, slides.length - 1));
        const target = slides[idx];
        if (target) {
            track.scrollTo({ left: target.offsetLeft, behavior: 'smooth' });
            syncDots();
        }
    };

    prevBtn?.addEventListener('click', () => goTo(idx - 1));
    nextBtn?.addEventListener('click', () => goTo(idx + 1));

    // Update active dot when user scrolls manually
    let ticking = false;
    track.addEventListener('scroll', () => {
        if (ticking) return;
        window.requestAnimationFrame(() => {
            let nearest = idx;
            let nearestDist = Math.abs(slides[idx].offsetLeft - track.scrollLeft);
            slides.forEach((s, i) => {
                const dist = Math.abs(s.offsetLeft - track.scrollLeft);
                if (dist < nearestDist) {
                    nearest = i;
                    nearestDist = dist;
                }
            });
            if (nearest !== idx) {
                idx = nearest;
                syncDots();
            }
            ticking = false;
        });
        ticking = true;
    });

    // Initialize
    syncDots();
})();
</script>
@endpush
