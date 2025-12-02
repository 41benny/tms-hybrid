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
        @include('payment-requests.partials.form-driver', ['driverAdvance' => $driverAdvance])
    @elseif($vendorBill ?? false)
        @include('payment-requests.partials.form-vendor-bill', ['vendorBill' => $vendorBill])
    @else
        @php
            $vendorOptions = ($vendors ?? collect())->map(fn($v) => [
                'id' => $v->id,
                'name' => $v->name,
                'vendor_type' => $v->vendor_type,
            ])->values();
        @endphp
        <div class="max-w-3xl mx-auto">
            <x-card title="Manual Payment Request Form" subtitle="Create payment request outside vendor bill">
                <form method="POST" action="{{ route('payment-requests.store') }}">
                    @csrf
                    <input type="hidden" name="payment_type" value="manual">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Vendor (opsional)</label>
                            <select name="vendor_id" class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Tidak pilih vendor --</option>
                                @foreach($vendors ?? [] as $vendor)
                                    <option value="{{ $vendor->id }}" @selected(old('vendor_id') == $vendor->id)>{{ $vendor->name }} ({{ $vendor->vendor_type }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Nama Penerima</label>
                                <input type="text" name="manual_payee_name" value="{{ old('manual_payee_name') }}" placeholder="Contoh: Budi, Supplier A" class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Bank</label>
                                <input type="text" name="manual_bank_name" value="{{ old('manual_bank_name') }}" placeholder="Nama bank (opsional)" class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">No Rekening</label>
                                <input type="text" name="manual_bank_account" value="{{ old('manual_bank_account') }}" placeholder="Nomor rekening (opsional)" class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Atas Nama</label>
                                <input type="text" name="manual_bank_holder" value="{{ old('manual_bank_holder') }}" placeholder="Nama pemilik rekening (opsional)" class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div class="md:col-span-2 text-xs text-slate-500 dark:text-slate-400 flex items-end">
                                <p>Nama & rekening bebas diisi, tidak wajib terhubung vendor. Pilih vendor hanya jika mau link ke master.</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Payment Description <span class="text-red-500">*</span></label>
                            <input type="text" name="description" value="{{ old('description') }}" required placeholder="Example: Office rent payment, Operational expenses, etc" class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('description') border-red-500 @enderror">
                            @error('description')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Payment Amount <span class="text-red-500">*</span></label>
                            <input type="text" id="amount_display_manual" placeholder="Enter payment amount" value="{{ old('amount') ? number_format(old('amount'), 0, ',', '.') : '' }}" class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('amount') border-red-500 @enderror">
                            <input type="hidden" name="amount" id="amount_input_manual" value="{{ old('amount', 0) }}">
                            @error('amount')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Additional Notes</label>
                            <textarea name="notes" rows="4" placeholder="Add notes for this request (optional)" class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                        </div>
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
    @if(!$vendorBill && !$driverAdvance)
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
        if (amountInputManual.value && amountInputManual.value != '0') {
            amountDisplayManual.value = formatNumber(amountInputManual.value);
        }
    }
    @endif    // Script for DRIVER ADVANCE payment request
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





