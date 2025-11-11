@extends('layouts.app', ['title' => 'Job Order Detail'])

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <x-button :href="route('job-orders.index')" variant="ghost" size="sm">
                ← Kembali
            </x-button>
        <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $job->job_number }}</h1>
                <p class="text-sm text-slate-600 dark:text-slate-400">{{ $job->customer->name }} • {{ strtoupper($job->service_type) }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <x-button :href="route('job-orders.edit', $job)" variant="outline" size="sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit
            </x-button>
            <form method="POST" action="{{ route('job-orders.destroy', $job) }}" onsubmit="return confirm('Yakin ingin menghapus job order ini?')">
                @csrf
                @method('DELETE')
                <x-button variant="danger" size="sm" type="submit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Hapus
                </x-button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Job Details --}}
        <div class="lg:col-span-1 space-y-6">
            <x-card title="Job Details">
                <div class="space-y-4">
                    <div>
                        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase mb-1">Current Status</div>
                        <x-badge :variant="match($job->status) {
                            'draft' => 'default',
                            'confirmed' => 'default',
                            'in_progress' => 'warning',
                            'completed' => 'success',
                            'cancelled' => 'danger',
                        }" class="text-base px-3 py-1">
                            {{ strtoupper(str_replace('_', ' ', $job->status)) }}
                        </x-badge>
                    </div>

                    <div class="pt-4 border-t border-slate-200 dark:border-slate-800">
                        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase mb-3">Order & Cargo Information</div>
                        <dl class="space-y-2">
                            <div class="flex gap-3">
                                <dt class="text-sm text-slate-500 dark:text-slate-400 w-28 shrink-0">Order ID:</dt>
                                <dd class="text-sm text-slate-900 dark:text-slate-100 flex-1">{{ $job->job_number }}</dd>
                            </div>
                            <div class="flex gap-3">
                                <dt class="text-sm text-slate-500 dark:text-slate-400 w-28 shrink-0">Customer:</dt>
                                <dd class="text-sm text-slate-900 dark:text-slate-100 flex-1">{{ $job->customer->name }}</dd>
                            </div>
                            <div class="flex gap-3">
                                <dt class="text-sm text-slate-500 dark:text-slate-400 w-28 shrink-0">Sales Agent:</dt>
                                <dd class="text-sm text-slate-900 dark:text-slate-100 flex-1">{{ $job->sales?->name ?? '-' }}</dd>
                            </div>
                            <div class="flex gap-3">
                                <dt class="text-sm text-slate-500 dark:text-slate-400 w-28 shrink-0">Origin:</dt>
                                <dd class="text-sm text-slate-900 dark:text-slate-100 flex-1">{{ $job->origin ?? '-' }}</dd>
                            </div>
                            <div class="flex gap-3">
                                <dt class="text-sm text-slate-500 dark:text-slate-400 w-28 shrink-0">Destination:</dt>
                                <dd class="text-sm text-slate-900 dark:text-slate-100 flex-1">{{ $job->destination ?? '-' }}</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Cargo Items --}}
                    @if($job->items->count() > 0)
                    <div class="pt-4 border-t border-slate-200 dark:border-slate-800">
                        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase mb-3">Cargo Items</div>
                        <div class="space-y-2">
                            @php
                                // Group items by cargo name and sum quantities
                                $groupedItems = $job->items->groupBy(fn($item) => $item->equipment?->name ?? $item->cargo_type)
                                    ->map(fn($group) => [
                                        'name' => $group->first()->equipment?->name ?? $group->first()->cargo_type,
                                        'quantity' => $group->sum('quantity')
                                    ]);
                            @endphp
                            @foreach($groupedItems as $item)
                            <div class="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-3">
                                <div class="font-medium text-slate-900 dark:text-slate-100 text-sm">
                                    {{ $item['name'] }}
                                </div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                    Qty: {{ $item['quantity'] }}
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Financial Summary --}}
                    <div class="pt-4 border-t border-slate-200 dark:border-slate-800">
                        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase mb-3">Financial Summary</div>
                        
                        {{-- Nilai Tagihan & Total Biaya (side by side) --}}
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            {{-- Nilai Tagihan --}}
                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-950/30 dark:to-blue-900/30 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                                <div class="text-xs font-medium text-blue-600 dark:text-blue-400 mb-2">Nilai Tagihan</div>
                                <div class="font-bold text-lg text-blue-700 dark:text-blue-300 break-words">
                                    Rp {{ number_format($job->invoice_amount + $job->total_billable, 0, ',', '.') }}
                                </div>
                                <div class="text-[10px] text-blue-600/70 dark:text-blue-400/70 mt-1.5 leading-tight">
                                    <div>Base: Rp {{ number_format($job->invoice_amount, 0, ',', '.') }}</div>
                                    <div>Billable: Rp {{ number_format($job->total_billable, 0, ',', '.') }}</div>
                                </div>
                            </div>

                            {{-- Total Biaya --}}
                            <div class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-950/30 dark:to-orange-900/30 rounded-lg p-4 border border-orange-200 dark:border-orange-800">
                                <div class="text-xs font-medium text-orange-600 dark:text-orange-400 mb-2">Total Biaya</div>
                                <div class="font-bold text-lg text-orange-700 dark:text-orange-300 break-words">
                                    Rp {{ number_format($job->total_cost, 0, ',', '.') }}
                                </div>
                                <div class="text-[10px] text-orange-600/70 dark:text-orange-400/70 mt-1.5 leading-tight">
                                    <div>Main: Rp {{ number_format($job->shipmentLegs->sum(fn($leg) => $leg->mainCost?->total ?? 0), 0, ',', '.') }}</div>
                                    <div>Add: Rp {{ number_format($job->shipmentLegs->sum(fn($leg) => $leg->additionalCosts->sum('amount')), 0, ',', '.') }}</div>
                                </div>
                            </div>
                        </div>

                        {{-- Estimasi Margin (full width) --}}
                        <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-950/30 dark:to-green-900/30 rounded-lg p-4 border border-green-200 dark:border-green-800">
                            <div class="flex items-end justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <div class="text-xs font-medium text-green-600 dark:text-green-400 mb-2">Estimasi Margin</div>
                                    <div class="font-bold text-lg text-green-700 dark:text-green-300 break-words">
                                        Rp {{ number_format($job->margin, 0, ',', '.') }}
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="font-bold text-2xl text-green-700 dark:text-green-300">
                                        {{ number_format($job->margin_percentage, 1) }}%
                                    </div>
                                    <div class="text-xs font-semibold text-green-600 dark:text-green-400">
                                        {{ $job->margin_percentage >= 50 ? 'Sangat Baik' : ($job->margin_percentage >= 30 ? 'Baik' : 'Perlu Perhatian') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
        </x-card>
        </div>

        {{-- Right: Shipment Legs --}}
        <div class="lg:col-span-2 space-y-6">
            <x-card>
                <x-slot:header>
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Shipment Legs</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Detail transportasi per segmen</p>
                        </div>
                        <x-button :href="route('job-orders.legs.create', $job)" variant="primary" size="sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add New Leg
                        </x-button>
                    </div>
                </x-slot:header>

                <div class="space-y-3">
                    @forelse($job->shipmentLegs as $leg)
                        {{-- Collapsible Leg Item --}}
                        <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden transition-all hover:shadow-md">
                            {{-- Leg Summary (Always Visible) --}}
                            <div class="p-4 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors" onclick="toggleLeg({{ $leg->id }})">
                                <div class="flex items-center gap-3">
                                    <button type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-transform leg-arrow" id="arrow-{{ $leg->id }}">
                                        ▶
                                    </button>
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-600 to-indigo-800 flex items-center justify-center text-white font-bold text-sm shrink-0">
                                        #{{ $leg->leg_number }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-semibold text-slate-900 dark:text-slate-100 text-sm">Leg #{{ $leg->leg_number }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $leg->leg_code }}</div>
                                        <div class="text-xs text-slate-600 dark:text-slate-300 mt-1">
                                            @if($leg->cost_category == 'trucking' && $leg->truck)
                                                {{ $leg->truck->plate_number }} • {{ $leg->driver?->name ?? '-' }}
                                            @elseif($leg->cost_category == 'vendor' && $leg->vendor)
                                                Vendor: {{ $leg->vendor->name }}
                                            @elseif($leg->cost_category == 'pelayaran')
                                                {{ $leg->vessel_name ?? 'Vessel' }} • {{ $leg->mainCost?->shipping_line ?? '-' }}
                                            @elseif($leg->cost_category == 'asuransi')
                                                🛡️ {{ $leg->mainCost?->insurance_provider ?? 'Insurance' }}
                                            @else
                                                {{ strtoupper($leg->cost_category) }}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <div class="font-bold text-slate-900 dark:text-slate-100 text-sm">
                                            Rp {{ number_format($leg->mainCost ? $leg->mainCost->total : 0, 0, ',', '.') }}
                                        </div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">Total</div>
                                        <div class="mt-1">
                                            <x-badge :variant="match($leg->status) {
                                                'pending' => 'default',
                                                'in_transit' => 'warning',
                                                'delivered' => 'success',
                                                'cancelled' => 'danger',
                                            }" class="text-xs">
                                                {{ strtoupper(str_replace('_', ' ', $leg->status)) }}
                                            </x-badge>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Leg Details (Collapsible) --}}
                            <div id="leg-{{ $leg->id }}" class="hidden border-t border-slate-200 dark:border-slate-800">
                                <div class="p-5 bg-slate-50/50 dark:bg-slate-800/20">
                                    {{-- Leg Info Grid --}}
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                        <div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400">Vendor/Executor</div>
                                            <div class="font-medium text-slate-900 dark:text-slate-100 text-sm">
                                                @if($leg->executor_type == 'vendor' && $leg->vendor)
                                                    {{ $leg->vendor->name }}
                                                @elseif($leg->executor_type == 'own_fleet')
                                                    Own Fleet
                                                @else
                                                    -
                                                @endif
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400">Vehicle/Vessel</div>
                                            <div class="font-medium text-slate-900 dark:text-slate-100 text-sm">
                                                {{ $leg->truck?->plate_number ?? $leg->vessel_name ?? '-' }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400">Driver/PIC</div>
                                            <div class="font-medium text-slate-900 dark:text-slate-100 text-sm">{{ $leg->driver?->name ?? '-' }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400">Quantity</div>
                                            <div class="font-medium text-slate-900 dark:text-slate-100 text-sm">{{ $leg->quantity }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400">Load Date</div>
                                            <div class="font-medium text-slate-900 dark:text-slate-100 text-sm">{{ $leg->load_date->format('d/m/Y') }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400">Unload Date</div>
                                            <div class="font-medium text-slate-900 dark:text-slate-100 text-sm">{{ $leg->unload_date->format('d/m/Y') }}</div>
                                        </div>
                                        @if($leg->serial_numbers)
                                        <div class="md:col-span-2">
                                            <div class="text-xs text-slate-500 dark:text-slate-400">S/N</div>
                                            <div class="font-medium text-slate-900 dark:text-slate-100 text-sm">{{ $leg->serial_numbers }}</div>
                                        </div>
                                        @endif
                                    </div>

                                    {{-- Main Costs --}}
                                    @if($leg->mainCost)
                                    <div class="bg-indigo-50 dark:bg-indigo-950/20 rounded-lg p-4 mb-3">
                                        <div class="font-semibold text-indigo-900 dark:text-indigo-100 mb-3 text-sm flex items-center justify-between">
                                            <span>MAIN COSTS</span>
                                            <span class="text-lg">Rp {{ number_format($leg->mainCost->total, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
                                            @if($leg->mainCost->vendor_cost > 0)
                                            <div>
                                                <div class="text-xs text-indigo-600 dark:text-indigo-400">Vendor Cost</div>
                                                <div class="font-medium text-indigo-900 dark:text-indigo-100">Rp {{ number_format($leg->mainCost->vendor_cost, 0, ',', '.') }}</div>
                                            </div>
                                            @endif
                                            @if($leg->mainCost->freight_cost > 0)
                                            <div>
                                                <div class="text-xs text-indigo-600 dark:text-indigo-400">Freight Cost</div>
                                                <div class="font-medium text-indigo-900 dark:text-indigo-100">Rp {{ number_format($leg->mainCost->freight_cost, 0, ',', '.') }}</div>
                                            </div>
                                            @endif
                                            @if($leg->mainCost->ppn > 0)
                                            <div>
                                                <div class="text-xs text-indigo-600 dark:text-indigo-400">+ PPN 11%</div>
                                                <div class="font-medium text-indigo-900 dark:text-indigo-100">Rp {{ number_format($leg->mainCost->ppn, 0, ',', '.') }}</div>
                                            </div>
                                            @endif
                                            @if($leg->mainCost->pph23 > 0)
                                            <div>
                                                <div class="text-xs text-rose-600 dark:text-rose-400">- PPH 23 (Dipotong)</div>
                                                <div class="font-medium text-rose-900 dark:text-rose-100">Rp {{ number_format($leg->mainCost->pph23, 0, ',', '.') }}</div>
                                            </div>
                                            @endif
                                            @if($leg->mainCost->uang_jalan > 0)
                                            <div>
                                                <div class="text-xs text-indigo-600 dark:text-indigo-400">Uang Jalan</div>
                                                <div class="font-medium text-indigo-900 dark:text-indigo-100">Rp {{ number_format($leg->mainCost->uang_jalan, 0, ',', '.') }}</div>
                                            </div>
                                            @endif
                                            @if($leg->mainCost->bbm > 0)
                                            <div>
                                                <div class="text-xs text-indigo-600 dark:text-indigo-400">BBM</div>
                                                <div class="font-medium text-indigo-900 dark:text-indigo-100">Rp {{ number_format($leg->mainCost->bbm, 0, ',', '.') }}</div>
                                            </div>
                                            @endif
                                            @if($leg->mainCost->toll > 0)
                                            <div>
                                                <div class="text-xs text-indigo-600 dark:text-indigo-400">Toll</div>
                                                <div class="font-medium text-indigo-900 dark:text-indigo-100">Rp {{ number_format($leg->mainCost->toll, 0, ',', '.') }}</div>
                                            </div>
                                            @endif
                                            @if($leg->mainCost->shipping_line)
                                            <div>
                                                <div class="text-xs text-indigo-600 dark:text-indigo-400">Shipping Line</div>
                                                <div class="font-medium text-indigo-900 dark:text-indigo-100">{{ $leg->mainCost->shipping_line }}</div>
                                            </div>
                                            @endif
                                            @if($leg->mainCost->insurance_provider)
                                            <div>
                                                <div class="text-xs text-indigo-600 dark:text-indigo-400">Perusahaan Asuransi</div>
                                                <div class="font-medium text-indigo-900 dark:text-indigo-100">{{ $leg->mainCost->insurance_provider }}</div>
                                            </div>
                                            @endif
                                            @if($leg->mainCost->policy_number)
                                            <div>
                                                <div class="text-xs text-indigo-600 dark:text-indigo-400">Nomor Polis</div>
                                                <div class="font-medium text-indigo-900 dark:text-indigo-100">{{ $leg->mainCost->policy_number }}</div>
                                            </div>
                                            @endif
                                            @if($leg->mainCost->insured_value > 0)
                                            <div>
                                                <div class="text-xs text-indigo-600 dark:text-indigo-400">Nilai Pertanggungan</div>
                                                <div class="font-medium text-indigo-900 dark:text-indigo-100">Rp {{ number_format($leg->mainCost->insured_value, 0, ',', '.') }}</div>
                                            </div>
                                            @endif
                                            @if($leg->mainCost->premium_rate > 0)
                                            <div>
                                                <div class="text-xs text-indigo-600 dark:text-indigo-400">Rate Premi</div>
                                                <div class="font-medium text-indigo-900 dark:text-indigo-100">{{ number_format($leg->mainCost->premium_rate, 2) }}%</div>
                                            </div>
                                            @endif
                                            @if($leg->mainCost->admin_fee > 0)
                                            <div>
                                                <div class="text-xs text-indigo-600 dark:text-indigo-400">Biaya Admin</div>
                                                <div class="font-medium text-indigo-900 dark:text-indigo-100">Rp {{ number_format($leg->mainCost->admin_fee, 0, ',', '.') }}</div>
                                            </div>
                                            @endif
                                            @if($leg->mainCost->premium_cost > 0)
                                            <div>
                                                <div class="text-xs text-indigo-600 dark:text-indigo-400">Premi yang Dibayar</div>
                                                <div class="font-medium text-indigo-900 dark:text-indigo-100">Rp {{ number_format($leg->mainCost->premium_cost, 0, ',', '.') }}</div>
                                            </div>
                                            @endif
                                            @if($leg->mainCost->billable_rate > 0)
                                            <div>
                                                <div class="text-xs text-blue-600 dark:text-blue-400">Rate untuk Customer</div>
                                                <div class="font-medium text-blue-900 dark:text-blue-100">{{ number_format($leg->mainCost->billable_rate, 2) }}%</div>
                                            </div>
                                            @endif
                                            @if($leg->mainCost->premium_billable > 0)
                                            <div>
                                                <div class="text-xs text-blue-600 dark:text-blue-400">Premi yang Ditagihkan</div>
                                                <div class="font-medium text-blue-900 dark:text-blue-100">Rp {{ number_format($leg->mainCost->premium_billable, 0, ',', '.') }}</div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    @endif

                                    {{-- Additional Costs --}}
                                    <div class="bg-emerald-50 dark:bg-emerald-950/20 rounded-lg p-4 mb-3">
                                        <div class="font-semibold text-emerald-900 dark:text-emerald-100 mb-2 text-sm flex items-center justify-between">
                                            <span>ADDITIONAL COSTS</span>
                                            <button 
                                                type="button" 
                                                onclick="openAddCostModal({{ $leg->id }}, {{ $leg->leg_number }})"
                                                class="text-xs px-2 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md transition-colors flex items-center gap-1"
                                            >
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                </svg>
                                                Add Cost
                                            </button>
                                        </div>
                                        @if($leg->additionalCosts->count() > 0)
                                        <div class="space-y-2">
                                            @foreach($leg->additionalCosts as $cost)
                                            <div class="flex items-center justify-between gap-3 text-sm bg-white dark:bg-slate-800 rounded p-3 group hover:shadow-sm transition-shadow">
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-medium text-emerald-900 dark:text-emerald-100">{{ $cost->cost_type }}</span>
                                                        @if($cost->is_billable)
                                                            <span class="text-xs px-1.5 py-0.5 bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded">Billable</span>
                                                        @endif
                                                    </div>
                                                    @if($cost->description)
                                                        <div class="text-xs text-emerald-600 dark:text-emerald-400 mt-0.5">{{ $cost->description }}</div>
                                                    @endif
                                                    @if($cost->is_billable && $cost->billable_amount != $cost->amount)
                                                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                                            Cost: Rp {{ number_format($cost->amount, 0, ',', '.') }} → Bill: Rp {{ number_format($cost->billable_amount, 0, ',', '.') }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    <div class="text-right">
                                                        <div class="font-medium text-emerald-900 dark:text-emerald-100">Rp {{ number_format($cost->amount, 0, ',', '.') }}</div>
                                                        @if($cost->is_billable && $cost->billable_amount != $cost->amount)
                                                            <div class="text-xs text-blue-600 dark:text-blue-400">Bill: Rp {{ number_format($cost->billable_amount, 0, ',', '.') }}</div>
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                        <button 
                                                            type="button"
                                                            onclick="openEditCostModal({{ $cost->id }}, '{{ $cost->cost_type }}', '{{ $cost->description }}', {{ $cost->amount }}, {{ $cost->is_billable ? 'true' : 'false' }}, {{ $cost->billable_amount ?? 0 }}, {{ $leg->id }}, {{ $leg->leg_number }})"
                                                            class="p-1.5 text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/30 rounded transition-colors"
                                                            title="Edit"
                                                        >
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                            </svg>
                                                        </button>
                                                        <form method="POST" action="{{ route('additional-costs.destroy', $cost) }}" onsubmit="return confirm('Hapus biaya ini?')" class="inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button 
                                                                type="submit"
                                                                class="p-1.5 text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 rounded transition-colors"
                                                                title="Hapus"
                                                            >
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                </svg>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                        @else
                                        <div class="text-sm text-emerald-600 dark:text-emerald-400 text-center py-2">No additional costs</div>
                                        @endif
                                    </div>

                                    {{-- Actions --}}
                                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200 dark:border-slate-800">
                                        <x-button :href="route('job-orders.legs.edit', [$job, $leg])" variant="outline" size="sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit Details
                                        </x-button>
                                        <form method="POST" action="{{ route('job-orders.legs.destroy', [$job, $leg]) }}" onsubmit="return confirm('Hapus leg ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <x-button variant="ghost" size="sm" type="submit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </x-button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <span class="text-6xl">🚛</span>
                            <p class="mt-4 text-slate-600 dark:text-slate-400">Belum ada shipment legs</p>
                            <p class="text-sm text-slate-500 dark:text-slate-500 mt-1">Klik "Add New Leg" untuk menambahkan</p>
                        </div>
                    @endforelse
            </div>
        </x-card>
        </div>
    </div>
</div>

{{-- Modal Add Additional Cost --}}
<div id="addCostModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <form method="POST" id="addCostForm" class="p-6 space-y-4">
            @csrf
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Add Additional Cost</h3>
                <button type="button" onclick="closeAddCostModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <div class="text-sm text-slate-500 dark:text-slate-400 mb-4" id="modalLegInfo">
                For Leg #<span id="modalLegNumber"></span> (Job: {{ $job->job_number }})
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Payee / Person in Charge</label>
                <input 
                    type="text" 
                    name="cost_type" 
                    required
                    placeholder="e.g., Eko (Sopir), Budi (Kawalan)"
                    class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Description</label>
                <input 
                    type="text" 
                    name="description" 
                    placeholder="e.g., Uang Inap, Biaya Kawalan, Sewa Crane"
                    class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Amount (IDR)</label>
                <input 
                    type="text" 
                    id="amount_display"
                    placeholder="e.g., 150000"
                    class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
                >
                <input type="hidden" name="amount" id="amount_input" required>
            </div>

            <div class="flex items-center gap-2">
                <input 
                    type="checkbox" 
                    name="is_billable" 
                    id="is_billable"
                    value="1"
                    onchange="toggleBillableAmount()"
                    class="w-4 h-4 text-indigo-600 bg-white dark:bg-slate-900 border-slate-300 rounded focus:ring-indigo-500"
                >
                <label for="is_billable" class="text-sm font-medium text-slate-700 dark:text-slate-300">Billable to Customer</label>
            </div>

            <div id="billable_amount_field" class="hidden">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Billable Amount (IDR)</label>
                <input 
                    type="text" 
                    id="billable_amount_display"
                    placeholder="e.g., 165000"
                    class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
                >
                <input type="hidden" name="billable_amount" id="billable_amount_input">
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">Jumlah yang akan ditagihkan ke customer (default sama dengan amount)</p>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button 
                    type="button" 
                    onclick="closeAddCostModal()"
                    class="px-4 py-2 rounded-lg bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors"
                >
                    Cancel
                </button>
                <button 
                    type="submit"
                    class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition-colors"
                >
                    Save Cost
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Additional Cost --}}
<div id="editCostModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <form method="POST" id="editCostForm" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Edit Additional Cost</h3>
                <button type="button" onclick="closeEditCostModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <div class="text-sm text-slate-500 dark:text-slate-400 mb-4" id="editModalLegInfo">
                For Leg #<span id="editModalLegNumber"></span> (Job: {{ $job->job_number }})
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Payee / Person in Charge</label>
                <input 
                    type="text" 
                    name="cost_type"
                    id="edit_cost_type"
                    required
                    placeholder="e.g., Eko (Sopir), Budi (Kawalan)"
                    class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Description</label>
                <input 
                    type="text" 
                    name="description"
                    id="edit_description"
                    placeholder="e.g., Uang Inap, Biaya Kawalan, Sewa Crane"
                    class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Amount (IDR)</label>
                <input 
                    type="text" 
                    id="edit_amount_display"
                    placeholder="e.g., 150000"
                    class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
                >
                <input type="hidden" name="amount" id="edit_amount_input" required>
            </div>

            <div class="flex items-center gap-2">
                <input 
                    type="checkbox" 
                    name="is_billable" 
                    id="edit_is_billable"
                    value="1"
                    onchange="toggleEditBillableAmount()"
                    class="w-4 h-4 text-indigo-600 bg-white dark:bg-slate-900 border-slate-300 rounded focus:ring-indigo-500"
                >
                <label for="edit_is_billable" class="text-sm font-medium text-slate-700 dark:text-slate-300">Billable to Customer</label>
            </div>

            <div id="edit_billable_amount_field" class="hidden">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Billable Amount (IDR)</label>
                <input 
                    type="text" 
                    id="edit_billable_amount_display"
                    placeholder="e.g., 165000"
                    class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
                >
                <input type="hidden" name="billable_amount" id="edit_billable_amount_input">
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">Jumlah yang akan ditagihkan ke customer (default sama dengan amount)</p>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button 
                    type="button" 
                    onclick="closeEditCostModal()"
                    class="px-4 py-2 rounded-lg bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors"
                >
                    Cancel
                </button>
                <button 
                    type="submit"
                    class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition-colors"
                >
                    Update Cost
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleLeg(legId) {
    const detailsDiv = document.getElementById('leg-' + legId);
    const arrow = document.getElementById('arrow-' + legId);
    
    if (detailsDiv.classList.contains('hidden')) {
        // Expand
        detailsDiv.classList.remove('hidden');
        arrow.style.transform = 'rotate(90deg)';
    } else {
        // Collapse
        detailsDiv.classList.add('hidden');
        arrow.style.transform = 'rotate(0deg)';
    }
}

let currentLegId = null;

function openAddCostModal(legId, legNumber) {
    currentLegId = legId;
    const modal = document.getElementById('addCostModal');
    const form = document.getElementById('addCostForm');
    
    document.getElementById('modalLegNumber').textContent = legNumber;
    form.action = `/legs/${legId}/additional-costs`;
    
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeAddCostModal() {
    const modal = document.getElementById('addCostModal');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
    
    // Reset form
    document.getElementById('addCostForm').reset();
    document.getElementById('amount_display').value = '';
    document.getElementById('amount_input').value = '';
    document.getElementById('billable_amount_display').value = '';
    document.getElementById('billable_amount_input').value = '';
    document.getElementById('billable_amount_field').classList.add('hidden');
    document.getElementById('is_billable').checked = false;
}

function toggleBillableAmount() {
    const checkbox = document.getElementById('is_billable');
    const field = document.getElementById('billable_amount_field');
    
    if (checkbox.checked) {
        field.classList.remove('hidden');
        // Auto-fill billable_amount with amount if empty
        const amount = parseFloat(document.getElementById('amount_input').value) || 0;
        if (amount > 0 && !document.getElementById('billable_amount_input').value) {
            document.getElementById('billable_amount_display').value = formatNumber(amount);
            document.getElementById('billable_amount_input').value = amount;
        }
    } else {
        field.classList.add('hidden');
        document.getElementById('billable_amount_display').value = '';
        document.getElementById('billable_amount_input').value = '';
    }
}

// Format number with thousand separator
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function parseNumber(str) {
    return parseFloat(str.replace(/\./g, '')) || 0;
}

// Setup formatted input for amount
const amountDisplay = document.getElementById('amount_display');
const amountInput = document.getElementById('amount_input');

if (amountDisplay && amountInput) {
    amountDisplay.addEventListener('input', function() {
        let value = this.value.replace(/\./g, '');
        value = value.replace(/[^\d]/g, '');
        
        if (value) {
            this.value = formatNumber(value);
            amountInput.value = value;
            
            // Auto-update billable_amount if checkbox is checked and billable_amount is empty
            if (document.getElementById('is_billable').checked && !document.getElementById('billable_amount_input').value) {
                document.getElementById('billable_amount_display').value = formatNumber(value);
                document.getElementById('billable_amount_input').value = value;
            }
        } else {
            this.value = '';
            amountInput.value = '';
        }
    });
}

// Setup formatted input for billable_amount
const billableAmountDisplay = document.getElementById('billable_amount_display');
const billableAmountInput = document.getElementById('billable_amount_input');

if (billableAmountDisplay && billableAmountInput) {
    billableAmountDisplay.addEventListener('input', function() {
        let value = this.value.replace(/\./g, '');
        value = value.replace(/[^\d]/g, '');
        
        if (value) {
            this.value = formatNumber(value);
            billableAmountInput.value = value;
        } else {
            this.value = '';
            billableAmountInput.value = '';
        }
    });
}

// Close modal on outside click
document.getElementById('addCostModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeAddCostModal();
    }
});

// Edit Modal Functions
let currentEditCostId = null;

function openEditCostModal(costId, costType, description, amount, isBillable, billableAmount, legId, legNumber) {
    currentEditCostId = costId;
    const modal = document.getElementById('editCostModal');
    const form = document.getElementById('editCostForm');
    
    document.getElementById('editModalLegNumber').textContent = legNumber;
    form.action = `/additional-costs/${costId}`;
    
    // Fill form with existing values
    document.getElementById('edit_cost_type').value = costType;
    document.getElementById('edit_description').value = description || '';
    document.getElementById('edit_amount_display').value = formatNumber(amount);
    document.getElementById('edit_amount_input').value = amount;
    
    const billableCheckbox = document.getElementById('edit_is_billable');
    billableCheckbox.checked = isBillable;
    
    if (isBillable) {
        document.getElementById('edit_billable_amount_field').classList.remove('hidden');
        document.getElementById('edit_billable_amount_display').value = formatNumber(billableAmount);
        document.getElementById('edit_billable_amount_input').value = billableAmount;
    } else {
        document.getElementById('edit_billable_amount_field').classList.add('hidden');
    }
    
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeEditCostModal() {
    const modal = document.getElementById('editCostModal');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
    
    // Reset form
    document.getElementById('editCostForm').reset();
    document.getElementById('edit_amount_display').value = '';
    document.getElementById('edit_amount_input').value = '';
    document.getElementById('edit_billable_amount_display').value = '';
    document.getElementById('edit_billable_amount_input').value = '';
    document.getElementById('edit_billable_amount_field').classList.add('hidden');
}

function toggleEditBillableAmount() {
    const checkbox = document.getElementById('edit_is_billable');
    const field = document.getElementById('edit_billable_amount_field');
    
    if (checkbox.checked) {
        field.classList.remove('hidden');
        const amount = parseFloat(document.getElementById('edit_amount_input').value) || 0;
        if (amount > 0 && !document.getElementById('edit_billable_amount_input').value) {
            document.getElementById('edit_billable_amount_display').value = formatNumber(amount);
            document.getElementById('edit_billable_amount_input').value = amount;
        }
    } else {
        field.classList.add('hidden');
        document.getElementById('edit_billable_amount_display').value = '';
        document.getElementById('edit_billable_amount_input').value = '';
    }
}

// Setup formatted input for edit modal amount
const editAmountDisplay = document.getElementById('edit_amount_display');
const editAmountInput = document.getElementById('edit_amount_input');

if (editAmountDisplay && editAmountInput) {
    editAmountDisplay.addEventListener('input', function() {
        let value = this.value.replace(/\./g, '');
        value = value.replace(/[^\d]/g, '');
        
        if (value) {
            this.value = formatNumber(value);
            editAmountInput.value = value;
            
            if (document.getElementById('edit_is_billable').checked && !document.getElementById('edit_billable_amount_input').value) {
                document.getElementById('edit_billable_amount_display').value = formatNumber(value);
                document.getElementById('edit_billable_amount_input').value = value;
            }
        } else {
            this.value = '';
            editAmountInput.value = '';
        }
    });
}

// Setup formatted input for edit modal billable_amount
const editBillableAmountDisplay = document.getElementById('edit_billable_amount_display');
const editBillableAmountInput = document.getElementById('edit_billable_amount_input');

if (editBillableAmountDisplay && editBillableAmountInput) {
    editBillableAmountDisplay.addEventListener('input', function() {
        let value = this.value.replace(/\./g, '');
        value = value.replace(/[^\d]/g, '');
        
        if (value) {
            this.value = formatNumber(value);
            editBillableAmountInput.value = value;
        } else {
            this.value = '';
            editBillableAmountInput.value = '';
        }
    });
}

// Close edit modal on outside click
document.getElementById('editCostModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditCostModal();
    }
});
</script>
@endsection
