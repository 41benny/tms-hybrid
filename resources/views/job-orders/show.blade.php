@extends('layouts.app', ['title' => 'Job Order Detail'])

@section('content')
@php
    $isSalesUser = auth()->check() && auth()->user()->role === \App\Models\User::ROLE_SALES;
@endphp
<div class="space-y-6">
    {{-- Header Card --}}
    <x-card>
        <x-slot:header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3 jo-header-actions">
                    <x-button :href="route('job-orders.index')" variant="ghost" size="sm" class="jo-action-btn jo-action-back">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali
                    </x-button>
                    <div>
                        <div class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $job->job_number }}</div>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">{{ $job->customer->name }} • {{ strtoupper($job->service_type) }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 jo-header-actions">
                    @if(!$job->isLocked())
                        <x-button :href="route('job-orders.edit', $job)" variant="outline" size="sm" class="jo-action-btn jo-action-edit transition-all duration-200 border-transparent hover:shadow-md hover:shadow-indigo-500/20 hover:border-indigo-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit
                        </x-button>
                    @endif

                    {{-- Cancel Order - untuk semua user (kecuali yang sudah locked) --}}
                    @if(!$job->isLocked())
                        <x-button
                            type="button"
                            onclick="openCancelModal()"
                            variant="danger"
                            size="sm"
                            class="jo-action-btn jo-action-cancel transition-all duration-200 hover:shadow-md hover:shadow-red-500/25 hover:border-red-600"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Cancel Order
                        </x-button>
                    @endif

                    {{-- Delete Order - HANYA untuk superadmin --}}
                    @if(auth()->user()->isSuperAdmin() && !$job->isLocked())
                        <form method="POST" action="{{ route('job-orders.destroy', $job) }}" onsubmit="return confirm('⚠️ PERINGATAN: Menghapus Job Order akan menghapus semua data terkait (Shipment Legs, Driver Advances, dll). Yakin ingin menghapus?')" class="inline">
                            @csrf
                            @method('DELETE')
                            <x-button variant="ghost" size="sm" type="submit" class="transition-all duration-200 hover:shadow-md hover:shadow-slate-500/15 hover:border-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Hapus
                            </x-button>
                        </form>
                    @endif
                </div>
            </div>
        </x-slot:header>
    </x-card>

    @php
        $leftColumnClass = $isSalesUser ? 'hidden lg:block lg:col-span-1' : 'lg:col-span-1';
        $rightColumnClass = $isSalesUser ? 'col-span-1 lg:col-span-2' : 'lg:col-span-2';
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Job Details --}}
        <div class="{{ $leftColumnClass }} space-y-6">
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

                    {{-- Invoice Status --}}
                    <div class="pt-4 border-t border-slate-200 dark:border-slate-800">
                        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase mb-1">Status Invoice</div>
                        @php
                            $invoiceStatus = $job->invoice_status;
                        @endphp
                        @if($invoiceStatus === 'not_invoiced')
                            <x-badge variant="danger" class="text-base px-3 py-1">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                Belum Diinvoice
                            </x-badge>
                            <div class="mt-2">
                                <x-button :href="route('invoices.create', ['job_order_id' => $job->id])" variant="primary" size="sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Buat Invoice
                                </x-button>
                            </div>
                        @elseif($invoiceStatus === 'partially_invoiced')
                            <x-badge variant="warning" class="text-base px-3 py-1">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Sebagian
                            </x-badge>
                            <div class="mt-2 text-xs text-slate-600 dark:text-slate-400">
                                <div>Sudah Diinvoice: <span class="font-semibold text-slate-900 dark:text-slate-100">Rp {{ number_format($job->total_invoiced, 0, ',', '.') }}</span></div>
                                <div>Sisa: <span class="font-semibold text-orange-600 dark:text-orange-400">Rp {{ number_format($job->uninvoiced_amount, 0, ',', '.') }}</span></div>
                            </div>
                            <div class="mt-2">
                                <x-button :href="route('invoices.create', ['job_order_id' => $job->id])" variant="primary" size="sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Buat Invoice Lagi
                                </x-button>
                            </div>
                        @else
                            <x-badge variant="success" class="text-base px-3 py-1">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Sudah Diinvoice Penuh
                            </x-badge>
                            <div class="mt-2 text-xs text-slate-600 dark:text-slate-400">
                                Total: <span class="font-semibold text-green-600 dark:text-green-400">Rp {{ number_format($job->total_invoiced, 0, ',', '.') }}</span>
                            </div>
                        @endif
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
                        @php
                            $mainDppSum = $job->shipmentLegs->sum(function ($leg) {
                                $main = $leg->mainCost;
                                $cat = $leg->cost_category;
                                return match ($cat) {
                                    'vendor' => (float) ($main->vendor_cost ?? 0),
                                    'pelayaran' => (float) ($main->freight_cost ?? 0),
                                    'trucking' => (float) (($main->uang_jalan ?? 0) + ($main->bbm ?? 0) + ($main->toll ?? 0) + ($main->other_costs ?? 0)),
                                    'asuransi' => (float) (($main->premium_cost ?? 0) + ($main->admin_fee ?? 0)),
                                    'pic' => (float) ($main->pic_amount ?? 0),
                                    default => (float) ($main->vendor_cost ?? 0),
                                };
                            });
                            $ppnNonCredSum = $job->shipmentLegs->sum(function ($leg) {
                                $main = $leg->mainCost;
                                return ($main && $main->ppn_noncreditable) ? (float) ($main->ppn ?? 0) : 0;
                            });
                            $additionalSum = $job->shipmentLegs->sum(fn($leg) => $leg->additionalCosts->sum('amount'));
                        @endphp
                        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase mb-3">Financial Summary</div>

                        {{-- Nilai Tagihan & Total Biaya (side by side) --}}
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            {{-- Nilai Tagihan --}}
                            <div class="glass-blue rounded-lg p-4">
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
                            <div class="glass-orange rounded-lg p-4">
                                <div class="text-xs font-medium text-orange-600 dark:text-orange-400 mb-2">Total Biaya</div>
                                <div class="font-bold text-lg text-orange-700 dark:text-orange-300 break-words">
                                    Rp {{ number_format($job->total_cost_dpp, 0, ',', '.') }}
                                </div>
                                <div class="text-[10px] text-orange-600/70 dark:text-orange-400/70 mt-1.5 leading-tight">
                                    <div>Main: Rp {{ number_format($mainDppSum + $ppnNonCredSum, 0, ',', '.') }}</div>
                                    @if($ppnNonCredSum > 0)
                                        <div>PPN non kredit: Rp {{ number_format($ppnNonCredSum, 0, ',', '.') }}</div>
                                    @endif
                                    <div>Add: Rp {{ number_format($additionalSum, 0, ',', '.') }}</div>
                                </div>
                            </div>
                        </div>

                        {{-- Estimasi Margin (full width) --}}
                        <div class="glass-green rounded-lg p-4">
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

                    {{-- Invoices --}}
                    @if($job->invoices->count() > 0)
                    <div class="pt-4 border-t border-slate-200 dark:border-slate-800">
                        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase mb-3">
                            Invoice History ({{ $job->invoices->count() }})
                        </div>
                        <div class="space-y-2">
                            @foreach($job->invoices->sortByDesc('invoice_date') as $invoice)
                            <a href="{{ route('invoices.show', $invoice) }}" class="block bg-slate-50 dark:bg-slate-800/50 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg p-3 transition-colors border border-slate-200 dark:border-slate-700">
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <div class="font-medium text-slate-900 dark:text-slate-100 text-sm">
                                        {{ $invoice->invoice_number }}
                                    </div>
                                    <x-badge :variant="match($invoice->status) {
                                        'draft' => 'default',
                                        'sent' => 'warning',
                                        'paid' => 'success',
                                        'partial' => 'warning',
                                        'overdue' => 'danger',
                                        'cancelled' => 'danger',
                                        default => 'default'
                                    }" size="sm">
                                        {{ strtoupper($invoice->status) }}
                                    </x-badge>
                                </div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 space-y-1">
                                    <div>Tanggal: {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</div>
                                    <div class="font-semibold text-slate-700 dark:text-slate-300">
                                        Total: Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                                    </div>
                                    @if($invoice->paid_amount > 0)
                                        <div class="text-green-600 dark:text-green-400">
                                            Dibayar: Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}
                                        </div>
                                    @endif
                                </div>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Cancel Information --}}
                    @if($job->status === 'cancelled')
                    <div class="pt-4 border-t border-slate-200 dark:border-slate-800">
                        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase mb-2">Cancel Information</div>
                        <div class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 rounded-lg p-3">
                            <div class="text-sm text-red-900 dark:text-red-100 font-medium mb-1">Alasan Cancel:</div>
                            <div class="text-sm text-red-800 dark:text-red-200">{{ $job->cancel_reason }}</div>
                            @if($job->cancelled_at)
                                <div class="text-xs text-red-600 dark:text-red-400 mt-2">
                                    Dibatalkan pada: {{ $job->cancelled_at->format('d M Y H:i') }}
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif
            </div>
        </x-card>
        </div>

        {{-- Right: Shipment Legs --}}
        <div class="{{ $rightColumnClass }} space-y-6">
            @if($isSalesUser)
                @include('job-orders.partials.legs-sales-mobile', ['job' => $job])
            @endif

            {{-- Tampilkan layout lama hanya di desktop (sales) atau semua role non-sales --}}
            <div class="@if($isSalesUser) hidden lg:block @endif">
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
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div class="shrink-0 rounded-md p-[1px] bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500">
                                        <div class="px-2.5 py-1 rounded-[5px] bg-white dark:bg-slate-900">
                                            <span class="text-xs font-bold text-purple-600 dark:text-purple-400">#{{ $leg->leg_number }}</span>
                                        </div>
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
                                                <span class="inline-flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                    </svg>
                                                    {{ $leg->mainCost?->insurance_provider ?? 'Insurance' }}
                                                </span>
                                            @elseif($leg->cost_category == 'pic')
                                                <span class="inline-flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                    </svg>
                                                    PIC: {{ $leg->mainCost?->pic_name ?? '-' }} ({{ ucfirst($leg->mainCost?->cost_type ?? 'fee') }})
                                                </span>
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

                                          @if($leg->cost_category === 'trucking' && $leg->executor_type === 'own_fleet')
                                              <div class="mt-2 flex justify-end">
                                                  <a
                                                      href="{{ route('legs.print-trucking', $leg) }}"
                                                      target="_blank"
                                                      class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-full text-[11px] font-medium bg-white dark:bg-slate-900 border border-indigo-300 dark:border-indigo-600 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-950/30 hover:border-indigo-400 dark:hover:border-indigo-500 shadow-sm hover:shadow-md transition-all"
                                                  >
                                                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                      </svg>
                                                      Print SPK Uang Jalan
                                                  </a>
                                              </div>
                                          @endif

                                        @php
                                            $driverAdvance = $leg->driverAdvance;
                                            $advanceStatus = $driverAdvance?->status;
                                            $canRequestDp = $driverAdvance && $advanceStatus === 'pending';
                                            $canRequestSettlement = $driverAdvance && $advanceStatus === 'dp_paid';
                                        @endphp

                                        @if($isSalesUser && ($canRequestDp || $canRequestSettlement))
                                            <div class="mt-2 flex justify-end">
                                                <a
                                                    href="{{ $canRequestDp
                                                        ? route('payment-requests.create', ['driver_advance_id' => $driverAdvance->id])
                                                        : route('payment-requests.create', ['driver_advance_id' => $driverAdvance->id, 'type' => 'settlement']) }}"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-medium bg-cyan-600 hover:bg-cyan-500 text-white shadow-sm"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                    </svg>
                                                    {{ $canRequestDp ? 'Ajukan DP' : 'Ajukan Pelunasan' }}
                                                </a>
                                            </div>
                                        @endif

                                        @php
                                            $canQuickVendor = $isSalesUser
                                                && in_array($leg->cost_category, ['vendor', 'pelayaran', 'asuransi', 'pic'], true)
                                                && $leg->vendor_id
                                                && $leg->mainCost
                                                && $leg->total_cost > 0
                                                && $leg->vendorBillItems->isEmpty();
                                        @endphp

                                        @if($canQuickVendor)
                                            <div class="mt-2 flex justify-end">
                                                <form method="POST" action="{{ route('legs.sales-quick-vendor-request', $leg) }}">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-medium bg-emerald-600 hover:bg-emerald-500 text-white shadow-sm">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                        </svg>
                                                        Ajukan Vendor
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Leg Details (Collapsible) --}}
                            <div id="leg-{{ $leg->id }}" class="hidden leg-details-bg">
                                <div class="p-5">
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
                                            <div class="text-xs text-slate-500 dark:text-slate-400">Rekening Vendor</div>
                                            @if($leg->vendor)
                                                @php
                                                    $primaryAccount = $leg->vendor->activeBankAccounts->firstWhere('is_primary', true) ?? $leg->vendor->activeBankAccounts->first();
                                                @endphp
                                                @if($primaryAccount)
                                                    <div class="font-medium text-slate-900 dark:text-slate-100 text-sm">{{ $primaryAccount->bank_name }} - {{ $primaryAccount->account_number }}</div>
                                                    <div class="text-[11px] text-slate-500 dark:text-slate-400">a.n. {{ $primaryAccount->account_holder_name }}</div>
                                                @else
                                                    <div class="text-sm text-slate-500 dark:text-slate-400">Belum ada rekening</div>
                                                @endif
                                            @else
                                                <div class="text-sm text-slate-500 dark:text-slate-400">-</div>
                                            @endif
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
                                            <div class="font-medium text-slate-900 dark:text-slate-100 text-sm">
                                                {{ optional($leg->unload_date)->format('d/m/Y') ?: '-' }}
                                            </div>
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
                                    <div class="glass-panel rounded-lg p-4 mb-3">
                                        <div class="font-semibold text-indigo-900 dark:text-indigo-100 mb-3 text-sm flex items-center justify-between gap-2">
                                            <div>
                                                <span>MAIN COSTS</span>
                                                <span class="ml-2 text-xs text-slate-500 dark:text-slate-400">Leg #{{ $leg->leg_number }}</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-lg">Rp {{ number_format($leg->mainCost->total, 0, ',', '.') }}</span>
                                                @if($leg->cost_category === 'trucking' && $leg->executor_type === 'own_fleet')
                                                    <a
                                                        href="{{ route('legs.print-trucking', $leg) }}"
                                                        target="_blank"
                                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-full text-[11px] font-medium bg-white dark:bg-slate-900 border border-indigo-300 dark:border-indigo-600 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-950/30 hover:border-indigo-400 dark:hover:border-indigo-500 shadow-sm hover:shadow-md transition-all"
                                                    >
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                        </svg>
                                                        Print SPK
                                                    </a>
                                                @endif
                                            </div>
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
                                            @if($leg->mainCost->cost_type)
                                            <div>
                                                <div class="text-xs text-indigo-600 dark:text-indigo-400">Tipe</div>
                                                <div class="font-medium text-indigo-900 dark:text-indigo-100">{{ ucfirst($leg->mainCost->cost_type) }}</div>
                                            </div>
                                            @endif
                                            @if($leg->mainCost->pic_name)
                                            <div>
                                                <div class="text-xs text-indigo-600 dark:text-indigo-400">Nama PIC</div>
                                                <div class="font-medium text-indigo-900 dark:text-indigo-100">{{ $leg->mainCost->pic_name }}</div>
                                            </div>
                                            @endif
                                            @if($leg->mainCost->pic_phone)
                                            <div>
                                                <div class="text-xs text-indigo-600 dark:text-indigo-400">No HP</div>
                                                <div class="font-medium text-indigo-900 dark:text-indigo-100">{{ $leg->mainCost->pic_phone }}</div>
                                            </div>
                                            @endif
                                            @if($leg->mainCost->pic_amount > 0)
                                            <div>
                                                <div class="text-xs text-indigo-600 dark:text-indigo-400">Jumlah</div>
                                                <div class="font-medium text-indigo-900 dark:text-indigo-100">Rp {{ number_format($leg->mainCost->pic_amount, 0, ',', '.') }}</div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    @endif

                                    {{-- Vendor Bills Generated from This Leg --}}
                                    @if($leg->vendorBillItems()->exists())
                                    @php
                                        $vendorBills = \App\Models\Finance\VendorBill::query()
                                            ->whereHas('items', function($q) use ($leg) {
                                                $q->where('shipment_leg_id', $leg->id);
                                            })
                                            ->with('items')
                                            ->get();
                                        $legBillsCount = $vendorBills->count();
                                    @endphp
                                    <div class="bg-blue-50 dark:bg-blue-950/20 rounded-lg p-4 mb-3">
                                        <div class="font-semibold text-blue-900 dark:text-blue-100 mb-2 text-sm">
                                            VENDOR BILLS ({{ $legBillsCount }}x Generated)
                                        </div>
                                        <div class="space-y-2">
                                            @foreach($vendorBills as $vb)
                                            <div class="flex items-center justify-between gap-3 text-sm bg-white dark:bg-slate-800 rounded p-3">
                                                <div class="flex-1">
                                                    <a href="{{ route('vendor-bills.show', $vb) }}" class="font-medium text-blue-600 dark:text-blue-400 hover:underline">
                                                        {{ $vb->vendor_bill_number }}
                                                    </a>
                                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                                        {{ $vb->bill_date->format('d M Y') }} •
                                                        <x-badge :variant="match($vb->status) {
                                                            'draft' => 'default',
                                                            'received' => 'warning',
                                                            'partially_paid' => 'warning',
                                                            'paid' => 'success',
                                                            default => 'default'
                                                        }" class="text-[10px]">{{ strtoupper($vb->status) }}</x-badge>
                                                    </div>
                                                </div>
                                                <div class="text-right font-medium text-blue-900 dark:text-blue-100">
                                                    Rp {{ number_format($vb->total_amount, 0, ',', '.') }}
                                                </div>
                                            </div>
                                            @endforeach
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
                                    <div class="flex items-center justify-between gap-2 pt-3 border-t border-slate-200 dark:border-slate-800">
                                        <div class="flex-1">
                                            @php
                                                $totalGenerated = (float) $leg->vendorBillItems()->sum('subtotal');
                                                $legTotalCost = (float) ($leg->mainCost ? $leg->mainCost->total : 0);
                                                $remaining = $legTotalCost - $totalGenerated;
                                                $billsCount = $leg->vendorBillItems()->distinct('vendor_bill_id')->count('vendor_bill_id');

                                                // Tracking berdasarkan pengajuan (bukan pembayaran) - cicilan pengajuan
                                                $vendorBillIds = $leg->vendorBillItems()->distinct('vendor_bill_id')->pluck('vendor_bill_id');
                                                $allVendorBills = \App\Models\Finance\VendorBill::with(['paymentRequests'])
                                                    ->whereIn('id', $vendorBillIds)
                                                    ->whereNotIn('status', ['paid', 'cancelled'])
                                                    ->get();
                                                // Filter bills yang masih ada sisa belum diajukan
                                                $billsWithRemainingToRequest = $allVendorBills->filter(function($bill){
                                                    $totalRequested = $bill->paymentRequests->sum('amount');
                                                    $remainingToRequest = $bill->total_amount - $totalRequested;
                                                    return $remainingToRequest > 0;
                                                });
                                                $hasBillsWithRemainingToRequest = $billsWithRemainingToRequest->count() > 0;
                                                $hasAnyBills = $vendorBillIds->count() > 0;
                                            @endphp

                                            @if($leg->vendor_id && $remaining > 0 && $leg->status != 'cancelled')
                                            {{-- Masih ada sisa yang perlu di-bill --}}
                                            <button
                                                type="button"
                                                onclick="openGenerateBillModal({{ $leg->id }}, {{ $leg->additionalCosts->count() }})"
                                                class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                Generate Vendor Bill
                                                @if($billsCount > 0)
                                                    <span class="text-[10px] opacity-75">({{ $billsCount + 1 }}x)</span>
                                                @endif
                                            </button>
                                            <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">
                                                @if($billsCount > 0)
                                                    Sudah billed: Rp {{ number_format($totalGenerated, 0, ',', '.') }} • Sisa: Rp {{ number_format($remaining, 0, ',', '.') }}
                                                @else
                                                    Total: Rp {{ number_format($legTotalCost, 0, ',', '.') }}
                                                @endif
                                            </div>
                                            @elseif($remaining <= 0 && $billsCount > 0)
                                            {{-- Fully billed, cek pengajuan berdasarkan remaining_to_request --}}
                                                @if($hasBillsWithRemainingToRequest)
                                                <a href="{{ route('payment-requests.create', ['vendor_bill_id' => $billsWithRemainingToRequest->first()->id]) }}"
                                                   class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    Ajukan Pembayaran
                                                    @if($billsWithRemainingToRequest->count() > 1)
                                                        <span class="text-[10px] opacity-75">({{ $billsWithRemainingToRequest->count() }}x)</span>
                                                    @endif
                                                </a>
                                                <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">
                                                    Bill siap diajukan: {{ $billsWithRemainingToRequest->count() }}x
                                                </div>
                                                @else
                                                {{-- Semua bill sudah ada payment request --}}
                                                <div class="text-xs">
                                                    <span class="text-green-600 dark:text-green-400 flex items-center gap-1">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                        Sudah Diajukan Penuh ({{ $billsCount }}x)
                                                    </span>
                                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">
                                                        Total: Rp {{ number_format($legTotalCost, 0, ',', '.') }}
                                                    </div>
                                                </div>
                                                @endif
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2">
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
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <div class="flex justify-center mb-4">
                                <svg class="w-16 h-16 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                </svg>
                            </div>
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
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5 leading-tight">Jumlah yang akan ditagihkan ke customer (default sama dengan amount)</p>
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
        arrow.style.transition = 'transform 0.2s ease';
    } else {
        // Collapse
        detailsDiv.classList.add('hidden');
        arrow.style.transform = 'rotate(0deg)';
        arrow.style.transition = 'transform 0.2s ease';
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

// Generate Vendor Bill Modal Functions
let currentLegIdForGenerate = null;

function openGenerateBillModal(legId, additionalCostsCount) {
    currentLegIdForGenerate = legId;
    const modal = document.getElementById('generateBillModal');
    const additionalCostsInfo = document.getElementById('additionalCostsInfo');
    const separateOption = document.getElementById('separateOption');

    // Show/hide "separate" option based on additional costs
    if (additionalCostsCount > 0) {
        additionalCostsInfo.classList.remove('hidden');
        separateOption.classList.remove('hidden');
    } else {
        additionalCostsInfo.classList.add('hidden');
        separateOption.classList.add('hidden');
        // Default to 'combined' if no additional costs
        document.getElementById('bill_mode_combined').checked = true;
    }

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeGenerateBillModal() {
    const modal = document.getElementById('generateBillModal');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
    currentLegIdForGenerate = null;
}

function submitGenerateBill() {
    const billMode = document.querySelector('input[name="bill_mode"]:checked').value;

    if (!currentLegIdForGenerate) {
        alert('Error: Leg ID tidak ditemukan');
        return;
    }

    // Create and submit form
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/legs/${currentLegIdForGenerate}/generate-vendor-bill`;

    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    form.appendChild(csrfInput);

    // Add bill_mode input
    const billModeInput = document.createElement('input');
    billModeInput.type = 'hidden';
    billModeInput.name = 'bill_mode';
    billModeInput.value = billMode;
    form.appendChild(billModeInput);

    document.body.appendChild(form);
    form.submit();
}

// Close generate bill modal on outside click
document.getElementById('generateBillModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeGenerateBillModal();
    }
});
</script>

{{-- Generate Vendor Bill Modal --}}
<div id="generateBillModal" class="hidden fixed inset-0 bg-slate-900/50 dark:bg-slate-900/80 backdrop-blur-sm z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl max-w-lg w-full">
            {{-- Header --}}
            <div class="flex items-center justify-between p-6 border-b border-slate-200 dark:border-slate-700">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                    Pilih Mode Generate Vendor Bill
                </h3>
                <button
                    type="button"
                    onclick="closeGenerateBillModal()"
                    class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="p-6 space-y-4">
                <div id="additionalCostsInfo" class="hidden bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 rounded-lg p-3 text-sm text-blue-800 dark:text-blue-200">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m-1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <strong>Leg ini memiliki Additional Costs.</strong>
                            <p class="mt-1 text-xs">Anda bisa memilih untuk menggabungkan semua biaya dalam 1 vendor bill, atau memisahkan main cost dan additional costs menjadi 2 vendor bill terpisah.</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    {{-- Option 1: Gabung --}}
                    <label class="flex items-start gap-3 p-4 border-2 border-slate-200 dark:border-slate-700 rounded-lg cursor-pointer hover:border-emerald-500 dark:hover:border-emerald-500 transition-colors group">
                        <input
                            type="radio"
                            id="bill_mode_combined"
                            name="bill_mode"
                            value="combined"
                            checked
                            class="mt-1 w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                        <div class="flex-1">
                            <div class="font-medium text-slate-900 dark:text-slate-100 group-hover:text-emerald-600 dark:group-hover:text-emerald-400">
                                Gabung dalam 1 Vendor Bill
                            </div>
                            <div class="text-xs text-slate-600 dark:text-slate-400 mt-1">
                                Main cost + semua additional costs digabung menjadi 1 vendor bill
                            </div>
                        </div>
                    </label>

                    {{-- Option 2: Pisah --}}
                    <label id="separateOption" class="flex items-start gap-3 p-4 border-2 border-slate-200 dark:border-slate-700 rounded-lg cursor-pointer hover:border-emerald-500 dark:hover:border-emerald-500 transition-colors group">
                        <input
                            type="radio"
                            id="bill_mode_separate"
                            name="bill_mode"
                            value="separate"
                            class="mt-1 w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                        <div class="flex-1">
                            <div class="font-medium text-slate-900 dark:text-slate-100 group-hover:text-emerald-600 dark:group-hover:text-emerald-400">
                                Pisah Main Cost & Additional Costs
                            </div>
                            <div class="text-xs text-slate-600 dark:text-slate-400 mt-1">
                                Generate 2 vendor bills: 1 untuk main cost, 1 untuk semua additional costs
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 p-6 border-t border-slate-200 dark:border-slate-700">
                <button
                    type="button"
                    onclick="closeGenerateBillModal()"
                    class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-lg transition-colors">
                    Batal
                </button>
                <button
                    type="button"
                    onclick="submitGenerateBill()"
                    class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors">
                    Generate Vendor Bill
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Cancel Order --}}
<div id="cancelModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xl max-w-md w-full">
        <form method="POST" action="{{ route('job-orders.cancel', $job) }}" class="p-6 space-y-4">
            @csrf
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Cancel Job Order</h3>
                <button type="button" onclick="closeCancelModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="bg-yellow-50 dark:bg-yellow-950/30 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3 mb-4">
                <div class="text-sm text-yellow-800 dark:text-yellow-200">
                    <strong>Perhatian:</strong> Job Order yang di-cancel tidak bisa di-edit lagi. Pastikan semua data sudah benar.
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    Alasan Cancel <span class="text-red-500">*</span>
                </label>
                <textarea
                    name="cancel_reason"
                    required
                    rows="4"
                    placeholder="Contoh: Customer tidak bisa dihubungi, vendor sudah di lokasi tapi customer tidak ada, dll."
                    class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-red-500 shadow-sm"
                ></textarea>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">Minimal 10 karakter</p>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button
                    type="button"
                    onclick="closeCancelModal()"
                    class="px-4 py-2 rounded-lg bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 transition-colors"
                >
                    Cancel Order
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openCancelModal() {
    document.getElementById('cancelModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeCancelModal() {
    document.getElementById('cancelModal').classList.add('hidden');
    document.body.style.overflow = '';
}

// Close modal on outside click
document.getElementById('cancelModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeCancelModal();
    }
});
</script>

@endsection
