@extends('layouts.app', ['title' => 'Payment Receipts'])

@section('content')
    <x-card>
        <x-slot:header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="text-2xl font-bold text-slate-900 dark:text-slate-100">Payment Receipts</div>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Manage customer payments and allocations</p>
                </div>
                <x-button :href="route('payment-receipts.create')" variant="primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Record Payment
                </x-button>
            </div>
        </x-slot:header>

        {{-- Summary Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 rounded-lg p-4">
                <div class="text-sm text-emerald-600 dark:text-emerald-400 font-medium">Total Received</div>
                <div class="text-2xl font-bold text-emerald-700 dark:text-emerald-300 mt-1">
                    Rp {{ number_format($stats['total_received'] ?? 0, 0, ',', '.') }}
                </div>
            </div>
            <div class="bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="text-sm text-blue-600 dark:text-blue-400 font-medium">Total Allocated</div>
                <div class="text-2xl font-bold text-blue-700 dark:text-blue-300 mt-1">
                    Rp {{ number_format($stats['total_allocated'] ?? 0, 0, ',', '.') }}
                </div>
            </div>
            <div class="bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
                <div class="text-sm text-amber-600 dark:text-amber-400 font-medium">Unallocated</div>
                <div class="text-2xl font-bold text-amber-700 dark:text-amber-300 mt-1">
                    Rp {{ number_format($stats['total_unallocated'] ?? 0, 0, ',', '.') }}
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <form method="get" class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <select name="customer_id" class="rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Customers</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" @selected(request('customer_id')==$customer->id)>{{ $customer->name }}</option>
                @endforeach
            </select>

            <select name="payment_method" class="rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Methods</option>
                @foreach(['bank_transfer' => 'Bank Transfer', 'cash' => 'Cash', 'check' => 'Check', 'giro' => 'Giro', 'other' => 'Other'] as $val => $label)
                    <option value="{{ $val }}" @selected(request('payment_method')===$val)>{{ $label }}</option>
                @endforeach
            </select>

            <input type="date" name="from" value="{{ request('from') }}" placeholder="From Date" class="rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500" />

            <input type="date" name="to" value="{{ request('to') }}" placeholder="To Date" class="rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500" />

            <x-button type="submit" variant="outline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filter
            </x-button>
        </form>
    </x-card>

    {{-- Desktop Table View --}}
    <x-card :noPadding="true" class="mt-6 hidden md:block">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-[#2d2d2d]">
                <thead class="bg-slate-50 dark:bg-[#252525]">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Receipt #</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Customer</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Method</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Amount</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Allocated</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Unallocated</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-[#1e1e1e] divide-y divide-slate-200 dark:divide-[#2d2d2d]">
                @forelse($receipts as $receipt)
                    <tr class="hover:bg-slate-50 dark:hover:bg-[#252525] transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <a href="{{ route('payment-receipts.show', $receipt) }}" class="text-blue-600 dark:text-blue-400 hover:underline font-semibold text-sm">
                                {{ $receipt->receipt_number }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400 text-sm">
                            {{ $receipt->customer->name }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-400 text-sm">
                            {{ $receipt->payment_date->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-400 text-sm">
                            {{ ucwords(str_replace('_', ' ', $receipt->payment_method)) }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right text-slate-900 dark:text-slate-100 text-sm font-medium">
                            Rp {{ number_format($receipt->amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right text-emerald-600 dark:text-emerald-400 text-sm">
                            Rp {{ number_format($receipt->allocated_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right text-amber-600 dark:text-amber-400 text-sm font-medium">
                            Rp {{ number_format($receipt->unallocated_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center gap-2">
                                <x-button :href="route('payment-receipts.show', $receipt)" variant="outline" size="sm">View</x-button>

                                @if($receipt->unallocated_amount > 0)
                                <x-badge variant="warning" class="text-xs">Allocate</x-badge>
                                @else
                                <x-badge variant="success" class="text-xs">Fully Allocated</x-badge>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                            <svg class="w-12 h-12 mx-auto mb-2 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <p class="text-sm">No payment receipts found</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    {{-- Mobile Card View --}}
    <div class="mt-6 space-y-4 md:hidden">
        @forelse($receipts as $receipt)
        <x-card :noPadding="true">
            <div class="p-4">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex-1">
                        <a href="{{ route('payment-receipts.show', $receipt) }}" class="font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                            {{ $receipt->receipt_number }}
                        </a>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $receipt->payment_date->format('d M Y') }}</div>
                    </div>
                    @if($receipt->unallocated_amount > 0)
                    <x-badge variant="warning" class="text-xs">Unallocated</x-badge>
                    @else
                    <x-badge variant="success" class="text-xs">Fully Allocated</x-badge>
                    @endif
                </div>

                <div class="space-y-2 text-sm mb-4">
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Customer:</span>
                        <span class="text-slate-900 dark:text-slate-100 font-medium">{{ $receipt->customer->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Method:</span>
                        <span class="text-slate-900 dark:text-slate-100">{{ ucwords(str_replace('_', ' ', $receipt->payment_method)) }}</span>
                    </div>
                    <div class="flex justify-between pt-2 border-t border-slate-200 dark:border-slate-700">
                        <span class="text-slate-500 dark:text-slate-400">Amount:</span>
                        <span class="text-lg font-bold text-slate-900 dark:text-slate-100">Rp {{ number_format($receipt->amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Unallocated:</span>
                        <span class="font-semibold text-amber-600 dark:text-amber-400">Rp {{ number_format($receipt->unallocated_amount, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-3 border-t border-slate-200 dark:border-slate-700">
                    <x-button :href="route('payment-receipts.show', $receipt)" variant="outline" size="sm" class="flex-1 justify-center">View & Allocate</x-button>
                </div>
            </div>
        </x-card>
        @empty
        <x-card>
            <div class="text-center py-8 text-slate-500 dark:text-slate-400">
                <svg class="w-12 h-12 mx-auto mb-2 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <p class="text-sm">No payment receipts found</p>
            </div>
        </x-card>
        @endforelse
    </div>

    <div class="mt-4">{{ $receipts->links() }}</div>
@endsection
