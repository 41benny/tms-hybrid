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
        <div class="max-w-3xl mx-auto">
            <x-card title="Manual Payment Request Form" subtitle="Create payment request outside vendor bill">
                <form method="POST" action="{{ route('payment-requests.store') }}">
                    @csrf
                    <input type="hidden" name="payment_type" value="manual">
                    <div class="space-y-6">
                        {{-- Penerima Transfer --}}
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Nama Penerima</label>
                                <div class="flex rounded-lg overflow-hidden border border-slate-300 dark:border-[#3d3d3d] bg-white dark:bg-[#252525]">
                                    <input
                                        type="text"
                                        name="manual_payee_name"
                                        value="{{ old('manual_payee_name') }}"
                                        placeholder="Contoh: Budi, Supplier A"
                                        class="flex-1 min-w-0 px-3 py-2 text-sm text-slate-900 dark:text-slate-100 bg-transparent focus:outline-none"
                                    >
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1 px-3 text-xs font-medium text-sky-600 dark:text-sky-400 bg-sky-50 dark:bg-sky-900/20 hover:bg-sky-100 dark:hover:bg-sky-900/40 border-l border-slate-300 dark:border-[#3d3d3d]"
                                        title="Cari penerima tersimpan (coming soon)"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 5a6 6 0 100 12 6 6 0 000-12z" />
                                        </svg>
                                        Cari
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Nomor Rekening</label>
                                    <input
                                        type="text"
                                        name="manual_bank_account"
                                        value="{{ old('manual_bank_account') }}"
                                        placeholder="Nomor rekening (opsional)"
                                        class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Bank Tujuan</label>
                                    <input
                                        type="text"
                                        name="manual_bank_name"
                                        value="{{ old('manual_bank_name') }}"
                                        placeholder="Nama bank (opsional, wajib jika isi no rek)"
                                        class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    >
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                        Wajib diisi jika nomor rekening diisi.
                                    </p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Atas Nama (opsional)</label>
                                <input
                                    type="text"
                                    name="manual_bank_holder"
                                    value="{{ old('manual_bank_holder') }}"
                                    placeholder="Nama pemilik rekening"
                                    class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                >
                            </div>

                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        onclick="const cb = document.getElementById('save_recipient_toggle'); if (!cb) return; cb.checked = !cb.checked; const knob = document.getElementById('save_recipient_knob'); if (knob) knob.style.transform = cb.checked ? 'translateX(1.25rem)' : 'translateX(0)'; this.classList.toggle('bg-indigo-500', cb.checked); this.classList.toggle('bg-slate-200', !cb.checked);"
                                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out bg-slate-200 dark:bg-slate-700"
                                    >
                                        <span id="save_recipient_knob" class="inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out translate-x-0"></span>
                                    </button>
                                    <input type="checkbox" name="save_recipient" id="save_recipient_toggle" class="hidden">
                                    <span class="text-sm text-slate-700 dark:text-slate-300">Simpan data penerima</span>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    Jika aktif, data nama &amp; rekening dapat disimpan ke master (konfigurasi backend menyusul).
                                </p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Tujuan Transfer / Deskripsi <span class="text-red-500">*</span></label>
                            <input type="text" name="description" value="{{ old('description') }}" required placeholder="Contoh: Pembayaran sewa kantor, biaya operasional, dll" class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('description') border-red-500 @enderror">
                            @error('description')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Jumlah Transfer (Rp) <span class="text-red-500">*</span></label>
                            <input type="text" id="amount_display_manual" placeholder="Masukkan jumlah transfer" value="{{ old('amount') ? number_format(old('amount'), 0, ',', '.') : '' }}" class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('amount') border-red-500 @enderror">
                            <input type="hidden" name="amount" id="amount_input_manual" value="{{ old('amount', 0) }}">
                            @error('amount')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Keterangan (opsional)</label>
                            <textarea name="notes" rows="4" placeholder="Catatan tambahan untuk tim finance (opsional)" class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                        </div>

                        {{-- Link ke Vendor (opsional) --}}
                        <div class="pt-4 border-t border-slate-200 dark:border-slate-700">
                            <details class="group">
                                <summary class="flex items-center justify-between cursor-pointer list-none">
                                    <div class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                        Link ke Vendor (opsional)
                                    </div>
                                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400 transition-transform duration-200 group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </summary>
                                <div class="mt-3">
                                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Vendor (opsional)</label>
                                    <select name="vendor_id" class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <option value="">-- Tidak pilih vendor --</option>
                                        @foreach($vendors ?? [] as $vendor)
                                            <option value="{{ $vendor->id }}" @selected(old('vendor_id') == $vendor->id)>{{ $vendor->name }} ({{ $vendor->vendor_type }})</option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                        Pilih vendor hanya jika ingin link ke master vendor. Nama penerima &amp; rekening tetap bebas diisi.
                                    </p>
                                </div>
                            </details>
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





