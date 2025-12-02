@php
    /** @var \App\Models\Finance\VendorBill $vendorBill */
    $vendor = $vendorBill->vendor;
    $maxAmount = $vendorBill->remaining_to_request ?? $vendorBill->remaining ?? $vendorBill->total_amount;
    $defaultAmount = old('amount', $maxAmount);
@endphp

<div class="max-w-4xl mx-auto">
    <x-card
        title="Vendor Bill Payment Request"
        subtitle="Create payment request for this vendor bill"
    >
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 text-sm">
            <div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Vendor Bill Number</div>
                <div class="font-medium text-slate-900 dark:text-slate-100">
                    {{ $vendorBill->vendor_bill_number }}
                </div>
            </div>
            <div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Vendor</div>
                <div class="font-medium text-slate-900 dark:text-slate-100">
                    {{ $vendor?->name ?? '-' }}
                </div>
            </div>
            <div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Bill Date</div>
                <div class="font-medium text-slate-900 dark:text-slate-100">
                    {{ optional($vendorBill->bill_date)->format('d M Y') ?: '-' }}
                </div>
            </div>
            <div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Total Amount</div>
                <div class="font-semibold text-slate-900 dark:text-slate-100">
                    Rp {{ number_format($vendorBill->total_amount, 0, ',', '.') }}
                </div>
            </div>
            <div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Already Requested</div>
                <div class="font-semibold text-amber-600 dark:text-amber-400">
                    Rp {{ number_format($vendorBill->total_requested ?? 0, 0, ',', '.') }}
                </div>
            </div>
            <div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Remaining to Request</div>
                <div class="font-semibold text-emerald-600 dark:text-emerald-400">
                    Rp {{ number_format($maxAmount, 0, ',', '.') }}
                </div>
            </div>
        </div>

        @if($vendor && $vendor->activeBankAccounts->count() === 0)
            <div class="mb-6">
                <x-alert variant="warning" title="Vendor belum punya rekening bank">
                    <p class="text-sm">
                        Vendor ini belum memiliki rekening bank di master data. Untuk mempermudah proses pembayaran,
                        tambahkan nomor rekening di menu <strong>Master &gt; Vendors</strong>.
                    </p>
                    <p class="mt-2">
                        <a
                            href="{{ route('vendors.edit', $vendor) }}"
                            class="inline-flex items-center gap-1 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline"
                            target="_blank"
                        >
                            Edit vendor &amp; tambah rekening
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M14 3h7m0 0v7m0-7L10 14m-4 7h14" />
                            </svg>
                        </a>
                    </p>
                </x-alert>
            </div>
        @endif

        <form method="POST" action="{{ route('payment-requests.store') }}">
            @csrf
            <input type="hidden" name="payment_type" value="vendor_bill">
            <input type="hidden" name="vendor_bill_id" value="{{ $vendorBill->id }}">
            @if($vendor)
                <input type="hidden" name="vendor_id" value="{{ $vendor->id }}">
            @endif

            <div class="space-y-6">
                @if($vendor && $vendor->activeBankAccounts->count() > 0)
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Transfer Destination Account
                        </label>
                        <select
                            name="vendor_bank_account_id"
                            class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                            <option value="">-- Select Bank Account --</option>
                            @foreach($vendor->activeBankAccounts as $account)
                                <option
                                    value="{{ $account->id }}"
                                    @selected(old('vendor_bank_account_id') == $account->id)
                                >
                                    {{ $account->formatted_account }}
                                    @if($account->is_primary)
                                        (Primary)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('vendor_bank_account_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Payment Description (optional)
                    </label>
                    <input
                        type="text"
                        name="description"
                        value="{{ old('description') }}"
                        placeholder="Example: First payment for vendor bill {{ $vendorBill->vendor_bill_number }}"
                        class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('description') border-red-500 @enderror"
                    >
                    @error('description')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Payment Amount <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="amount_display"
                        placeholder="Enter payment amount"
                        value="{{ $defaultAmount ? number_format($defaultAmount, 0, ',', '.') : '' }}"
                        class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('amount') border-red-500 @enderror"
                    >
                    <input
                        type="hidden"
                        name="amount"
                        id="amount_input"
                        value="{{ $defaultAmount }}"
                    >
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Maximum amount you can request now:
                        <span class="font-semibold">
                            Rp {{ number_format($maxAmount, 0, ',', '.') }}
                        </span>
                    </p>
                    @error('amount')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Additional Notes
                    </label>
                    <textarea
                        name="notes"
                        rows="4"
                        placeholder="Add notes for this payment request (optional)"
                        class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
                    <x-button
                        type="submit"
                        variant="primary"
                        size="sm"
                        class="w-full sm:w-auto justify-center normal-case"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Submit Payment Request
                    </x-button>
                    <x-button
                        :href="route('vendor-bills.show', $vendorBill)"
                        variant="outline"
                        size="sm"
                        class="w-full sm:w-auto justify-center normal-case"
                    >
                        Cancel
                    </x-button>
                </div>
            </div>
        </form>
    </x-card>
</div>
