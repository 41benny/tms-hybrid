@extends('layouts.app', ['title' => 'Detail Driver Advance'])

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-3">
        <x-button :href="route('driver-advances.index')" variant="ghost" size="sm">
            ← Kembali
        </x-button>
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Driver Advance #{{ $advance->advance_number }}</h1>
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
                        <x-badge variant="warning" class="text-lg px-4 py-2">⏳ Pending - Belum Bayar DP</x-badge>
                    @elseif($advance->status === 'dp_paid')
                        <x-badge variant="info" class="text-lg px-4 py-2">🚚 DP Paid - Driver Sudah Jalan</x-badge>
                    @else
                        <x-badge variant="success" class="text-lg px-4 py-2">✅ Settled - Lunas</x-badge>
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
    <x-card title="Ringkasan Pembayaran">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-4">
                <p class="text-sm text-slate-500 dark:text-slate-400">Total Uang Jalan</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1">
                    Rp {{ number_format($advance->amount, 0, ',', '.') }}
                </p>
            </div>

            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                <p class="text-sm text-slate-500 dark:text-slate-400">DP Dibayar</p>
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">
                    Rp {{ number_format($advance->dp_amount, 0, ',', '.') }}
                </p>
                @if($advance->dp_paid_date)
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $advance->dp_paid_date->format('d M Y') }}</p>
                @endif
            </div>

            <div class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-4">
                <p class="text-sm text-slate-500 dark:text-slate-400">Sisa</p>
                <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">
                    Rp {{ number_format($advance->remaining_amount, 0, ',', '.') }}
                </p>
            </div>

            <div class="bg-rose-50 dark:bg-rose-900/20 rounded-lg p-4">
                <p class="text-sm text-slate-500 dark:text-slate-400">Potongan</p>
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
                        <p class="text-sm font-medium text-green-700 dark:text-green-300">Pelunasan Final</p>
                        <p class="text-xs text-green-600 dark:text-green-400 mt-1">Sisa - (Tabungan + Jaminan)</p>
                    </div>
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400">
                        Rp {{ number_format($advance->final_settlement, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        @endif
    </x-card>

    {{-- Breakdown Costs --}}
    <x-card title="Detail Biaya" subtitle="Breakdown biaya dari shipment leg">
        @php
            $mainCost = $advance->shipmentLeg->mainCost;
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg">
                <p class="text-sm text-slate-500 dark:text-slate-400">Uang Jalan</p>
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
        </div>
    </x-card>

    {{-- Form Bayar DP --}}
    @if($advance->status === 'pending')
        <x-card title="💰 Bayar DP ke Driver" subtitle="Kasih uang jalan sebelum driver berangkat">
            <form method="POST" action="{{ route('driver-advances.pay-dp', $advance) }}">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Jumlah DP (IDR)</label>
                        <input 
                            type="text"
                            id="dp_amount_display"
                            placeholder="7.000.000"
                            class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                        <input type="hidden" name="dp_amount" id="dp_amount_input" value="{{ old('dp_amount', $advance->amount * 0.7) }}">
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">Maksimal: Rp {{ number_format($advance->amount, 0, ',', '.') }}</p>
                        @error('dp_amount')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-input 
                        name="dp_paid_date" 
                        type="date"
                        label="Tanggal Bayar DP" 
                        :value="old('dp_paid_date', date('Y-m-d'))"
                        :error="$errors->first('dp_paid_date')"
                        :required="true"
                    />

                    <div class="md:col-span-2">
                        <x-textarea 
                            name="notes" 
                            label="Catatan"
                            :error="$errors->first('notes')"
                            placeholder="Catatan pembayaran DP..."
                            :rows="2"
                        >{{ old('notes', $advance->notes) }}</x-textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <x-button type="submit" variant="primary">
                        💵 Bayar DP Sekarang
                    </x-button>
                </div>
            </form>
        </x-card>
    @endif

    {{-- Form Settlement --}}
    @if($advance->status === 'dp_paid')
        <x-card title="🧾 Pelunasan Driver" subtitle="Proses pelunasan setelah driver delivered dengan potongan">
            <form method="POST" action="{{ route('driver-advances.settlement', $advance)}}" id="settlementForm">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                        <p class="text-sm font-medium text-blue-700 dark:text-blue-300">💡 Info</p>
                        <p class="text-sm text-blue-600 dark:text-blue-400 mt-1">
                            Sisa yang harus dibayar: <span class="font-bold">Rp {{ number_format($advance->remaining_amount, 0, ',', '.') }}</span>
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Potongan Tabungan (IDR)</label>
                        <input 
                            type="text"
                            id="deduction_savings_display"
                            placeholder="500.000"
                            class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                        <input type="hidden" name="deduction_savings" id="deduction_savings_input" value="{{ old('deduction_savings', 0) }}">
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">Potongan untuk tabungan driver</p>
                        @error('deduction_savings')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Potongan Jaminan (IDR)</label>
                        <input 
                            type="text"
                            id="deduction_guarantee_display"
                            placeholder="300.000"
                            class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                        <input type="hidden" name="deduction_guarantee" id="deduction_guarantee_input" value="{{ old('deduction_guarantee', 0) }}">
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">Potongan untuk jaminan</p>
                        @error('deduction_guarantee')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-input 
                        name="settlement_date" 
                        type="date"
                        label="Tanggal Pelunasan" 
                        :value="old('settlement_date', date('Y-m-d'))"
                        :error="$errors->first('settlement_date')"
                        :required="true"
                    />

                    <div class="md:col-span-2">
                        <x-textarea 
                            name="settlement_notes" 
                            label="Catatan Pelunasan"
                            :error="$errors->first('settlement_notes')"
                            placeholder="Catatan pelunasan..."
                            :rows="2"
                        >{{ old('settlement_notes') }}</x-textarea>
                    </div>

                    {{-- Preview Final Payment --}}
                    <div class="md:col-span-2 p-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-950/30 dark:to-emerald-950/30 rounded-lg border border-green-200 dark:border-green-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-green-700 dark:text-green-300">Pembayaran Final ke Driver</p>
                                <p class="text-xs text-green-600 dark:text-green-400 mt-1">Sisa - Total Potongan</p>
                            </div>
                            <p class="text-3xl font-bold text-green-600 dark:text-green-400" id="final_payment_preview">
                                Rp {{ number_format($advance->remaining_amount, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <x-button type="submit" variant="success">
                        ✅ Proses Pelunasan
                    </x-button>
                </div>
            </form>
        </x-card>
    @endif

    {{-- Settlement Details (if settled) --}}
    @if($advance->status === 'settled' && ($advance->deduction_savings > 0 || $advance->deduction_guarantee > 0))
        <x-card title="Detail Potongan">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($advance->deduction_savings > 0)
                    <div class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-lg">
                        <p class="text-sm text-slate-500 dark:text-slate-400">Potongan Tabungan</p>
                        <p class="text-xl font-semibold text-amber-600 dark:text-amber-400 mt-1">
                            Rp {{ number_format($advance->deduction_savings, 0, ',', '.') }}
                        </p>
                    </div>
                @endif

                @if($advance->deduction_guarantee > 0)
                    <div class="p-4 bg-rose-50 dark:bg-rose-900/20 rounded-lg">
                        <p class="text-sm text-slate-500 dark:text-slate-400">Potongan Jaminan</p>
                        <p class="text-xl font-semibold text-rose-600 dark:text-rose-400 mt-1">
                            Rp {{ number_format($advance->deduction_guarantee, 0, ',', '.') }}
                        </p>
                    </div>
                @endif
            </div>

            @if($advance->settlement_notes)
                <div class="mt-4 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg">
                    <p class="text-sm text-slate-500 dark:text-slate-400">Catatan Pelunasan</p>
                    <p class="text-slate-900 dark:text-slate-100 mt-1">{{ $advance->settlement_notes }}</p>
                </div>
            @endif
        </x-card>
    @endif

    {{-- Notes --}}
    @if($advance->notes)
        <x-card title="Catatan">
            <p class="text-slate-700 dark:text-slate-300">{{ $advance->notes }}</p>
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
    const savingsDisplay = document.getElementById('deduction_savings_display');
    const savingsInput = document.getElementById('deduction_savings_input');
    const guaranteeDisplay = document.getElementById('deduction_guarantee_display');
    const guaranteeInput = document.getElementById('deduction_guarantee_input');
    const finalPaymentPreview = document.getElementById('final_payment_preview');
    
    function updateFinalPayment() {
        const remaining = {{ $advance->remaining_amount }};
        const savings = parseFloat(savingsInput?.value || 0);
        const guarantee = parseFloat(guaranteeInput?.value || 0);
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
    
    if (savingsDisplay && savingsInput) {
        savingsDisplay.addEventListener('input', function() {
            let value = this.value.replace(/\./g, '');
            value = value.replace(/[^\d]/g, '');
            
            if (value) {
                this.value = formatNumber(value);
                savingsInput.value = value;
            } else {
                this.value = '';
                savingsInput.value = '0';
            }
            updateFinalPayment();
        });
    }
    
    if (guaranteeDisplay && guaranteeInput) {
        guaranteeDisplay.addEventListener('input', function() {
            let value = this.value.replace(/\./g, '');
            value = value.replace(/[^\d]/g, '');
            
            if (value) {
                this.value = formatNumber(value);
                guaranteeInput.value = value;
            } else {
                this.value = '';
                guaranteeInput.value = '0';
            }
            updateFinalPayment();
        });
    }
});
</script>
@endsection

