@extends('layouts.app', ['title' => 'Dashboard Hutang'])

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Dashboard Hutang</h1>
        <p class="text-sm text-slate-600 dark:text-slate-400">Monitoring semua hutang vendor (Trucking, Vendor, Pelayaran, Asuransi) dan uang jalan driver</p>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Hutang Vendor Pending --}}
        <x-card>
            <a href="javascript:void(0)" onclick="showTab('pending-legs')" class="block">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Pending Order Legs</p>
                            <h3 class="text-2xl font-bold text-orange-600 dark:text-orange-400 mt-1">
                                Rp {{ number_format($totalPendingVendorLegs, 0, ',', '.') }}
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-500 mt-1">{{ $pendingVendorLegs->count() }} legs (semua kategori) belum dibuat bill</p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                            <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                    </div>
                </div>
            </a>
        </x-card>

        {{-- Vendor Bills Belum Lunas --}}
        <x-card>
            <a href="javascript:void(0)" onclick="showTab('unpaid-bills')" class="block">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Unpaid Vendor Bills</p>
                            <h3 class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">
                                Rp {{ number_format($totalUnpaidVendorBills, 0, ',', '.') }}
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-500 mt-1">{{ $unpaidVendorBills->count() }} bills outstanding</p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </a>
        </x-card>

        {{-- Uang Jalan Driver Pending --}}
        <x-card>
            <a href="javascript:void(0)" onclick="showTab('driver-advances')" class="block">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Pending Driver Advances</p>
                            <h3 class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">
                                Rp {{ number_format($totalPendingDriverAdvances, 0, ',', '.') }}
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-500 mt-1">{{ $pendingDriverAdvances->count() }} advances pending</p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </a>
        </x-card>
    </div>

    {{-- Tabs --}}
    <div class="bg-white dark:bg-[#1e1e1e] rounded-xl shadow-lg">
        <div class="border-b border-slate-200 dark:border-slate-800">
            <nav class="flex gap-4 px-6" aria-label="Tabs">
                <button onclick="showTab('pending-legs')" id="tab-pending-legs" class="tab-button px-4 py-4 text-sm font-medium border-b-2 border-indigo-600 text-indigo-600">
                    Pending Order Legs ({{ $pendingVendorLegs->count() }})
                </button>
                <button onclick="showTab('unpaid-bills')" id="tab-unpaid-bills" class="tab-button px-4 py-4 text-sm font-medium border-b-2 border-transparent text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100">
                    Unpaid Bills ({{ $unpaidVendorBills->count() }})
                </button>
                <button onclick="showTab('driver-advances')" id="tab-driver-advances" class="tab-button px-4 py-4 text-sm font-medium border-b-2 border-transparent text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100">
                    Driver Advances ({{ $pendingDriverAdvances->count() }})
                </button>
                <button onclick="showTab('summary')" id="tab-summary" class="tab-button px-4 py-4 text-sm font-medium border-b-2 border-transparent text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100">
                    Summary
                </button>
            </nav>
        </div>

        {{-- Tab Content: Pending Legs --}}
        <div id="content-pending-legs" class="tab-content p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Job Order</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Leg</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Vendor</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Category</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Cost</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Days</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($pendingVendorLegs as $leg)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                <a href="{{ route('job-orders.show', $leg->jobOrder) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                    {{ $leg->jobOrder->job_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-900 dark:text-slate-100">
                                Leg #{{ $leg->leg_number }}
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-900 dark:text-slate-100">
                                {{ $leg->vendor->name }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                <x-badge :variant="match($leg->cost_category) {
                                    'trucking' => 'default',
                                    'vendor' => 'warning',
                                    'pelayaran' => 'primary',
                                    'asuransi' => 'success',
                                    default => 'default'
                                }">{{ strtoupper($leg->cost_category) }}</x-badge>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-medium text-slate-900 dark:text-slate-100">
                                Rp {{ number_format($leg->mainCost ? $leg->mainCost->total : 0, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $leg->unload_date->diffInDays(now()) > 30 ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300' }}">
                                    {{ $leg->unload_date->diffInDays(now()) }} hari
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right">
                                <form method="POST" action="{{ route('legs.generate-vendor-bill', $leg) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300 font-medium">
                                        Generate Bill →
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                ✅ Tidak ada pending vendor legs
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tab Content: Unpaid Bills --}}
        <div id="content-unpaid-bills" class="tab-content p-6 hidden">
            <div class="mb-4 flex items-center justify-between">
                <p class="text-sm text-slate-600 dark:text-slate-400">Daftar vendor bills yang belum lunas</p>
                <x-button :href="route('vendor-bills.index')" variant="outline" size="sm">
                    Lihat Semua Vendor Bills →
                </x-button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Bill Number</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Vendor</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Date</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">DPP</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">PPN</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">PPH23</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Sisa</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Days</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($unpaidVendorBills as $bill)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                <a href="{{ route('vendor-bills.show', $bill) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                                    {{ $bill->vendor_bill_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-900 dark:text-slate-100">
                                {{ $bill->vendor->name }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600 dark:text-slate-400">
                                {{ $bill->bill_date->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-slate-900 dark:text-slate-100">
                                Rp {{ number_format($bill->dpp, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-green-600 dark:text-green-400">
                                Rp {{ number_format($bill->ppn, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-orange-600 dark:text-orange-400">
                                Rp {{ number_format($bill->pph, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-bold text-slate-900 dark:text-slate-100">
                                Rp {{ number_format($bill->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-semibold {{ $bill->remaining > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                Rp {{ number_format($bill->remaining, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                <x-badge :variant="match($bill->status) {
                                    'draft' => 'default',
                                    'received' => 'warning',
                                    'partially_paid' => 'warning',
                                    default => 'default'
                                }">{{ strtoupper(str_replace('_', ' ', $bill->status)) }}</x-badge>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $bill->bill_date->diffInDays(now()) > 30 ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300' }}">
                                    {{ $bill->bill_date->diffInDays(now()) }} hari
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('vendor-bills.show', $bill) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300" title="View Detail">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    @if($bill->remaining > 0)
                                        @if($bill->has_active_payment_request)
                                            <span class="text-slate-400 dark:text-slate-600 cursor-not-allowed" title="Pembayaran sudah diajukan">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </span>
                                        @else
                                            <a href="{{ route('payment-requests.create', ['vendor_bill_id' => $bill->id]) }}" class="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300" title="Request Payment">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </a>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                ✅ Semua vendor bills sudah lunas
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tab Content: Driver Advances --}}
        <div id="content-driver-advances" class="tab-content p-6 hidden">
            <div class="mb-4 flex items-center justify-between">
                <p class="text-sm text-slate-600 dark:text-slate-400">Daftar uang jalan driver yang pending</p>
                <x-button :href="route('driver-advances.index')" variant="outline" size="sm">
                    Kelola Semua Driver Advances →
                </x-button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Advance#</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Driver</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Job Order / Leg</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Date</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Amount</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Days</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($pendingDriverAdvances as $advance)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                <a href="{{ route('driver-advances.show', $advance) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                                    {{ $advance->advance_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-900 dark:text-slate-100">
                                {{ $advance->driver->name }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                <a href="{{ route('job-orders.show', $advance->shipmentLeg->jobOrder) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                    {{ $advance->shipmentLeg->jobOrder->job_number }}
                                </a> / Leg #{{ $advance->shipmentLeg->leg_number }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600 dark:text-slate-400">
                                {{ $advance->advance_date->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-medium text-slate-900 dark:text-slate-100">
                                Rp {{ number_format($advance->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $advance->advance_date->diffInDays(now()) > 14 ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300' }}">
                                    {{ $advance->advance_date->diffInDays(now()) }} hari
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right">
                                <a href="{{ route('driver-advances.show', $advance) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 font-medium">
                                    Kelola →
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                ✅ Tidak ada pending driver advances
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tab Content: Summary --}}
        <div id="content-summary" class="tab-content p-6 hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Vendor Summary --}}
                <div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Pending per Vendor</h3>
                    <div class="space-y-3">
                        @forelse($vendorSummary as $summary)
                        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="font-medium text-slate-900 dark:text-slate-100">{{ $summary->vendor->name }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $summary->leg_count }} legs</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-slate-900 dark:text-slate-100">
                                        Rp {{ number_format($summary->total_cost, 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <p class="text-sm text-slate-500 dark:text-slate-400 text-center py-4">Tidak ada data</p>
                        @endforelse
                    </div>
                </div>

                {{-- Driver Summary --}}
                <div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Pending per Driver</h3>
                    <div class="space-y-3">
                        @forelse($driverSummary as $summary)
                        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="font-medium text-slate-900 dark:text-slate-100">{{ $summary->driver->name }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $summary->advance_count }} advances</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-slate-900 dark:text-slate-100">
                                        Rp {{ number_format($summary->total_amount, 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <p class="text-sm text-slate-500 dark:text-slate-400 text-center py-4">Tidak ada data</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    // Hide all content
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    
    // Remove active state from all buttons
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('border-indigo-600', 'text-indigo-600');
        btn.classList.add('border-transparent', 'text-slate-600', 'dark:text-slate-400');
    });
    
    // Show selected content
    document.getElementById('content-' + tabName).classList.remove('hidden');
    
    // Add active state to selected button
    const activeBtn = document.getElementById('tab-' + tabName);
    activeBtn.classList.add('border-indigo-600', 'text-indigo-600');
    activeBtn.classList.remove('border-transparent', 'text-slate-600', 'dark:text-slate-400');
}
</script>
@endsection

