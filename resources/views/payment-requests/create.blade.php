@extends('layouts.app', ['title' => 'Submit Payment'])

@section('content')
    <div class="mb-4 flex items-start gap-3">
        @if($driverAdvance ?? false)
            <x-button :href="route('driver-advances.show', $driverAdvance)" variant="ghost" size="sm" class="normal-case">
                Back
            </x-button>
        @elseif($vendorBill ?? false)
            <x-button :href="route('vendor-bills.show', $vendorBill)" variant="ghost" size="sm" class="normal-case">
                Back
            </x-button>
        @else
            <x-button :href="route('payment-requests.index')" variant="ghost" size="sm" class="normal-case">
                Back
            </x-button>
        @endif
        <div>
            <div class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                @if($vendorBill)
                    Submit Payment for Vendor Bill
                @elseif($driverAdvance ?? false)
                    Submit Payment for Driver Advance
                @else
                    Submit Manual Payment
                @endif
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                @if($vendorBill)
                    Create payment request for vendor bill
                @elseif($driverAdvance ?? false)
                    Create payment request for driver advance down payment
                @else
                    Create manual payment request outside vendor bill
                @endif
            </p>
        </div>
    </div>

    @if($driverAdvance ?? false)
    {{-- FORM UNTUK DRIVER ADVANCE --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Form --}}
        <div class="lg:col-span-2">
            @if($errors->any() && !$errors->has('amount'))
            <div class="mb-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="flex-1">
                        <h4 class="text-sm font-semibold text-red-800 dark:text-red-200 mb-1">Error Occurred</h4>
                        <ul class="text-xs text-red-700 dark:text-red-300 list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif

            <x-card title="Driver Payment Request Form">
                <form method="POST" action="{{ route('payment-requests.store') }}" id="driver-payment-form">
                    @csrf
                    <input type="hidden" name="payment_type" value="trucking">
                    <input type="hidden" name="driver_advance_id" value="{{ $driverAdvance->id }}">

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Down Payment Amount <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                id="amount_display"
                                placeholder="Enter amount"
                                value="{{ old('amount') ? number_format(old('amount'), 0, ',', '.') : number_format($driverAdvance->amount * 0.7, 0, ',', '.') }}"
                                class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('amount') border-red-500 @enderror"
                            >
                            <input type="hidden" name="amount" id="amount_input" value="{{ old('amount', $driverAdvance->amount * 0.7) }}">
                            @error('amount')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                Recommended 70%: Rp {{ number_format($driverAdvance->amount * 0.7, 0, ',', '.') }} | Maximum: Rp {{ number_format($driverAdvance->amount, 0, ',', '.') }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Notes</label>
                            <textarea
                                name="notes"
                                rows="4"
                                placeholder="Add notes for this request (optional)"
                                class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            >{{ old('notes', $driverAdvance->notes) }}</textarea>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center gap-3 pt-4">
                            <x-button type="submit" variant="primary" size="sm" class="w-full sm:w-auto justify-center normal-case">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Submit Payment
                            </x-button>
                            <x-button :href="route('driver-advances.show', $driverAdvance)" variant="outline" size="sm" class="w-full sm:w-auto justify-center normal-case">
                                Cancel
                            </x-button>
                        </div>
                    </div>
                </form>
            </x-card>
        </div>

        {{-- Driver Advance Info --}}
        <div>
            <x-card title="Driver Advance Information">
                <div class="space-y-3 text-sm">
                    <div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Number</div>
                        <div class="font-medium text-slate-900 dark:text-slate-100">{{ $driverAdvance->advance_number }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Driver</div>
                        <div class="font-medium text-slate-900 dark:text-slate-100">{{ $driverAdvance->driver->name }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Date</div>
                        <div class="font-medium text-slate-900 dark:text-slate-100">{{ $driverAdvance->advance_date->format('d M Y') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Job Order</div>
                        <div class="font-medium text-slate-900 dark:text-slate-100">{{ $driverAdvance->shipmentLeg->jobOrder->job_number ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Status</div>
                        <x-badge :variant="$driverAdvance->status === 'pending' ? 'warning' : ($driverAdvance->status === 'settled' ? 'success' : 'info')" class="text-xs">
                            {{ strtoupper($driverAdvance->status) }}
                        </x-badge>
                    </div>

                    <div class="pt-3 border-t border-slate-200 dark:border-slate-700">
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Total Advance Amount</div>
                        <div class="font-semibold text-slate-900 dark:text-slate-100">Rp {{ number_format($driverAdvance->amount, 0, ',', '.') }}</div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
    @elseif($vendorBill)
    {{-- FORM UNTUK VENDOR BILL --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Form --}}
        <div class="lg:col-span-2">
            <x-card title="Request Form">
                <form method="POST" action="{{ route('payment-requests.store') }}">
                    @csrf
                    <input type="hidden" name="payment_type" value="vendor_bill">
                    <input type="hidden" name="vendor_bill_id" value="{{ $vendorBill->id }}">

                    <div class="space-y-4">
                        {{-- Rekening Vendor --}}
                        @if($vendorBill->vendor->activeBankAccounts->count() > 0)
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Transfer Destination Account
                                <span class="text-xs text-slate-500 dark:text-slate-400 font-normal">(Select vendor account)</span>
                            </label>
                            <select
                                name="vendor_bank_account_id"
                                class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            >
                                <option value="">-- Select Account --</option>
                                @foreach($vendorBill->vendor->activeBankAccounts as $account)
                                    <option value="{{ $account->id }}" @selected(old('vendor_bank_account_id') == $account->id || $account->is_primary)>
                                        {{ $account->bank_name }} - {{ $account->account_number }} ({{ $account->account_holder_name }})
                                        @if($account->is_primary) ⭐ @endif
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                ⭐ = Primary vendor account
                            </p>
                        </div>
                        @else
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <div class="flex-1">
                                    <h4 class="text-sm font-semibold text-yellow-800 dark:text-yellow-200 mb-1">
                                        Vendor Has No Bank Account
                                    </h4>
                                    <p class="text-xs text-yellow-700 dark:text-yellow-300">
                                        Please add a bank account for vendor <strong>{{ $vendorBill->vendor->name }}</strong> first on the vendor master page.
                                    </p>
                                    <a href="{{ route('vendors.edit', $vendorBill->vendor) }}" target="_blank" class="text-xs text-yellow-800 dark:text-yellow-200 font-medium hover:underline inline-flex items-center gap-1 mt-2">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                        Edit Vendor & Add Account
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Request Amount <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                id="amount_display"
                                placeholder="Enter amount"
                                value="{{ old('amount') ? number_format(old('amount'), 0, ',', '.') : number_format($vendorBill->remaining_to_request, 0, ',', '.') }}"
                                class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('amount') border-red-500 @enderror"
                            >
                            <input type="hidden" name="amount" id="amount_input" value="{{ old('amount', $vendorBill->remaining_to_request) }}">
                            @error('amount')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                Maximum (remaining not yet requested): Rp {{ number_format($vendorBill->remaining_to_request, 0, ',', '.') }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Notes</label>
                            <textarea
                                name="notes"
                                rows="4"
                                placeholder="Add notes for this request (optional)"
                                class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            >{{ old('notes') }}</textarea>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center gap-3 pt-4">
                            <x-button type="submit" variant="primary" size="sm" class="w-full sm:w-auto justify-center normal-case">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Submit Payment
                            </x-button>
                            <x-button :href="route('vendor-bills.show', $vendorBill)" variant="outline" size="sm" class="w-full sm:w-auto justify-center normal-case">
                                Cancel
                            </x-button>
                        </div>
                    </div>
                </form>
            </x-card>
        </div>

        {{-- Vendor Bill Info --}}
        <div>
            <x-card title="Vendor Bill Information">
                <div class="space-y-3 text-sm">
                    <div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Number</div>
                        <div class="font-medium text-slate-900 dark:text-slate-100">{{ $vendorBill->vendor_bill_number }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Vendor</div>
                        <div class="font-medium text-slate-900 dark:text-slate-100">{{ $vendorBill->vendor->name }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Date</div>
                        <div class="font-medium text-slate-900 dark:text-slate-100">{{ $vendorBill->bill_date->format('d M Y') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Status</div>
                        <x-badge :variant="match($vendorBill->status) {
                            'draft' => 'default',
                            'received' => 'warning',
                            'partially_paid' => 'warning',
                            'paid' => 'success',
                            'cancelled' => 'danger',
                            default => 'default'
                        }" class="text-xs">{{ strtoupper($vendorBill->status) }}</x-badge>
                    </div>

                    <div class="pt-3 border-t border-slate-200 dark:border-slate-700">
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Total Bill</div>
                        <div class="font-semibold text-slate-900 dark:text-slate-100">Rp {{ number_format($vendorBill->total_amount, 0, ',', '.') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-blue-500 dark:text-blue-400 mb-1 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Already Requested
                        </div>
                        <div class="font-semibold text-blue-600 dark:text-blue-400">
                            Rp {{ number_format($vendorBill->total_requested, 0, ',', '.') }}
                            @if($vendorBill->paymentRequests->count() > 0)
                                <span class="text-xs text-slate-500">({{ $vendorBill->paymentRequests->count() }}x)</span>
                            @endif
                        </div>
                    </div>
                    <div class="pt-2 border-t border-slate-200 dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-xs text-rose-500 dark:text-rose-400 mb-1 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    Not Yet Requested (Remaining)
                                </div>
                                <div class="text-xl font-bold text-rose-600 dark:text-rose-400">Rp {{ number_format($vendorBill->remaining_to_request, 0, ',', '.') }}</div>
                            </div>
                            <div class="text-right text-[10px] text-slate-500 dark:text-slate-400">Maximum to avoid duplication</div>
                        </div>
                        <button type="button" onclick="openContextInfo()" class="mt-3 w-full px-3 py-2 rounded-lg bg-indigo-50 dark:bg-indigo-950/30 border border-indigo-200 dark:border-indigo-800 text-indigo-700 dark:text-indigo-300 text-xs font-medium flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 18a6 6 0 100-12 6 6 0 000 12z"/></svg>
                            Cargo & Leg Info
                        </button>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
    {{-- Popup info context (muatan & legs) --}}
    <div id="contextInfoPopup" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" onclick="closeContextInfo()"></div>
        <div class="relative bg-white dark:bg-slate-900 w-full max-w-lg rounded-xl shadow-xl p-5 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Cargo & Leg Summary</h3>
                <button type="button" onclick="closeContextInfo()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            @php
                $relatedLegs = collect();
                foreach($vendorBill->items as $it){ if($it->shipmentLeg){ $relatedLegs->push($it->shipmentLeg); } }
                $relatedLegs = $relatedLegs->unique('id');
            @endphp
            <div class="space-y-3 max-h-[55vh] overflow-y-auto">
                @forelse($relatedLegs as $leg)
                    <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-3 bg-slate-50 dark:bg-slate-800/40">
                        <div class="flex items-center justify-between">
                            <div class="font-semibold text-sm text-slate-900 dark:text-slate-100">Leg {{ $leg->leg_code }} (#{!! $leg->leg_number !!})</div>
                            <x-badge :variant="match($leg->status){'pending'=>'default','in_transit'=>'warning','delivered'=>'success','cancelled'=>'danger',default=>'default'}" class="text-[10px]">{{ strtoupper(str_replace('_',' ',$leg->status)) }}</x-badge>
                        </div>
                        <div class="text-xs text-slate-600 dark:text-slate-400 mt-1">
                            Load: {{ $leg->load_date->format('d M Y') }}
                            • Unload: {{ optional($leg->unload_date)->format('d M Y') ?: '-' }}
                        </div>
                        <div class="text-xs text-slate-600 dark:text-slate-400 mt-1">Qty: {{ $leg->quantity }} • Category: {{ ucfirst($leg->cost_category) }}</div>
                        @if($leg->truck)
                            <div class="text-xs text-slate-600 dark:text-slate-400 mt-1">Truck: {{ $leg->truck->plate_number }} • Driver: {{ $leg->driver?->name ?? '-' }}</div>
                        @endif
                        @if($leg->vendor)
                            <div class="text-xs text-slate-600 dark:text-slate-400 mt-1">Vendor: {{ $leg->vendor->name }}</div>
                        @endif
                    </div>
                @empty
                    <div class="text-sm text-slate-500 dark:text-slate-400">No related legs.</div>
                @endforelse
            </div>
        </div>
    </div>
    <script>
        function openContextInfo(){ document.getElementById('contextInfoPopup').classList.remove('hidden'); }
        function closeContextInfo(){ document.getElementById('contextInfoPopup').classList.add('hidden'); }
    </script>
    @else
    {{-- FORM MANUAL PAYMENT REQUEST --}}
    <div class="max-w-3xl mx-auto">
        <x-card title="Manual Payment Request Form" subtitle="Create payment request outside vendor bill">
            <form method="POST" action="{{ route('payment-requests.store') }}">
                @csrf
                <input type="hidden" name="payment_type" value="manual">

                <div class="space-y-6">
                    {{-- Vendor Selection --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Vendor <span class="text-red-500">*</span></label>
                        <select
                            name="vendor_id"
                            id="vendor_select"
                            required
                            class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('vendor_id') border-red-500 @enderror"
                        >
                            <option value="">-- Select Vendor --</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}"
                                    data-bank-accounts="{{ $vendor->activeBankAccounts->toJson() }}"
                                    @selected(old('vendor_id') == $vendor->id)>
                                    {{ $vendor->name }} ({{ $vendor->vendor_type }})
                                </option>
                            @endforeach
                        </select>
                        @error('vendor_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Rekening Vendor (Dynamic based on selected vendor) --}}
                    <div id="bank_account_section" class="hidden">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Transfer Destination Account
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-normal">(Select vendor account)</span>
                        </label>
                        <select
                            name="vendor_bank_account_id"
                            id="bank_account_select"
                            class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                            <option value="">-- Select Account --</option>
                        </select>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            ⭐ = Primary vendor account
                        </p>
                    </div>

                    {{-- No Bank Account Warning --}}
                    <div id="no_bank_warning" class="hidden bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div class="flex-1">
                                <h4 class="text-sm font-semibold text-yellow-800 dark:text-yellow-200 mb-1">
                                    Vendor Has No Bank Account
                                </h4>
                                <p class="text-xs text-yellow-700 dark:text-yellow-300">
                                    Please add a bank account for this vendor first.
                                </p>
                                <a href="#" id="edit_vendor_link" target="_blank" class="text-xs text-yellow-800 dark:text-yellow-200 font-medium hover:underline inline-flex items-center gap-1 mt-2">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                    Edit Vendor & Add Account
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Payment Description <span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            name="description"
                            value="{{ old('description') }}"
                            required
                            placeholder="Example: Office rent payment, Operational expenses, etc"
                            class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('description') border-red-500 @enderror"
                        >
                        @error('description')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Amount --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Payment Amount <span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            id="amount_display_manual"
                            placeholder="Enter payment amount"
                            value="{{ old('amount') ? number_format(old('amount'), 0, ',', '.') : '' }}"
                            class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('amount') border-red-500 @enderror"
                        >
                        <input type="hidden" name="amount" id="amount_input_manual" value="{{ old('amount', 0) }}">
                        @error('amount')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Notes --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Additional Notes</label>
                        <textarea
                            name="notes"
                            rows="4"
                            placeholder="Add notes for this request (optional)"
                            class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >{{ old('notes') }}</textarea>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex flex-col sm:flex-row items-center gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
                        <x-button type="submit" variant="primary" size="sm" class="w-full sm:w-auto justify-center normal-case">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
                            Submit Payment
                        </x-button>
                        <x-button :href="route('payment-requests.index')" variant="outline" size="sm" class="w-full sm:w-auto justify-center normal-case">
                            Cancel
            </x-button>
        </div>
                </div>
            </form>
    </x-card>
    </div>

    @endif

    <script>
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // Script for MANUAL payment request
    @if(!$vendorBill && $vendors)
    const vendorSelect = document.getElementById('vendor_select');
    const bankAccountSection = document.getElementById('bank_account_section');
    const bankAccountSelect = document.getElementById('bank_account_select');
    const noBankWarning = document.getElementById('no_bank_warning');
    const editVendorLink = document.getElementById('edit_vendor_link');

    if (vendorSelect) {
        vendorSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];

            if (!selectedOption.value) {
                bankAccountSection.classList.add('hidden');
                noBankWarning.classList.add('hidden');
                return;
            }

            const bankAccounts = JSON.parse(selectedOption.getAttribute('data-bank-accounts') || '[]');

            // Clear existing options
            bankAccountSelect.innerHTML = '<option value="">-- Select Account --</option>';

            if (bankAccounts.length > 0) {
                // Show bank account dropdown
                bankAccountSection.classList.remove('hidden');
                noBankWarning.classList.add('hidden');

                bankAccounts.forEach(account => {
                    const option = document.createElement('option');
                    option.value = account.id;
                    option.textContent = `${account.bank_name} - ${account.account_number} (${account.account_holder_name})${account.is_primary ? ' ⭐' : ''}`;
                    if (account.is_primary) {
                        option.selected = true;
                    }
                    bankAccountSelect.appendChild(option);
                });
            } else {
                // Show warning - no bank accounts
                bankAccountSection.classList.add('hidden');
                noBankWarning.classList.remove('hidden');
                editVendorLink.href = `/vendors/${selectedOption.value}/edit`;
            }
        });

        // Trigger change on load if vendor already selected
        if (vendorSelect.value) {
            vendorSelect.dispatchEvent(new Event('change'));
        }
    }

    // Format amount input for manual
    const amountDisplayManual = document.getElementById('amount_display_manual');
    const amountInputManual = document.getElementById('amount_input_manual');

    if (amountDisplayManual && amountInputManual) {
        amountDisplayManual.addEventListener('input', function() {
            let value = this.value.replace(/\./g, '');
            value = value.replace(/[^\d]/g, '');

            if (value) {
                this.value = formatNumber(value);
                amountInputManual.value = value;
            } else {
                this.value = '';
                amountInputManual.value = '';
            }
        });

        // Initialize with old value
        if (amountInputManual.value && amountInputManual.value != '0') {
            amountDisplayManual.value = formatNumber(amountInputManual.value);
        }
    }
    @endif

    // Script for DRIVER ADVANCE payment request
    @if($driverAdvance ?? false)
    // Setup formatted input for amount
    const amountDisplay = document.getElementById('amount_display');
    const amountInput = document.getElementById('amount_input');
    const maxAmount = {{ $driverAdvance->amount }};

    if (amountDisplay && amountInput) {
        amountDisplay.addEventListener('input', function() {
            let value = this.value.replace(/\./g, '');
            value = value.replace(/[^\d]/g, '');

            if (value) {
                const numValue = parseFloat(value);
                if (numValue > maxAmount) {
                    value = maxAmount.toString();
                }
                this.value = formatNumber(value);
                amountInput.value = value;
            } else {
                this.value = '';
                amountInput.value = '';
            }
        });

        // Initialize with current value
        if (amountInput.value && amountInput.value != '0') {
            amountDisplay.value = formatNumber(amountInput.value);
        }
    }

    // Debug form submission
    const driverPaymentForm = document.getElementById('driver-payment-form');
    if (driverPaymentForm) {
        driverPaymentForm.addEventListener('submit', function(e) {
            console.log('Form submitting with data:', {
                payment_type: document.querySelector('input[name="payment_type"]')?.value,
                driver_advance_id: document.querySelector('input[name="driver_advance_id"]')?.value,
                amount: document.querySelector('input[name="amount"]')?.value,
                notes: document.querySelector('textarea[name="notes"]')?.value
            });

            // Validate amount is not empty
            const amountValue = document.querySelector('input[name="amount"]')?.value;
            if (!amountValue || amountValue === '0' || amountValue === '') {
                e.preventDefault();
                alert('Payment amount must be filled!');
                return false;
            }
        });
    }
    @endif

    // Script for VENDOR BILL payment request
    @if($vendorBill)
    // Setup formatted input for amount
    const amountDisplay = document.getElementById('amount_display');
    const amountInput = document.getElementById('amount_input');
    const maxAmount = {{ $vendorBill->remaining }};

    if (amountDisplay && amountInput) {
        amountDisplay.addEventListener('input', function() {
            let value = this.value.replace(/\./g, '');
            value = value.replace(/[^\d]/g, '');

            if (value) {
                const numValue = parseFloat(value);
                if (numValue > maxAmount) {
                    value = maxAmount.toString();
                }
                this.value = formatNumber(value);
                amountInput.value = value;
            } else {
                this.value = '';
                amountInput.value = '';
            }
        });

        // Initialize with current value
        if (amountInput.value && amountInput.value != '0') {
            amountDisplay.value = formatNumber(amountInput.value);
        }
    }
    @endif
    </script>
@endsection
