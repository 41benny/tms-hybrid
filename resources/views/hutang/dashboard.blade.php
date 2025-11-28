@extends('layouts.app', ['title' => 'Dashboard Hutang'])

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div>
        <div class="text-2xl font-bold text-slate-900 dark:text-slate-100">Dashboard Hutang</div>
        <p class="text-sm text-slate-600 dark:text-slate-400">Monitoring semua hutang vendor (Trucking, Vendor, Pelayaran, Asuransi) dan uang jalan driver</p>
    </div>

    {{-- Consolidated Info Panel --}}
    <x-card>
        <div class="p-6">
            {{-- Main Metrics --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 pb-6 border-b border-slate-200 dark:border-slate-700">
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Total Tagihan Vendor</p>
                    <h3 class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-2">Rp {{ number_format($totalVendorBills,0,',','.') }}</h3>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">Semua vendor bills (exclude cancelled)</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Sudah Diajukan</p>
                    <h3 class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-2">Rp {{ number_format($totalRequested,0,',','.') }}</h3>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">Payment requests (pending/approved/paid)</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Sisa Belum Diajukan</p>
                    <h3 class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-2">Rp {{ number_format($totalRemainingToRequest,0,',','.') }}</h3>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">Outstanding (remaining to request)</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Dibayar Bulan Ini</p>
                    <h3 class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 mt-2">Rp {{ number_format($paidThisMonth,0,',','.') }}</h3>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">Payment requests status PAID ({{ now()->format('M Y') }})</p>
                </div>
            </div>

            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6">
                {{-- Pending Order Legs --}}
                <a href="javascript:void(0)" onclick="showTab('pending-legs')" class="block group">
                    <div class="flex items-start gap-4 p-4 rounded-xl bg-gradient-to-br from-orange-50 to-orange-100/50 dark:from-orange-900/20 dark:to-orange-800/10 border border-orange-200 dark:border-orange-800/30 hover:shadow-lg transition-all">
                        <div class="w-12 h-12 rounded-xl bg-orange-500 dark:bg-orange-600 flex items-center justify-center flex-shrink-0 shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-orange-700 dark:text-orange-400 uppercase tracking-wide">Pending Order Legs</p>
                            <h3 class="text-xl font-bold text-orange-900 dark:text-orange-300 mt-1">
                                Rp {{ number_format($totalPendingVendorLegs, 0, ',', '.') }}
                            </h3>
                            <p class="text-[10px] text-orange-600 dark:text-orange-500 mt-1">{{ $pendingVendorLegs->count() }} legs belum dibuat bill</p>
                        </div>
                    </div>
                </a>

                {{-- Unpaid Vendor Bills --}}
                <a href="javascript:void(0)" onclick="showTab('unpaid-bills')" class="block group">
                    <div class="flex items-start gap-4 p-4 rounded-xl bg-gradient-to-br from-red-50 to-red-100/50 dark:from-red-900/20 dark:to-red-800/10 border border-red-200 dark:border-red-800/30 hover:shadow-lg transition-all">
                        <div class="w-12 h-12 rounded-xl bg-red-500 dark:bg-red-600 flex items-center justify-center flex-shrink-0 shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-red-700 dark:text-red-400 uppercase tracking-wide">Unpaid Vendor Bills</p>
                            <h3 class="text-xl font-bold text-red-900 dark:text-red-300 mt-1">
                                Rp {{ number_format($totalUnpaidVendorBills, 0, ',', '.') }}
                            </h3>
                            <p class="text-[10px] text-red-600 dark:text-red-500 mt-1">{{ $unpaidVendorBills->count() }} bills outstanding</p>
                        </div>
                    </div>
                </a>

                {{-- Pending Driver Advances --}}
                <a href="javascript:void(0)" onclick="showTab('driver-advances')" class="block group">
                    <div class="flex items-start gap-4 p-4 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100/50 dark:from-blue-900/20 dark:to-blue-800/10 border border-blue-200 dark:border-blue-800/30 hover:shadow-lg transition-all">
                        <div class="w-12 h-12 rounded-xl bg-blue-500 dark:bg-blue-600 flex items-center justify-center flex-shrink-0 shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-blue-700 dark:text-blue-400 uppercase tracking-wide">Pending Driver Advances</p>
                            <h3 class="text-xl font-bold text-blue-900 dark:text-blue-300 mt-1">
                                Rp {{ number_format($totalPendingDriverAdvances, 0, ',', '.') }}
                            </h3>
                            <p class="text-[10px] text-blue-600 dark:text-blue-500 mt-1">{{ $pendingDriverAdvances->count() }} advances pending</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </x-card>

    {{-- Tabs --}}
    <div class="bg-white dark:bg-[#1e1e1e] rounded-xl shadow-lg">
        <div class="border-b border-slate-200 dark:border-slate-800">
            <nav class="flex gap-4 px-6" aria-label="Tabs">
                <button onclick="showTab('pending-legs')" id="tab-pending-legs" class="tab-button px-4 py-4 text-sm font-medium border-b-2 border-indigo-600 text-indigo-600">
                    Pending Order Legs ({{ $pendingVendorLegs->count() }})
                </button>
                <button onclick="showTab('unpaid-bills')" id="tab-unpaid-bills" class="tab-button px-4 py-4 text-sm font-medium border-b-2 border-transparent text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-slate-300 dark:hover:border-slate-600">
                    Unpaid Bills ({{ $unpaidVendorBills->count() }})
                </button>
                <button onclick="showTab('driver-advances')" id="tab-driver-advances" class="tab-button px-4 py-4 text-sm font-medium border-b-2 border-transparent text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-slate-300 dark:hover:border-slate-600">
                    Driver Advances ({{ $pendingDriverAdvances->count() }})
                </button>
                <button onclick="showTab('summary')" id="tab-summary" class="tab-button px-4 py-4 text-sm font-medium border-b-2 border-transparent text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-slate-300 dark:hover:border-slate-600">
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
                                @php
                                    $daysDiff = $leg->unload_date ? $leg->unload_date->diffInDays(now()) : null;
                                @endphp
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $daysDiff !== null && $daysDiff > 30 ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300' }}">
                                    {{ $daysDiff !== null ? $daysDiff . ' hari' : '-' }}
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
                                        Rp {{ number_format($summary->total_vendor_cost, 0, ',', '.') }}
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

