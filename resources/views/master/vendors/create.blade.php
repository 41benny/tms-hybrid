@extends('layouts.app', ['title' => isset($vendor) ? 'Edit Vendor' : 'Tambah Vendor'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Header Card --}}
    <x-card>
        <x-slot:header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <x-button :href="route('vendors.index')" variant="ghost" size="sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali
                    </x-button>
                    <div>
                        <div class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ isset($vendor) ? 'Edit Vendor' : 'Tambah Vendor' }}</div>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ isset($vendor) ? 'Update informasi vendor' : 'Lengkapi informasi vendor baru' }}</p>
                    </div>
                </div>
            </div>
        </x-slot:header>
    </x-card>

    <form method="post" action="{{ isset($vendor) ? route('vendors.update', $vendor) : route('vendors.store') }}" class="space-y-6">
        @csrf
        @if(isset($vendor))
            @method('PUT')
        @endif

        {{-- Form Card --}}
        <x-card title="Data Vendor" subtitle="Lengkapi informasi vendor baru">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-input
                    name="name"
                    label="Nama Vendor"
                    :value="old('name', $vendor->name ?? '')"
                    :error="$errors->first('name')"
                    :required="true"
                    placeholder="Masukkan nama vendor"
                />

                <x-input
                    name="phone"
                    label="Nomor Telepon"
                    type="tel"
                    :value="old('phone', $vendor->phone ?? '')"
                    :error="$errors->first('phone')"
                    :required="true"
                    placeholder="Contoh: 0812-3456-7890"
                />

                <x-input
                    name="email"
                    label="Email"
                    type="email"
                    :value="old('email', $vendor->email ?? '')"
                    :error="$errors->first('email')"
                    placeholder="email@example.com"
                />

                <x-input
                    name="npwp"
                    label="NPWP"
                    :value="old('npwp', $vendor->npwp ?? '')"
                    :error="$errors->first('npwp')"
                    placeholder="00.000.000.0-000.000"
                    helper="Nomor Pokok Wajib Pajak (untuk laporan PPh 23)"
                />

                <x-select
                    name="vendor_type"
                    label="Tipe Vendor"
                    :error="$errors->first('vendor_type')"
                    :required="true"
                >
                    <option value="">Pilih tipe vendor</option>
                    <option value="trucking" @selected(old('vendor_type', $vendor->vendor_type ?? '')=='trucking')>Trucking</option>
                    <option value="freight_forwarder" @selected(old('vendor_type', $vendor->vendor_type ?? '')=='freight_forwarder')>Freight Forwarder</option>
                    <option value="supplier" @selected(old('vendor_type', $vendor->vendor_type ?? '')=='supplier')>Supplier</option>
                    <option value="pelayaran" @selected(old('vendor_type', $vendor->vendor_type ?? '')=='pelayaran')>Pelayaran</option>
                    <option value="asuransi" @selected(old('vendor_type', $vendor->vendor_type ?? '')=='asuransi')>Asuransi</option>
                    <option value="other" @selected(old('vendor_type', $vendor->vendor_type ?? '')=='other')>Lainnya</option>
                </x-select>

                <x-input
                    name="pic_name"
                    label="Nama PIC"
                    :value="old('pic_name', $vendor->pic_name ?? '')"
                    :error="$errors->first('pic_name')"
                    placeholder="Nama person in charge"
                />

                <x-input
                    name="pic_phone"
                    label="No. Telp PIC"
                    type="tel"
                    :value="old('pic_phone', $vendor->pic_phone ?? '')"
                    :error="$errors->first('pic_phone')"
                    placeholder="Nomor telepon PIC"
                />

                <x-input
                    name="pic_email"
                    label="Email PIC"
                    type="email"
                    :value="old('pic_email', $vendor->pic_email ?? '')"
                    :error="$errors->first('pic_email')"
                    placeholder="Email PIC"
                />

                <div class="md:col-span-2">
                    <label class="flex items-center gap-3 p-4 rounded-lg border-2 border-slate-200 dark:border-slate-700 hover:border-indigo-500 dark:hover:border-indigo-500 cursor-pointer transition-colors">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            @checked(old('is_active', $vendor->is_active ?? true))
                            class="w-5 h-5 rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-2 focus:ring-indigo-500"
                        >
                        <div>
                            <div class="font-medium text-slate-900 dark:text-slate-100">Status Aktif</div>
                            <div class="text-sm text-slate-500 dark:text-slate-400">Vendor ini dapat digunakan untuk transaksi</div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="mt-6">
                <x-textarea
                    name="address"
                    label="Alamat Lengkap"
                    :error="$errors->first('address')"
                    :rows="4"
                    placeholder="Masukkan alamat lengkap vendor"
                >{{ old('address', $vendor->address ?? '') }}</x-textarea>
            </div>
        </x-card>

        {{-- Bank Accounts Card --}}
        <x-card>
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Rekening Bank</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Tambahkan satu atau lebih rekening bank untuk vendor ini</p>
                </div>
                <x-button type="button" variant="outline" size="sm" onclick="addBankAccount()">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Tambah Rekening
                </x-button>
            </div>

            <div id="bank-accounts-container" class="space-y-4">
                @if(isset($vendor) && $vendor->bankAccounts->count() > 0)
                    @foreach($vendor->bankAccounts as $index => $account)
                    <div class="bank-account-item bg-slate-50 dark:bg-slate-800/50 rounded-lg p-4 border border-slate-200 dark:border-slate-700">
                        <input type="hidden" name="bank_accounts[{{ $index }}][id]" value="{{ $account->id }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-input
                                name="bank_accounts[{{ $index }}][bank_name]"
                                label="Nama Bank"
                                :value="$account->bank_name"
                                :required="true"
                                placeholder="Contoh: BCA, Mandiri, BNI"
                            />
                            <x-input
                                name="bank_accounts[{{ $index }}][account_number]"
                                label="Nomor Rekening"
                                :value="$account->account_number"
                                :required="true"
                                placeholder="Masukkan nomor rekening"
                            />
                            <x-input
                                name="bank_accounts[{{ $index }}][account_holder_name]"
                                label="Nama Pemilik Rekening"
                                :value="$account->account_holder_name"
                                :required="true"
                                placeholder="Nama sesuai rekening"
                            />
                            <x-input
                                name="bank_accounts[{{ $index }}][branch]"
                                label="Cabang (Opsional)"
                                :value="$account->branch ?? ''"
                                placeholder="Nama cabang bank"
                            />
                            <div class="md:col-span-2 flex items-center gap-4">
                                <label class="flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        name="bank_accounts[{{ $index }}][is_primary]"
                                        value="1"
                                        @checked($account->is_primary)
                                        class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-2 focus:ring-indigo-500"
                                    >
                                    <span class="text-sm text-slate-700 dark:text-slate-300">Rekening Utama</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        name="bank_accounts[{{ $index }}][is_active]"
                                        value="1"
                                        @checked($account->is_active)
                                        class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 text-green-600 focus:ring-2 focus:ring-green-500"
                                    >
                                    <span class="text-sm text-slate-700 dark:text-slate-300">Aktif</span>
                                </label>
                                <button type="button" onclick="removeBankAccount(this)" class="ml-auto text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 text-sm font-medium">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>

            @if(!isset($vendor) || $vendor->bankAccounts->count() == 0)
            <div id="no-accounts-message" class="text-center py-8 text-slate-500 dark:text-slate-400">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <p>Belum ada rekening bank. Klik "Tambah Rekening" untuk menambahkan.</p>
            </div>
            @endif
        </x-card>

        {{-- Action Buttons --}}
        <x-card>
            <div class="flex justify-end gap-3">
                <x-button :href="route('vendors.index')" variant="outline">
                    Batal
                </x-button>
                <x-button type="submit" variant="primary">
                    💾 {{ isset($vendor) ? 'Update Vendor' : 'Simpan Vendor' }}
                </x-button>
            </div>
        </x-card>
    </form>
</div>

<script>
let bankAccountIndex = {{ isset($vendor) ? $vendor->bankAccounts->count() : 0 }};

function addBankAccount() {
    const container = document.getElementById('bank-accounts-container');
    const noAccountsMsg = document.getElementById('no-accounts-message');

    if (noAccountsMsg) {
        noAccountsMsg.remove();
    }

    const template = `
        <div class="bank-account-item bg-slate-50 dark:bg-slate-800/50 rounded-lg p-4 border border-slate-200 dark:border-slate-700">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nama Bank <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        name="bank_accounts[${bankAccountIndex}][bank_name]"
                        required
                        placeholder="Contoh: BCA, Mandiri, BNI"
                        class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nomor Rekening <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        name="bank_accounts[${bankAccountIndex}][account_number]"
                        required
                        placeholder="Masukkan nomor rekening"
                        class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nama Pemilik Rekening <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        name="bank_accounts[${bankAccountIndex}][account_holder_name]"
                        required
                        placeholder="Nama sesuai rekening"
                        class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Cabang (Opsional)</label>
                    <input
                        type="text"
                        name="bank_accounts[${bankAccountIndex}][branch]"
                        placeholder="Nama cabang bank"
                        class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>
                <div class="md:col-span-2 flex items-center gap-4">
                    <label class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            name="bank_accounts[${bankAccountIndex}][is_primary]"
                            value="1"
                            class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-2 focus:ring-indigo-500"
                        >
                        <span class="text-sm text-slate-700 dark:text-slate-300">Rekening Utama</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            name="bank_accounts[${bankAccountIndex}][is_active]"
                            value="1"
                            checked
                            class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 text-green-600 focus:ring-2 focus:ring-green-500"
                        >
                        <span class="text-sm text-slate-700 dark:text-slate-300">Aktif</span>
                    </label>
                    <button type="button" onclick="removeBankAccount(this)" class="ml-auto text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 text-sm font-medium">
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', template);
    bankAccountIndex++;
}

function removeBankAccount(button) {
    const item = button.closest('.bank-account-item');
    const accountId = item.querySelector('input[name*="[id]"]');

    if (accountId && accountId.value) {
        // Mark for deletion instead of removing from DOM
        const destroyInput = document.createElement('input');
        destroyInput.type = 'hidden';
        destroyInput.name = accountId.name.replace('[id]', '[_destroy]');
        destroyInput.value = '1';
        item.appendChild(destroyInput);
        item.style.display = 'none';
    } else {
        // New item, just remove from DOM
        item.remove();
    }

    // Check if container is empty
    const container = document.getElementById('bank-accounts-container');
    const visibleItems = container.querySelectorAll('.bank-account-item:not([style*="display: none"])');

    if (visibleItems.length === 0) {
        const noAccountsMsg = `
            <div id="no-accounts-message" class="text-center py-8 text-slate-500 dark:text-slate-400">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <p>Belum ada rekening bank. Klik "Tambah Rekening" untuk menambahkan.</p>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', noAccountsMsg);
    }
}
</script>
@endsection
