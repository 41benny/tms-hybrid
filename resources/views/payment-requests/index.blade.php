@extends('layouts.app', ['title' => 'Payment Requests'])

@section('content')
    @php
        $isSalesUser = Auth::check() && (Auth::user()->role ?? null) === \App\Models\User::ROLE_SALES;
    @endphp

    <x-card>
        <x-slot:header>
        <x-slot:header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-end gap-4">
                <div class="flex items-center gap-4">
                    <div>
                        @if(($show ?? null) === 'all')
                            <a href="{{ request()->fullUrlWithQuery(['show'=>null]) }}" class="inline-flex items-center gap-1.5 text-[11px] font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                Including paid • Click for outstanding only
                            </a>
                        @else
                            <a href="{{ request()->fullUrlWithQuery(['show'=>'all']) }}" class="inline-flex items-center gap-1.5 text-[11px] font-medium hover:text-indigo-600 dark:hover:text-indigo-400" style="color: var(--color-text-muted);">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                                Outstanding only • Click to show including paid
                            </a>
                        @endif
                    </div>
                    <x-button :href="route('payment-requests.create')" variant="primary" size="sm" class="normal-case">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New request
                    </x-button>
                </div>
            </div>
        </x-slot:header>

        <form method="get" class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <select name="status" class="rounded-lg bg-[var(--bg-surface-secondary)] border border-[var(--border-color)] px-3 py-2 text-sm text-[var(--color-text-main)] focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Status</option>
                @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'paid' => 'Paid'] as $val => $label)
                    <option value="{{ $val }}" @selected(request('status')===$val)>{{ $label }}</option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ request('from') }}" placeholder="From Date" class="rounded-lg bg-[var(--bg-surface-secondary)] border border-[var(--border-color)] px-3 py-2 text-sm text-[var(--color-text-main)] focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            <input type="date" name="to" value="{{ request('to') }}" placeholder="To Date" class="rounded-lg bg-[var(--bg-surface-secondary)] border border-[var(--border-color)] px-3 py-2 text-sm text-[var(--color-text-main)] focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            <div></div>
            <x-button type="submit" variant="outline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filter
            </x-button>
        </form>
    </x-card>

    {{-- Section: Vendor Bills Belum Lunas --}}
    @if($unpaidBills->count() > 0)
    <x-card class="mt-6 hidden md:block">
        <x-slot:header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-[var(--color-text-main)]">Vendor Bills Not Fully Requested</h2>
                    <p class="text-sm text-[var(--color-text-muted)] mt-0.5">Vendor bills that haven't been fully requested (total requested < total bill) - prevent double requests!</p>
                </div>
                <x-badge variant="warning" class="text-sm">{{ $unpaidBills->count() }} Bills</x-badge>
            </div>
        </x-slot:header>

        <div class="overflow-x-auto">
            <table id="vendorBillsTable" class="min-w-full divide-y divide-slate-200 dark:divide-[#2d2d2d]" data-per-page="8">
                <thead class="bg-[var(--bg-surface-secondary)]">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Vendor Bill</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Vendor</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Total Bill</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">
                            <div class="flex flex-col items-end">
                                <span>Already Requested</span>
                             </div>
                        </th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-rose-600 dark:text-rose-400 uppercase tracking-wider">
                            <div class="flex flex-col items-end">
                                <span>Not Requested</span>
                                <span class="text-[10px] font-normal normal-case">(Remaining)</span>
                            </div>
                        </th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody id="vendorBillsBody" class="bg-[var(--bg-panel)] divide-y divide-[var(--border-color)]">
                @foreach($unpaidBills as $bill)
                    <tr class="hover:bg-slate-50 dark:hover:bg-[#252525] transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <a href="{{ route('vendor-bills.show', $bill) }}" class="font-medium text-blue-600 dark:text-blue-400 hover:underline text-sm">
                                {{ $bill->vendor_bill_number }}
                            </a>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-400 text-sm">
                            {{ $bill->vendor->name }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-400 text-sm">
                            {{ $bill->bill_date->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right text-slate-900 dark:text-slate-100 text-sm font-semibold">
                            Rp {{ number_format($bill->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right">
                            <div class="text-emerald-600 dark:text-emerald-400 text-sm font-semibold">
                                Rp {{ number_format($bill->total_requested, 0, ',', '.') }}
                            </div>
                            @if($bill->paymentRequests->count() > 0)
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    {{ $bill->paymentRequests->count() }}x request
                                </div>
                            @else
                                <div class="text-xs text-slate-400 dark:text-slate-500 italic mt-0.5">
                                    None yet
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right">
                            <div class="text-rose-600 dark:text-rose-400 text-sm font-bold">
                                Rp {{ number_format($bill->remaining_to_request, 0, ',', '.') }}
                            </div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">
                                {{ number_format(($bill->total_requested / $bill->total_amount) * 100, 1) }}% requested
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center">
                            <a href="{{ route('payment-requests.create', ['vendor_bill_id' => $bill->id]) }}"
                               class="inline-flex items-center justify-center p-2 text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               title="Submit Payment Request">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <!-- Pagination for Vendor Bills -->
        <div id="vendorBillsPagination" class="flex items-center justify-between px-4 py-3 border-t border-[var(--border-color)]"></div>
    </x-card>
    @endif

    {{-- Section: Driver Advances Outstanding --}}
    @if(isset($outstandingAdvances) && $outstandingAdvances->count() > 0)
    <x-card class="mt-6 hidden md:block">
        <x-slot:header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Driver Advances Not Fully Requested</h2>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-0.5">Driver advances that haven't been fully requested (total requested < total amount) - prevent double requests!</p>
                </div>
                <x-badge variant="info" class="text-sm">{{ $outstandingAdvances->count() }} Advances</x-badge>
            </div>
        </x-slot:header>

        <div class="overflow-x-auto">
            <table id="driverAdvancesTable" class="min-w-full divide-y divide-slate-200 dark:divide-[#2d2d2d]" data-per-page="8">
                <thead class="bg-slate-50 dark:bg-[#252525]">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Number</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Driver</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Job Order</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Total Amount</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">
                            <div class="flex flex-col items-end">
                                <span>Already Requested</span>
                            </div>
                        </th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-rose-600 dark:text-rose-400 uppercase tracking-wider">
                            <div class="flex flex-col items-end">
                                <span>Not Requested</span>
                            </div>
                        </th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody id="driverAdvancesBody" class="bg-white dark:bg-[#1e1e1e] divide-y divide-slate-200 dark:divide-[#2d2d2d]">
                @foreach($outstandingAdvances as $advance)
                    <tr class="hover:bg-slate-50 dark:hover:bg-[#252525] transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <a href="{{ route('driver-advances.show', $advance) }}" class="font-medium text-blue-600 dark:text-blue-400 hover:underline text-sm">
                                {{ $advance->advance_number }}
                            </a>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-400 text-sm">
                            {{ $advance->driver->name }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-400 text-sm">
                            <a href="{{ route('job-orders.show', $advance->shipmentLeg->jobOrder) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                {{ $advance->shipmentLeg->jobOrder->job_number }}
                            </a>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-400 text-sm">
                            {{ $advance->advance_date->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right text-slate-900 dark:text-slate-100 text-sm font-semibold">
                            Rp {{ number_format($advance->amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right">
                            <div class="text-emerald-600 dark:text-emerald-400 text-sm font-semibold">
                                Rp {{ number_format($advance->total_requested, 0, ',', '.') }}
                            </div>
                            @if($advance->paymentRequests->count() > 0)
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    {{ $advance->paymentRequests->count() }}x request
                                </div>
                            @else
                                <div class="text-xs text-slate-400 dark:text-slate-500 italic mt-0.5">
                                    None yet
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right">
                            <div class="text-rose-600 dark:text-rose-400 text-sm font-bold">
                                Rp {{ number_format($advance->remaining_to_request, 0, ',', '.') }}
                            </div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">
                                {{ $advance->amount > 0 ? number_format(($advance->total_requested / $advance->amount) * 100, 1) : 0 }}% requested
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center">
                            @if($advance->status === 'pending')
                                <x-badge variant="warning" class="text-xs">Pending (DP)</x-badge>
                            @elseif($advance->status === 'dp_paid')
                                <x-badge variant="info" class="text-xs">DP Paid</x-badge>
                            @else
                                <x-badge variant="success" class="text-xs">{{ ucfirst($advance->status) }}</x-badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center">
                            @if($advance->status === 'pending')
                                <a href="{{ route('payment-requests.create', ['driver_advance_id' => $advance->id]) }}"
                                   class="inline-flex items-center justify-center gap-2 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed font-medium px-3 py-1.5 text-xs bg-amber-600 hover:bg-amber-700 text-white shadow-sm hover:shadow-md focus:ring-amber-500"
                                   title="Request DP Payment"
                                   style="background-color: #d97706 !important; color: white !important;">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Request DP
                                </a>
                            @elseif($advance->status === 'dp_paid')
                                <a href="{{ route('payment-requests.create', ['driver_advance_id' => $advance->id, 'type' => 'settlement']) }}"
                                   class="inline-flex items-center justify-center gap-2 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed font-medium px-3 py-1.5 text-xs bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm hover:shadow-md focus:ring-emerald-500"
                                   title="Request Settlement"
                                   style="background-color: #059669 !important; color: white !important;">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Request Settlement
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <!-- Pagination for Driver Advances -->
        <div id="driverAdvancesPagination" class="flex items-center justify-between px-4 py-3 border-t border-[var(--border-color)]"></div>
    </x-card>
    @endif

    {{-- Mobile Card View: Pengajuan Saya (non-sales only, sales pakai card di bawah) --}}
    @unless($isSalesUser)
    <div class="mt-6 space-y-3 md:hidden">
        @forelse($requests as $r)
            <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#111827] p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-xs font-semibold text-slate-900 dark:text-slate-100">
                            {{ $r->request_number }}
                        </div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400">
                            {{ $r->request_date->format('d M Y') }}
                        </div>
                        <div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                            @if($r->payment_type === 'vendor_bill' && $r->vendorBill)
                                {{ $r->vendorBill->vendor_bill_number }}
                            @elseif($r->payment_type === 'trucking' && $r->driverAdvance)
                                {{ $r->driverAdvance->advance_number }}
                            @else
                                {{ $r->description ?? 'Manual Payment' }}
                            @endif
                        </div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400">
                            @if($r->payment_type === 'trucking' && $r->driverAdvance)
                                {{ $r->driverAdvance->driver->name ?? '-' }}
                            @else
                                {{ $r->vendorBill?->vendor->name ?? $r->vendor?->name ?? '-' }}
                            @endif
                        </div>
                        @if($r->vendorBankAccount)
                        <div class="text-[11px] text-slate-500 dark:text-slate-400">
                            Rek: {{ $r->vendorBankAccount->bank_name }} - {{ $r->vendorBankAccount->account_number }}
                        </div>
                        @endif
                    </div>
                    <div class="text-right">
                        <x-badge :variant="match($r->status) {
                            'pending' => 'default',
                            'approved' => 'success',
                            'rejected' => 'danger',
                            'paid' => 'success',
                            default => 'default'
                        }" class="text-[10px]">
                            {{ strtoupper($r->status) }}
                        </x-badge>
                        <div class="mt-1 text-[11px] font-semibold text-slate-900 dark:text-slate-100">
                            Rp {{ number_format($r->amount, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-end gap-2">
                    @if(in_array($r->payment_type, ['vendor_bill', 'trucking']))
                        <button type="button"
                                onclick="showJobInfoPopup({{ $r->id }})"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-[11px] text-slate-700 dark:text-slate-200"
                                title="Job Order Info">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Info JO
                        </button>
                    @endif
                    <a href="{{ route('payment-requests.show', $r) }}"
                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-600 text-[11px] text-white"
                       title="Detail">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Detail
                    </a>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-slate-300 dark:border-slate-700 p-6 text-center text-slate-500 dark:text-slate-400">
                <div class="text-3xl mb-1">📭</div>
                <p class="text-sm">Belum ada payment request</p>
            </div>
        @endforelse
    </div>
    @endunless

    {{-- Desktop Table View: Pengajuan Saya --}}
    <x-card :noPadding="true" class="mt-6 hidden md:block" id="request-history">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-[#2d2d2d] bg-slate-50 dark:bg-[#252525]">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Request History</h2>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-0.5">All payment requests that have been created</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-[#2d2d2d]">
                <thead class="bg-slate-50 dark:bg-[#252525]">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Number</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Type</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Vendor Bill / Description</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Vendor</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Rekening</th>
                        @if(Auth::check() && (Auth::user()->role ?? 'admin') === 'super_admin')
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Requested By</th>
                        @endif
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Amount</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-[#1e1e1e] divide-y divide-slate-200 dark:divide-[#2d2d2d]">
                @forelse($requests as $r)
                    <tr class="hover:bg-slate-50 dark:hover:bg-[#252525] transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="font-medium text-slate-900 dark:text-slate-100 text-sm">{{ $r->request_number }}</div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <x-badge :variant="match($r->payment_type) {
                                'trucking' => 'info',
                                'manual' => 'warning',
                                default => 'default'
                            }" class="text-xs">
                                {{ strtoupper(str_replace('_', ' ', $r->payment_type)) }}
                            </x-badge>
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400 text-sm">
                            @if($r->payment_type === 'vendor_bill' && $r->vendorBill)
                                <a href="{{ route('vendor-bills.show', $r->vendorBill) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                    {{ $r->vendorBill->vendor_bill_number }}
                                </a>
                            @elseif($r->payment_type === 'trucking' && $r->driverAdvance)
                                <a href="{{ route('driver-advances.show', $r->driverAdvance) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                    {{ $r->driverAdvance->advance_number }}
                                </a>
                            @else
                                <span class="italic">{{ $r->description ?? 'Manual Payment' }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-400 text-sm">
                            @if($r->payment_type === 'trucking' && $r->driverAdvance)
                                {{ $r->driverAdvance->driver->name ?? '-' }}
                            @else
                                {{ $r->vendorBill?->vendor->name ?? $r->vendor?->name ?? '-' }}
                            @endif
                        </td>
                          <td class="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-400 text-sm">
                              @if($r->vendorBankAccount)
                                  <div class="font-medium text-slate-900 dark:text-slate-100 text-sm">{{ $r->vendorBankAccount->bank_name }}</div>
                                  <div class="text-[11px] text-slate-500 dark:text-slate-400 font-mono">{{ $r->vendorBankAccount->account_number }}</div>
                                  <div class="text-[11px] text-slate-500 dark:text-slate-400">a.n. {{ $r->vendorBankAccount->account_holder_name }}</div>
                              @elseif($r->payment_type === 'manual' && $r->notes)
                                  @php
                                      $manualPayee = $manualBank = $manualAccount = $manualHolder = null;
                                      foreach (preg_split("/\r\n|\n|\r/", $r->notes) as $line) {
                                          if (strpos($line, 'Manual payee info') !== false) {
                                              if (preg_match('/Payee:\s*([^|]+)/', $line, $m)) {
                                                  $manualPayee = trim($m[1]);
                                              }
                                              if (preg_match('/Bank:\s*([^|]+)/', $line, $m)) {
                                                  $manualBank = trim($m[1]);
                                              }
                                              if (preg_match('/No Rek:\s*([^|]+)/', $line, $m)) {
                                                  $manualAccount = trim($m[1]);
                                              }
                                              if (preg_match('/a\.n:\s*([^|]+)/', $line, $m)) {
                                                  $manualHolder = trim($m[1]);
                                              }
                                              break;
                                          }
                                      }
                                  @endphp
                                  @if($manualPayee || $manualAccount || $manualBank || $manualHolder)
                                      <div class="font-medium text-slate-900 dark:text-slate-100 text-sm">
                                          {{ $manualPayee ?? '-' }}
                                      </div>
                                      <div class="text-[11px] text-slate-500 dark:text-slate-400 font-mono">
                                          @if($manualBank) {{ $manualBank }} @endif
                                          @if($manualAccount) {{ $manualBank ? ' · ' : '' }}{{ $manualAccount }} @endif
                                      </div>
                                      @if($manualHolder)
                                          <div class="text-[11px] text-slate-500 dark:text-slate-400">a.n. {{ $manualHolder }}</div>
                                      @endif
                                  @else
                                      <span class="text-xs text-slate-400 dark:text-slate-600">-</span>
                                  @endif
                              @else
                                  <span class="text-xs text-slate-400 dark:text-slate-600">-</span>
                              @endif
                          </td>
                        @if(Auth::check() && (Auth::user()->role ?? 'admin') === 'super_admin')
                        <td class="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-400 text-sm">
                            {{ $r->requestedBy->name ?? '-' }}
                        </td>
                        @endif
                        <td class="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-400 text-sm">
                            {{ $r->request_date->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right text-slate-900 dark:text-slate-100 text-sm font-medium">
                            Rp {{ number_format($r->amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <x-badge :variant="match($r->status) {
                                'pending' => 'default',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'paid' => 'success',
                                default => 'default'
                            }" class="text-xs">{{ strtoupper($r->status) }}</x-badge>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center gap-1">
                                <button
                                    type="button"
                                    onclick="showJobInfoPopup({{ $r->id }})"
                                    class="p-1 text-sky-600 dark:text-sky-400 hover:bg-sky-50 dark:hover:bg-sky-950/30 rounded transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                                    title="Job Order Info"
                                    @if(!in_array($r->payment_type, ['vendor_bill', 'trucking'])) disabled aria-disabled="true" @endif
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>

                                <a
                                    href="{{ route('payment-requests.show', $r) }}"
                                    class="p-1 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition-colors"
                                    title="View"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>

                                @if($r->status === 'pending' && Auth::check() && (Auth::user()->role ?? 'admin') === 'super_admin')
                                    <form method="POST" action="{{ route('payment-requests.approve', $r) }}" class="inline">
                                        @csrf
                                        <button
                                            type="submit"
                                            onclick="return confirm('Approve this request?')"
                                            class="p-1 text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300 transition-colors"
                                            title="Approve"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                    </form>
                                @endif

                                @if($r->status === 'pending' && Auth::check() && (Auth::user()->id === $r->requested_by || (Auth::user()->role ?? 'admin') === 'super_admin'))
                                    <form
                                        method="POST"
                                        action="{{ route('payment-requests.destroy', $r) }}"
                                        onsubmit="return confirm('Are you sure you want to delete this request?')"
                                        class="inline"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="p-1 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition-colors"
                                            title="Delete"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ (Auth::check() && (Auth::user()->role ?? 'admin') === 'super_admin') ? 9 : 8 }}" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-12 h-12 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-sm">No payment requests yet</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    {{-- Mobile Card View --}}
    <div class="mt-6 space-y-4 md:hidden">
        @forelse($requests as $r)
        <x-card :noPadding="true">
            <div class="p-4">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex-1">
                        <div class="font-semibold text-slate-900 dark:text-slate-100">{{ $r->request_number }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $r->request_date->format('d M Y') }}</div>
                    </div>
                    <x-badge :variant="match($r->status) {
                        'pending' => 'default',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'paid' => 'success',
                        default => 'default'
                    }" class="text-xs">{{ strtoupper($r->status) }}</x-badge>
                </div>

                <div class="space-y-2 text-sm mb-4">
                    <div class="flex justify-between items-start">
                        <span class="text-slate-500 dark:text-slate-400">Type:</span>
                        <x-badge :variant="$r->payment_type === 'manual' ? 'warning' : 'default'" class="text-xs">
                            {{ $r->payment_type === 'manual' ? 'MANUAL' : 'VENDOR BILL' }}
                        </x-badge>
                    </div>
                    @if($r->payment_type === 'vendor_bill' && $r->vendorBill)
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Vendor Bill:</span>
                        <a href="{{ route('vendor-bills.show', $r->vendorBill) }}" class="text-blue-600 dark:text-blue-400 hover:underline font-medium">
                            {{ $r->vendorBill->vendor_bill_number }}
                        </a>
                    </div>
                    @else
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Description:</span>
                        <span class="text-slate-900 dark:text-slate-100 font-medium italic">{{ $r->description ?? '-' }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Vendor:</span>
                        <span class="text-slate-900 dark:text-slate-100 font-medium">{{ $r->vendorBill?->vendor->name ?? $r->vendor?->name ?? '-' }}</span>
                    </div>
                    @if(Auth::check() && (Auth::user()->role ?? 'admin') === 'super_admin')
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Requested:</span>
                        <span class="text-slate-900 dark:text-slate-100 font-medium">{{ $r->requestedBy->name ?? '-' }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between pt-2 border-t border-slate-200 dark:border-slate-700">
                        <span class="text-slate-500 dark:text-slate-400">Amount:</span>
                        <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($r->amount, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-3 border-t border-slate-200 dark:border-slate-700">
                    <button type="button" onclick="showJobInfoPopup({{ $r->id }})" class="flex-1 justify-center inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-sky-300 dark:border-sky-700 text-sky-600 dark:text-sky-400 bg-sky-50 dark:bg-sky-950/30 text-xs font-medium disabled:opacity-40 disabled:cursor-not-allowed" title="Job Order Info" @if(!in_array($r->payment_type, ['vendor_bill', 'trucking'])) disabled aria-disabled="true" @endif>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Job
                    </button>
                    <x-button :href="route('payment-requests.show', $r)" variant="outline" size="sm" class="flex-1 justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        View
                    </x-button>

                    @if($r->status === 'pending' && Auth::check() && (Auth::user()->role ?? 'admin') === 'super_admin')
                    <form method="POST" action="{{ route('payment-requests.approve', $r) }}" class="flex-1">
                        @csrf
                        <button type="submit" onclick="return confirm('Approve this request?')" class="w-full px-3 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium transition-colors flex items-center justify-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Approve
                        </button>
                    </form>
                    @endif

                    @if($r->status === 'pending' && Auth::check() && (Auth::user()->id === $r->requested_by || (Auth::user()->role ?? 'admin') === 'super_admin'))
                    <form method="POST" action="{{ route('payment-requests.destroy', $r) }}" onsubmit="return confirm('Are you sure you want to delete this request?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 rounded transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </x-card>
        @empty
        <x-card>
            <div class="text-center py-8 text-slate-500 dark:text-slate-400">
                <svg class="w-12 h-12 mx-auto mb-2 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-sm">No payment requests yet</p>
            </div>
        </x-card>
        @endforelse
    </div>

    <div class="mt-4">{{ $requests->links() }}</div>

        @include('components.payables.info-popup', ['id'=>'jobInfoPopup','title'=>'Related Job Order Information'])
        @include('components.payables.popup-scripts')
        <script>
            function renderJobOrders(data){
                if(!data.job_orders || !data.job_orders.length){
                    return '<div class="text-center py-12 text-slate-500 dark:text-slate-400 text-sm">No related job orders</div>';
                }
                return data.job_orders.map(j => `
                    <div class=\"border border-slate-200 dark:border-slate-700 rounded-lg p-3 md:p-4 bg-slate-50 dark:bg-slate-800/40\">
                        <div class=\"flex flex-wrap items-center justify-between gap-2 mb-2\">
                            <div class=\"font-semibold text-xs md:text-sm text-slate-900 dark:text-slate-100\">${j.job_number}</div>
                            <span class=\"text-[10px] md:text-xs px-2 py-0.5 rounded bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400\">${(j.status||'').toUpperCase()}</span>
                        </div>
                        <div class=\"grid grid-cols-1 md:grid-cols-2 gap-1 md:gap-2 text-[11px] md:text-xs text-slate-600 dark:text-slate-400\">
                            <div>📅 Order: ${j.order_date}</div>
                            <div>👤 Customer: ${j.customer ?? '-'} </div>
                            <div>🚩 Origin: ${j.origin ?? '-'} </div>
                            <div>🎯 Destination: ${j.destination ?? '-'} </div>
                            <div class=\"md:col-span-2\">📦 Cargo: ${j.cargo_summary || '-'} </div>
                        </div>
                    </div>
                `).join('');
            }
            function showJobInfoPopup(id){
                showPayablesPopup('jobInfoPopup', `/payment-requests/${id}/job-info`, renderJobOrders);
            }
        </script>

        <script>
            // Client-side pagination for outstanding tables
            document.addEventListener('DOMContentLoaded', function() {
                setupTablePagination('vendorBillsBody', 'vendorBillsPagination', 8);
                setupTablePagination('driverAdvancesBody', 'driverAdvancesPagination', 8);
            });

            function setupTablePagination(tbodyId, paginationId, perPage) {
                const tbody = document.getElementById(tbodyId);
                const paginationContainer = document.getElementById(paginationId);
                
                if (!tbody || !paginationContainer) return;
                
                const rows = Array.from(tbody.querySelectorAll('tr'));
                const totalRows = rows.length;
                const totalPages = Math.ceil(totalRows / perPage);
                let currentPage = 1;

                if (totalRows <= perPage) {
                    paginationContainer.style.display = 'none';
                    return;
                }

                function showPage(page) {
                    currentPage = page;
                    const start = (page - 1) * perPage;
                    const end = start + perPage;

                    rows.forEach((row, index) => {
                        row.style.display = (index >= start && index < end) ? '' : 'none';
                    });

                    renderPagination();
                }

                function renderPagination() {
                    const start = (currentPage - 1) * perPage + 1;
                    const end = Math.min(currentPage * perPage, totalRows);

                    paginationContainer.innerHTML = `
                        <div class="text-sm text-slate-600 dark:text-slate-400">
                            Showing <span class="font-medium">${start}</span> to <span class="font-medium">${end}</span> of <span class="font-medium">${totalRows}</span> results
                        </div>
                        <div class="flex items-center gap-1">
                            ${currentPage > 1 ? `
                                <button type="button" onclick="window.paginateTo_${tbodyId}(${currentPage - 1})" 
                                    class="px-3 py-1.5 text-xs font-medium rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                    Previous
                                </button>
                            ` : `
                                <button type="button" disabled 
                                    class="px-3 py-1.5 text-xs font-medium rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 cursor-not-allowed">
                                    Previous
                                </button>
                            `}
                            ${generatePageNumbers()}
                            ${currentPage < totalPages ? `
                                <button type="button" onclick="window.paginateTo_${tbodyId}(${currentPage + 1})" 
                                    class="px-3 py-1.5 text-xs font-medium rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                    Next
                                </button>
                            ` : `
                                <button type="button" disabled 
                                    class="px-3 py-1.5 text-xs font-medium rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 cursor-not-allowed">
                                    Next
                                </button>
                            `}
                        </div>
                    `;
                }

                function generatePageNumbers() {
                    let html = '';
                    const maxVisible = 5;
                    let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
                    let endPage = Math.min(totalPages, startPage + maxVisible - 1);
                    
                    if (endPage - startPage + 1 < maxVisible) {
                        startPage = Math.max(1, endPage - maxVisible + 1);
                    }

                    for (let i = startPage; i <= endPage; i++) {
                        if (i === currentPage) {
                            html += `<span class="px-3 py-1.5 text-xs font-medium rounded-lg bg-indigo-600 text-white">${i}</span>`;
                        } else {
                            html += `<button type="button" onclick="window.paginateTo_${tbodyId}(${i})" 
                                class="px-3 py-1.5 text-xs font-medium rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">${i}</button>`;
                        }
                    }
                    return html;
                }

                // Expose pagination function globally for onclick
                window['paginateTo_' + tbodyId] = showPage;

                // Initialize first page
                showPage(1);
            }
        </script>
@endsection
