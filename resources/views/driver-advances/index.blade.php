@extends('layouts.app', ['title' => 'Driver Advances'])

@section('content')
<div class="space-y-6">
    {{-- Header Section --}}
    <x-card>
        <x-slot:header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="text-2xl font-bold text-slate-900 dark:text-slate-100">Driver Advances</div>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Manage trip money & driver settlement</p>
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <form method="get" class="flex items-center gap-2">
                        <select name="status" class="rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-4 py-2 text-sm text-slate-900 dark:text-slate-100">
                            <option value="">All Status</option>
                            <option value="pending" @selected(request('status')=='pending')>Pending (Awaiting DP)</option>
                            <option value="dp_paid" @selected(request('status')=='dp_paid')>DP Paid</option>
                            <option value="settled" @selected(request('status')=='settled')>Settled</option>
                        </select>
                        <x-button variant="outline" type="submit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Filter
                        </x-button>
                    </form>
                </div>
            </div>
        </x-slot:header>
    </x-card>

    {{-- Stats Cards --}}
    @php
        $totalPending = \App\Models\Operations\DriverAdvance::where('status', 'pending')->sum('amount');
        $totalDpPaid = \App\Models\Operations\DriverAdvance::where('status', 'dp_paid')->sum('amount');
        $totalSettled = \App\Models\Operations\DriverAdvance::where('status', 'settled')->sum('amount');
    @endphp
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-card :noPadding="true">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Pending (Awaiting DP)</p>
                        <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">
                            Rp {{ number_format($totalPending, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="p-3 bg-amber-100 dark:bg-amber-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </x-card>

        <x-card :noPadding="true">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">DP Paid (On Road)</p>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">
                            Rp {{ number_format($totalDpPaid, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
            </div>
        </x-card>

        <x-card :noPadding="true">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Settled (Completed)</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">
                            Rp {{ number_format($totalSettled, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </x-card>
    </div>

    {{-- Table Section --}}
    <x-card :noPadding="true">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-[#2d2d2d]">
                <thead class="bg-slate-50 dark:bg-[#252525]">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                            Number
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                            Driver
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                            Job Order
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                            Total
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                            DP
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                            Remaining
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-[#1e1e1e] divide-y divide-slate-200 dark:divide-[#2d2d2d]">
                    @forelse($advances as $adv)
                        <tr class="hover:bg-slate-50 dark:hover:bg-[#252525] transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-slate-900 dark:text-slate-100">{{ $adv->advance_number }}</div>
                                <div class="text-sm text-slate-500 dark:text-slate-400">{{ $adv->advance_date->format('d M Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span class="text-slate-900 dark:text-slate-100">{{ $adv->driver->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-400">
                                {{ $adv->shipmentLeg->jobOrder->job_number ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-semibold text-slate-900 dark:text-slate-100">
                                    Rp {{ number_format($adv->amount, 0, ',', '.') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($adv->dp_amount > 0)
                                    <div class="text-blue-600 dark:text-blue-400 font-medium">
                                        Rp {{ number_format($adv->dp_amount, 0, ',', '.') }}
                                    </div>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-amber-600 dark:text-amber-400 font-medium">
                                    Rp {{ number_format($adv->remaining_amount, 0, ',', '.') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($adv->status === 'pending')
                                    <x-badge variant="warning">Pending</x-badge>
                                @elseif($adv->status === 'dp_paid')
                                    <x-badge variant="info">DP Paid</x-badge>
                                @else
                                    <x-badge variant="success">Settled</x-badge>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <x-button :href="route('driver-advances.show', $adv)" variant="ghost" size="sm" title="View Details">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </x-button>
                                    @if($adv->status === 'pending')
                                        <x-button :href="route('payment-requests.create', ['driver_advance_id' => $adv->id])" variant="ghost" size="sm" title="Request DP Payment">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </x-button>
                                    @elseif($adv->status === 'dp_paid')
                                        <x-button :href="route('payment-requests.create', ['driver_advance_id' => $adv->id, 'type' => 'settlement'])" variant="ghost" size="sm" title="Submit Settlement">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </x-button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                <div class="flex flex-col items-center gap-2">
                                    <span class="text-4xl">💰</span>
                                    <p class="text-sm">No driver advance data yet</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($advances->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 dark:border-[#2d2d2d]">
                {{ $advances->links() }}
            </div>
        @endif
    </x-card>
</div>
@endsection

