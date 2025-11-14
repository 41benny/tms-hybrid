@extends('layouts.app', ['title' => 'Ajukan Pembayaran'])

@section('content')
    <div class="mb-4">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $vendorBill ? 'Ajukan Pembayaran Vendor Bill' : 'Ajukan Pembayaran Manual' }}</h1>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">{{ $vendorBill ? 'Buat pengajuan pembayaran untuk vendor bill' : 'Buat pengajuan pembayaran manual diluar vendor bill' }}</p>
    </div>

    @if($vendorBill)
    {{-- FORM UNTUK VENDOR BILL --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Form --}}
        <div class="lg:col-span-2">
            <x-card title="Form Pengajuan">
                <form method="POST" action="{{ route('payment-requests.store') }}">
                    @csrf
                    <input type="hidden" name="payment_type" value="vendor_bill">
                    <input type="hidden" name="vendor_bill_id" value="{{ $vendorBill->id }}">

                    <div class="space-y-4">
                        {{-- Rekening Vendor --}}
                        @if($vendorBill->vendor->activeBankAccounts->count() > 0)
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Rekening Tujuan Transfer 
                                <span class="text-xs text-slate-500 dark:text-slate-400 font-normal">(Pilih rekening vendor)</span>
                            </label>
                            <select 
                                name="vendor_bank_account_id" 
                                class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            >
                                <option value="">-- Pilih Rekening --</option>
                                @foreach($vendorBill->vendor->activeBankAccounts as $account)
                                    <option value="{{ $account->id }}" @selected(old('vendor_bank_account_id') == $account->id || $account->is_primary)>
                                        {{ $account->bank_name }} - {{ $account->account_number }} ({{ $account->account_holder_name }})
                                        @if($account->is_primary) ⭐ @endif
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                ⭐ = Rekening utama vendor
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
                                        Vendor Belum Punya Rekening Bank
                                    </h4>
                                    <p class="text-xs text-yellow-700 dark:text-yellow-300">
                                        Silakan tambahkan rekening bank untuk vendor <strong>{{ $vendorBill->vendor->name }}</strong> terlebih dahulu di halaman master vendor.
                                    </p>
                                    <a href="{{ route('vendors.edit', $vendorBill->vendor) }}" target="_blank" class="text-xs text-yellow-800 dark:text-yellow-200 font-medium hover:underline inline-flex items-center gap-1 mt-2">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                        Edit Vendor & Tambah Rekening
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Jumlah Pengajuan <span class="text-red-500">*</span></label>
                            <input 
                                type="text" 
                                id="amount_display"
                                placeholder="Masukkan jumlah"
                                value="{{ old('amount') ? number_format(old('amount'), 0, ',', '.') : number_format($vendorBill->remaining, 0, ',', '.') }}"
                                class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('amount') border-red-500 @enderror"
                            >
                            <input type="hidden" name="amount" id="amount_input" value="{{ old('amount', $vendorBill->remaining) }}">
                            @error('amount')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                Maksimal: Rp {{ number_format($vendorBill->remaining, 0, ',', '.') }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Catatan</label>
                            <textarea 
                                name="notes" 
                                rows="4"
                                placeholder="Tambahkan catatan untuk pengajuan ini (opsional)"
                                class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            >{{ old('notes') }}</textarea>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center gap-3 pt-4">
                            <x-button type="submit" variant="primary" class="w-full sm:w-auto justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Ajukan Pembayaran
                            </x-button>
                            <x-button :href="route('vendor-bills.show', $vendorBill)" variant="outline" class="w-full sm:w-auto justify-center">
                                Batal
                            </x-button>
                        </div>
                    </div>
                </form>
            </x-card>
        </div>

        {{-- Vendor Bill Info --}}
        <div>
            <x-card title="Informasi Vendor Bill">
                <div class="space-y-3 text-sm">
                    <div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Nomor</div>
                        <div class="font-medium text-slate-900 dark:text-slate-100">{{ $vendorBill->vendor_bill_number }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Vendor</div>
                        <div class="font-medium text-slate-900 dark:text-slate-100">{{ $vendorBill->vendor->name }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Tanggal</div>
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
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Total Tagihan</div>
                        <div class="font-semibold text-slate-900 dark:text-slate-100">Rp {{ number_format($vendorBill->total_amount, 0, ',', '.') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-emerald-500 dark:text-emerald-400 mb-1">Total Dibayar</div>
                        <div class="font-semibold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($vendorBill->payments->sum('amount'), 0, ',', '.') }}</div>
                    </div>
                    <div class="pt-2 border-t border-slate-200 dark:border-slate-700">
                        <div class="text-xs text-rose-500 dark:text-rose-400 mb-1">Sisa Belum Dibayar</div>
                        <div class="text-xl font-bold text-rose-600 dark:text-rose-400">Rp {{ number_format($vendorBill->remaining, 0, ',', '.') }}</div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
    
    @else
    {{-- FORM MANUAL PAYMENT REQUEST --}}
    <div class="max-w-3xl mx-auto">
        <x-card title="Form Pengajuan Pembayaran Manual" subtitle="Buat pengajuan pembayaran diluar vendor bill">
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
                            <option value="">-- Pilih Vendor --</option>
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
                            Rekening Tujuan Transfer
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-normal">(Pilih rekening vendor)</span>
                        </label>
                        <select 
                            name="vendor_bank_account_id" 
                            id="bank_account_select"
                            class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                            <option value="">-- Pilih Rekening --</option>
                        </select>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            ⭐ = Rekening utama vendor
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
                                    Vendor Belum Punya Rekening Bank
                                </h4>
                                <p class="text-xs text-yellow-700 dark:text-yellow-300">
                                    Silakan tambahkan rekening bank untuk vendor ini terlebih dahulu.
                                </p>
                                <a href="#" id="edit_vendor_link" target="_blank" class="text-xs text-yellow-800 dark:text-yellow-200 font-medium hover:underline inline-flex items-center gap-1 mt-2">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                    Edit Vendor & Tambah Rekening
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Deskripsi Pembayaran <span class="text-red-500">*</span></label>
                        <input 
                            type="text" 
                            name="description"
                            value="{{ old('description') }}"
                            required
                            placeholder="Contoh: Pembayaran sewa kantor, Biaya operasional, dll"
                            class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('description') border-red-500 @enderror"
                        >
                        @error('description')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Amount --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Jumlah Pembayaran <span class="text-red-500">*</span></label>
                        <input 
                            type="text" 
                            id="amount_display_manual"
                            placeholder="Masukkan jumlah pembayaran"
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
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Catatan Tambahan</label>
                        <textarea 
                            name="notes" 
                            rows="4"
                            placeholder="Tambahkan catatan untuk pengajuan ini (opsional)"
                            class="w-full rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >{{ old('notes') }}</textarea>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex flex-col sm:flex-row items-center gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
                        <x-button type="submit" variant="primary" class="w-full sm:w-auto justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
                            Ajukan Pembayaran
                        </x-button>
                        <x-button :href="route('payment-requests.index')" variant="outline" class="w-full sm:w-auto justify-center">
                            Batal
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
            bankAccountSelect.innerHTML = '<option value="">-- Pilih Rekening --</option>';
            
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
    }
    @endif
    </script>
@endsection

