@php
    /** @var \App\Models\Operations\DriverAdvance $driverAdvance */
    $maxAmount = $driverAdvance->remaining_to_request ?? $driverAdvance->remaining_amount ?? $driverAdvance->amount;
    $defaultAmount = old('amount', $maxAmount);
@endphp

<div class="max-w-3xl mx-auto">
    <x-card
        title="Driver Advance Payment Request"
        subtitle="Request DP payment for this driver advance"
    >
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 text-sm">
            <div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Advance Number</div>
                <div class="font-medium text-slate-900 dark:text-slate-100">
                    {{ $driverAdvance->advance_number }}
                </div>
            </div>
            <div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Driver</div>
                <div class="font-medium text-slate-900 dark:text-slate-100">
                    {{ $driverAdvance->driver->name ?? '-' }}
                </div>
            </div>
            <div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Advance Date</div>
                <div class="font-medium text-slate-900 dark:text-slate-100">
                    {{ optional($driverAdvance->advance_date)->format('d M Y') ?: '-' }}
                </div>
            </div>
            <div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Total Trip Money</div>
                <div class="font-semibold text-slate-900 dark:text-slate-100">
                    Rp {{ number_format($driverAdvance->amount, 0, ',', '.') }}
                </div>
            </div>
            <div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Already Requested</div>
                <div class="font-semibold text-amber-600 dark:text-amber-400">
                    Rp {{ number_format($driverAdvance->total_requested ?? 0, 0, ',', '.') }}
                </div>
            </div>
            <div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Remaining to Request</div>
                <div class="font-semibold text-emerald-600 dark:text-emerald-400">
                    Rp {{ number_format($maxAmount, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('payment-requests.store') }}" id="driver-payment-form">
            @csrf
            <input type="hidden" name="payment_type" value="trucking">
            <input type="hidden" name="driver_advance_id" value="{{ $driverAdvance->id }}">

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Payment Amount <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="amount_display"
                        placeholder="Enter DP amount"
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
                        placeholder="Add notes for this DP payment request (optional)"
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
                        Submit DP Payment Request
                    </x-button>
                    <x-button
                        :href="route('driver-advances.show', $driverAdvance)"
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

