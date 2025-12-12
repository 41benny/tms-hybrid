@extends('layouts.app', ['title' => 'Detail Driver Advance'])

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-3">
        <x-button :href="route('driver-advances.index')" variant="ghost" size="sm">
            ← Back
        </x-button>
        <div>
            <div class="text-xl font-bold text-slate-900 dark:text-slate-100">Driver Advance #{{ $advance->advance_number }}</div>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                {{ $advance->driver->name }} • {{ $advance->advance_date->format('d M Y') }}
            </p>
        </div>
    </div>

    {{-- Status Card --}}
    <x-card>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 dark:text-slate-400">Status</p>
                <div class="mt-2">
                    @if($advance->status === 'pending')
                        <x-badge variant="warning" class="text-lg px-4 py-2">⏳ Pending - Awaiting DP Payment</x-badge>
                    @elseif($advance->status === 'dp_paid')
                        <x-badge variant="info" class="text-lg px-4 py-2">🚚 DP Paid - Driver On The Road</x-badge>
                    @else
                        <x-badge variant="success" class="text-lg px-4 py-2">✅ Settled - Completed</x-badge>
                    @endif
                </div>
            </div>
            <div class="text-right">
                <p class="text-sm text-slate-500 dark:text-slate-400">Job Order</p>
                <a href="{{ route('job-orders.show', $advance->shipmentLeg->jobOrder) }}" class="text-lg font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                    {{ $advance->shipmentLeg->jobOrder->job_number }}
                </a>
            </div>
        </div>
    </x-card>

    {{-- Amount Summary --}}
    <x-card title="Payment Summary">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-4">
                <p class="text-sm text-slate-500 dark:text-slate-400">Total Trip Money</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1">
                    Rp {{ number_format($advance->amount, 0, ',', '.') }}
                </p>
            </div>

            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                <p class="text-sm text-slate-500 dark:text-slate-400">Down Payment Paid</p>
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">
                    Rp {{ number_format($advance->dp_amount, 0, ',', '.') }}
                </p>
                @if($advance->dp_paid_date)
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $advance->dp_paid_date->format('d M Y') }}</p>
                @endif
            </div>

            <div class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-4">
                <p class="text-sm text-slate-500 dark:text-slate-400">Remaining</p>
                <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">
                    Rp {{ number_format($advance->remaining_amount, 0, ',', '.') }}
                </p>
            </div>

            <div class="bg-rose-50 dark:bg-rose-900/20 rounded-lg p-4">
                <p class="text-sm text-slate-500 dark:text-slate-400">Deductions</p>
                <p class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-1">
                    Rp {{ number_format($advance->total_deductions, 0, ',', '.') }}
                </p>
                @if($advance->settlement_date)
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $advance->settlement_date->format('d M Y') }}</p>
                @endif
            </div>
        </div>

        @if($advance->status === 'settled')
            <div class="mt-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-950/30 dark:to-emerald-950/30 rounded-lg border border-green-200 dark:border-green-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-700 dark:text-green-300">Final Settlement</p>
                        <p class="text-xs text-green-600 dark:text-green-400 mt-1">Remaining - (Savings + Guarantee)</p>
                    </div>
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400">
                        Rp {{ number_format($advance->final_settlement, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        @endif
    </x-card>

    {{-- Breakdown Costs --}}
    <x-card title="Cost Details" subtitle="Cost breakdown from shipment leg">
        @php
            $mainCost = $advance->shipmentLeg->mainCost;
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg">
                <p class="text-sm text-slate-500 dark:text-slate-400">Trip Money</p>
                <p class="text-xl font-semibold text-slate-900 dark:text-slate-100 mt-1">
                    Rp {{ number_format($mainCost->uang_jalan ?? 0, 0, ',', '.') }}
                </p>
            </div>
            <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg">
                <p class="text-sm text-slate-500 dark:text-slate-400">BBM</p>
                <p class="text-xl font-semibold text-slate-900 dark:text-slate-100 mt-1">
                    Rp {{ number_format($mainCost->bbm ?? 0, 0, ',', '.') }}
                </p>
            </div>
            <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg">
                <p class="text-sm text-slate-500 dark:text-slate-400">Tol</p>
                <p class="text-xl font-semibold text-slate-900 dark:text-slate-100 mt-1">
                    Rp {{ number_format($mainCost->toll ?? 0, 0, ',', '.') }}
                </p>
            </div>
            <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg">
                <p class="text-sm text-slate-500 dark:text-slate-400">Other Costs</p>
                <p class="text-xl font-semibold text-slate-900 dark:text-slate-100 mt-1">
                    Rp {{ number_format($mainCost->other_costs ?? 0, 0, ',', '.') }}
                </p>
            </div>
        </div>
    </x-card>

    {{-- Ajukan Pembayaran DP --}}
    @if($advance->status === 'pending')
        <x-card title="💰 Request Down Payment" subtitle="Create trip money payment request via Cash & Bank">
            <div class="space-y-4">
                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-blue-700 dark:text-blue-300">Payment Information</p>
                            <p class="text-sm text-blue-600 dark:text-blue-400 mt-1">
                                Total trip money: <span class="font-bold">Rp {{ number_format($advance->amount, 0, ',', '.') }}</span><br>
                                Recommended DP (70%): <span class="font-bold">Rp {{ number_format($advance->amount * 0.7, 0, ',', '.') }}</span>
                            </p>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mt-2">
                                Payment will be processed through Cash & Bank system for better financial control.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex md:justify-end">
                    <a href="{{ route('payment-requests.create', ['driver_advance_id' => $advance->id]) }}"
                       class="w-full md:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Request DP Payment
                    </a>
                </div>
            </div>
        </x-card>
    @endif

    {{-- Form Settlement --}}
    @if($advance->status === 'dp_paid')
        @php
            $legMainCost = $advance->shipmentLeg->mainCost;
            $fixedSavings = $legMainCost ? (float) $legMainCost->driver_savings_deduction : 0;
            $fixedGuarantee = $legMainCost ? (float) $legMainCost->driver_guarantee_deduction : 0;
        @endphp
        <x-card title="🧾 Driver Settlement" subtitle="Process settlement after driver delivered with deductions">
            <form method="POST" action="{{ route('driver-advances.settlement', $advance)}}" id="settlementForm">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                        <p class="text-sm font-medium text-blue-700 dark:text-blue-300">💡 Info</p>
                        <p class="text-sm text-blue-600 dark:text-blue-400 mt-1">
                            Remaining to be paid: <span class="font-bold">Rp {{ number_format($advance->remaining_amount, 0, ',', '.') }}</span>
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Savings Deduction (IDR)</label>
                        <input
                            type="text"
                            id="deduction_savings_display"
                            value="{{ number_format($fixedSavings, 0, ',', '.') }}"
                            class="w-full rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-500 dark:text-slate-400 cursor-not-allowed focus:outline-none"
                            readonly
                        >
                        <input type="hidden" name="deduction_savings" id="deduction_savings_input" value="{{ $fixedSavings }}">
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            Fixed from Leg Main Cost
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Guarantee Deduction (IDR)</label>
                        <input
                            type="text"
                            id="deduction_guarantee_display"
                            value="{{ number_format($fixedGuarantee, 0, ',', '.') }}"
                            class="w-full rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-500 dark:text-slate-400 cursor-not-allowed focus:outline-none"
                            readonly
                        >
                        <input type="hidden" name="deduction_guarantee" id="deduction_guarantee_input" value="{{ $fixedGuarantee }}">
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            Fixed from Leg Main Cost
                        </p>
                    </div>

                    <x-input
                        name="settlement_date"
                        type="date"
                        label="Settlement Date"
                        :value="old('settlement_date', date('Y-m-d'))"
                        :error="$errors->first('settlement_date')"
                        :required="true"
                    />

                    <div class="md:col-span-2">
                        <x-textarea
                            name="settlement_notes"
                            label="Settlement Notes"
                            :error="$errors->first('settlement_notes')"
                            placeholder="Settlement notes..."
                            :rows="2"
                        >{{ old('settlement_notes') }}</x-textarea>
                    </div>

                    {{-- Preview Final Payment --}}
                    <div class="md:col-span-2 p-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-950/30 dark:to-emerald-950/30 rounded-lg border border-green-200 dark:border-green-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-green-700 dark:text-green-300">Final Payment to Driver</p>
                                <p class="text-xs text-green-600 dark:text-green-400 mt-1">Remaining - Total Deductions</p>
                            </div>
                            <p class="text-3xl font-bold text-green-600 dark:text-green-400" id="final_payment_preview">
                                @php
                                    $finalPayment = $advance->remaining_amount - $fixedSavings - $fixedGuarantee;
                                @endphp
                                Rp {{ number_format($finalPayment, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <x-button type="submit" variant="success">
                        ✅ Process Settlement
                    </x-button>
                </div>
            </form>
        </x-card>
    @endif

    {{-- Settlement Details (if settled) --}}
    @if($advance->status === 'settled' && ($advance->deduction_savings > 0 || $advance->deduction_guarantee > 0))
        <x-card title="Deduction Details">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($advance->deduction_savings > 0)
                    <div class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-lg">
                        <p class="text-sm text-slate-500 dark:text-slate-400">Savings Deduction</p>
                        <p class="text-xl font-semibold text-amber-600 dark:text-amber-400 mt-1">
                            Rp {{ number_format($advance->deduction_savings, 0, ',', '.') }}
                        </p>
                    </div>
                @endif

                @if($advance->deduction_guarantee > 0)
                    <div class="p-4 bg-rose-50 dark:bg-rose-900/20 rounded-lg">
                        <p class="text-sm text-slate-500 dark:text-slate-400">Guarantee Deduction</p>
                        <p class="text-xl font-semibold text-rose-600 dark:text-rose-400 mt-1">
                            Rp {{ number_format($advance->deduction_guarantee, 0, ',', '.') }}
                        </p>
                    </div>
                @endif
            </div>

            {{-- Catatan pelunasan dipindah ke kartu catatan gabungan di bawah untuk menghindari duplikasi --}}
        </x-card>
    @endif

    {{-- Catatan Gabungan --}}
    @if($advance->notes || $advance->settlement_notes)
        <x-card title="Notes">
            @if($advance->notes || !$advance->notes)
                <div class="mb-4">
                    <p class="text-sm text-slate-500 dark:text-slate-400">Notes</p>
                    <p class="text-slate-700 dark:text-slate-300 mt-1">{{ $advance->notes ?: $advance->auto_description }}</p>
                </div>
            @endif
            @if($advance->settlement_notes)
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Settlement Notes</p>
                    <p class="text-slate-700 dark:text-slate-300 mt-1">{{ $advance->settlement_notes }}</p>
                </div>
            @endif
        </x-card>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Format number helper
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function parseNumber(str) {
        return parseFloat(str.replace(/\./g, '')) || 0;
    }

    // Setup DP Amount
    const dpDisplay = document.getElementById('dp_amount_display');
    const dpInput = document.getElementById('dp_amount_input');

    if (dpDisplay && dpInput) {
        dpDisplay.addEventListener('input', function() {
            let value = this.value.replace(/\./g, '');
            value = value.replace(/[^\d]/g, '');

            if (value) {
                this.value = formatNumber(value);
                dpInput.value = value;
            } else {
                this.value = '';
                dpInput.value = '0';
            }
        });

        // Initialize
        if (dpInput.value && parseFloat(dpInput.value) > 0) {
            dpDisplay.value = formatNumber(dpInput.value);
        }
    }

    // Setup Deductions with live calculation
    // Note: Deductions are now READONLY and fixed from Leg Main Cost.
    // No need for live calculation or input formatting listeners for deductions.

    const finalPaymentPreview = document.getElementById('final_payment_preview');

    // Optional: If we ever need to update final payment dynamically based on other inputs (currently none)
    function updateFinalPayment() {
        const remaining = {{ $advance->remaining_amount }};
        const savings = parseFloat(document.getElementById('deduction_savings_input')?.value || 0);
        const guarantee = parseFloat(document.getElementById('deduction_guarantee_input')?.value || 0);
        const final = remaining - savings - guarantee;

        if (finalPaymentPreview) {
            finalPaymentPreview.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(final);

            // Change color if negative
            if (final < 0) {
                finalPaymentPreview.classList.add('text-red-600', 'dark:text-red-400');
                finalPaymentPreview.classList.remove('text-green-600', 'dark:text-green-400');
            } else {
                finalPaymentPreview.classList.remove('text-red-600', 'dark:text-red-400');
                finalPaymentPreview.classList.add('text-green-600', 'dark:text-green-400');
            }
        }
    }

    // Initial check (optional, as blade already renders it correctly)
    updateFinalPayment();
});
</script>
@endsection

