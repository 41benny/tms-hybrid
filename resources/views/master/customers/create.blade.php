@extends('layouts.app', ['title' => isset($customer) ? 'Edit Customer' : 'Tambah Customer'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Header Card --}}
    <x-card>
        <x-slot:header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <x-button :href="route('customers.index')" variant="ghost" size="sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali
                    </x-button>
                    <div>
                        <div class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                            {{ isset($customer) ? 'Edit Customer' : 'Tambah Customer' }}
                        </h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                            {{ isset($customer) ? 'Perbarui informasi customer' : 'Lengkapi informasi customer baru' }}
                        </p>
                    </div>
                </div>
            </div>
        </x-slot:header>
    </x-card>

    <form method="post" action="{{ isset($customer) ? route('customers.update', $customer) : route('customers.store') }}" class="space-y-6">
        @csrf
        @if(isset($customer))
            @method('PUT')
        @endif

        {{-- Form Card --}}
        <x-card title="Data Customer" subtitle="{{ isset($customer) ? 'Perbarui informasi customer' : 'Lengkapi informasi customer baru' }}">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-input
                    name="name"
                    label="Nama Customer"
                    :value="old('name', $customer->name ?? '')"
                    :error="$errors->first('name')"
                    :required="true"
                    placeholder="Masukkan nama customer"
                />

                <x-input
                    name="contact_person"
                    label="PIC / Contact Person"
                    :value="old('contact_person', $customer->contact_person ?? '')"
                    :error="$errors->first('contact_person')"
                    placeholder="Nama PIC / Contact Person"
                />

                <x-input
                    name="phone"
                    label="Nomor Telepon"
                    type="tel"
                    :value="old('phone', $customer->phone ?? '')"
                    :error="$errors->first('phone')"
                    :required="true"
                    placeholder="Contoh: 0812-3456-7890"
                />

                <x-input
                    name="email"
                    label="Email"
                    type="email"
                    :value="old('email', $customer->email ?? '')"
                    :error="$errors->first('email')"
                    placeholder="email@example.com"
                />

                <x-input
                    name="npwp"
                    label="NPWP"
                    :value="old('npwp', $customer->npwp ?? '')"
                    :error="$errors->first('npwp')"
                    placeholder="Nomor NPWP (opsional)"
                />

                <x-select
                    name="payment_term"
                    label="Termin Pembayaran"
                    :error="$errors->first('payment_term')"
                >
                    @php
                        $termValue = old('payment_term', $customer->payment_term ?? '');
                    @endphp
                    <option value="">Pilih termin pembayaran</option>
                    <option value="cod" @selected($termValue === 'cod')>COD (Cash On Delivery)</option>
                    <option value="net_7" @selected($termValue === 'net_7')>Net 7 Hari</option>
                    <option value="net_14" @selected($termValue === 'net_14')>Net 14 Hari</option>
                    <option value="net_30" @selected($termValue === 'net_30')>Net 30 Hari</option>
                    <option value="net_45" @selected($termValue === 'net_45')>Net 45 Hari</option>
                    <option value="net_60" @selected($termValue === 'net_60')>Net 60 Hari</option>
                </x-select>
            </div>

            <div class="mt-6">
                <x-textarea
                    name="address"
                    label="Alamat Lengkap"
                    :error="$errors->first('address')"
                    :rows="4"
                    placeholder="Masukkan alamat lengkap customer"
                >{{ old('address', $customer->address ?? '') }}</x-textarea>
            </div>
        </x-card>

        {{-- Action Buttons --}}
        <x-card>
            <div class="flex justify-end gap-3">
                <x-button :href="route('customers.index')" variant="outline">
                    Batal
                </x-button>
                <x-button type="submit" variant="primary">
                    {{ isset($customer) ? 'Update Customer' : 'Simpan Customer' }}
                </x-button>
            </div>
        </x-card>
    </form>
</div>
@endsection
