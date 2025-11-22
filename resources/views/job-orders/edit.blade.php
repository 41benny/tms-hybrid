@extends('layouts.app', ['title' => 'Edit Job Order'])

@section('content')
@php
    $preparedItems = collect(old('items', []))
        ->filter(function ($item) {
            return is_array($item) && (isset($item['cargo_type']) || isset($item['equipment_id']));
        })
        ->map(function ($item) {
            return [
                'cargo_type' => $item['cargo_type'] ?? '',
                'quantity' => $item['quantity'] ?? 1,
                'serial_numbers' => $item['serial_numbers'] ?? '',
                'equipment_id' => $item['equipment_id'] ?? '',
                'equipment_name' => $item['equipment_name'] ?? '',
                'price' => $item['price'] ?? 0,
            ];
        });

    if ($preparedItems->isEmpty()) {
        $preparedItems = $job->items->map(function ($item) {
            return [
                'cargo_type' => $item->cargo_type,
                'quantity' => $item->quantity,
                'serial_numbers' => $item->serial_numbers,
                'equipment_id' => $item->equipment_id,
                'equipment_name' => $item->equipment?->name,
                'price' => $item->price,
            ];
        });
    }

    $preparedItems = $preparedItems->values();
@endphp
<div class="max-w-4xl mx-auto space-y-6 w-full">
    {{-- Header Card --}}
    <x-card>
        <x-slot:header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <x-button :href="route('job-orders.show', $job)" variant="ghost" size="sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali
                    </x-button>
                    <div>
                        <div class="text-2xl font-bold text-slate-900 dark:text-slate-100">Edit Job Order</div>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $job->job_number }}</p>
                    </div>
                </div>
            </div>
        </x-slot:header>
    </x-card>

    @if($job->isLocked())
        <x-alert variant="warning" class="mb-4">
            Job Order yang sudah <strong>{{ strtoupper(str_replace('_', ' ', $job->status)) }}</strong> tidak bisa di-edit.
        </x-alert>
    @endif

    <form method="post" action="{{ route('job-orders.update', $job) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <fieldset @disabled($job->isLocked())>
            <x-card title="Job Order Information" subtitle="Update data job order">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-select
                        name="customer_id"
                        label="Customer"
                        :error="$errors->first('customer_id')"
                        :required="true"
                    >
                        <option value="">Pilih customer</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" @selected(old('customer_id', $job->customer_id) == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </x-select>

                    <x-select
                        name="sales_id"
                        label="Sales"
                        :error="$errors->first('sales_id')"
                    >
                        <option value="">Pilih sales</option>
                        @foreach($salesList as $s)
                            <option value="{{ $s->id }}" @selected(old('sales_id', $job->sales_id) == $s->id)>{{ $s->name }}</option>
                        @endforeach
                    </x-select>

                    <x-input
                        name="origin"
                        label="Origin"
                        :value="old('origin', $job->origin)"
                        :error="$errors->first('origin')"
                        placeholder="e.g., Jakarta, Indonesia"
                    />

                    <x-input
                        name="destination"
                        label="Destination"
                        :value="old('destination', $job->destination)"
                        :error="$errors->first('destination')"
                        placeholder="e.g., Surabaya, Indonesia"
                    />

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nilai Tagihan (IDR)</label>
                        <input
                            type="text"
                            id="invoice_amount_display"
                            placeholder="1.000.000"
                            class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
                        >
                        <input type="hidden" name="invoice_amount" id="invoice_amount" value="{{ old('invoice_amount', $job->invoice_amount ?? 0) }}">
                        @error('invoice_amount')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Preview</label>
                        <div class="px-4 py-2.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-100 font-medium" id="invoice_preview">
                            Rp 0
                        </div>
                    </div>

                    <x-input
                        name="order_date"
                        type="date"
                        label="Tanggal Order"
                        :value="old('order_date', $job->order_date->format('Y-m-d'))"
                        :error="$errors->first('order_date')"
                        :required="true"
                    />

                    <x-select
                        name="service_type"
                        label="Jenis Layanan"
                        :error="$errors->first('service_type')"
                        :required="true"
                    >
                        <option value="">Pilih jenis layanan</option>
                        <option value="multimoda" @selected(old('service_type', $job->service_type) == 'multimoda')>Multimoda</option>
                        <option value="inland" @selected(old('service_type', $job->service_type) == 'inland')>Inland</option>
                    </x-select>

                    <x-select
                        name="status"
                        label="Status"
                        :error="$errors->first('status')"
                        :required="true"
                    >
                        <option value="draft" @selected(old('status', $job->status) == 'draft')>Draft</option>
                        <option value="confirmed" @selected(old('status', $job->status) == 'confirmed')>Confirmed</option>
                        <option value="in_progress" @selected(old('status', $job->status) == 'in_progress')>In Progress</option>
                        <option value="completed" @selected(old('status', $job->status) == 'completed')>Completed</option>
                        <option value="cancelled" @selected(old('status', $job->status) == 'cancelled')>Cancelled</option>
                    </x-select>

                    <div class="md:col-span-2">
                        <x-textarea
                            name="notes"
                            label="Catatan"
                            :error="$errors->first('notes')"
                            :rows="3"
                            placeholder="Catatan tambahan (opsional)"
                        >{{ old('notes', $job->notes) }}</x-textarea>
                    </div>
                </div>
            </x-card>

            {{-- Cargo Items --}}
            <x-card>
                <x-slot:header>
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Cargo Items</h3>
                        <x-button type="button" variant="outline" size="sm" id="addCargoItem">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Item
                        </x-button>
                    </div>
                </x-slot:header>

                <div id="cargoItems" class="space-y-4">
                    @forelse($preparedItems as $idx => $item)
                        <div class="cargo-item border border-slate-200 dark:border-slate-800 rounded-lg p-4 bg-slate-50 dark:bg-slate-800/30">
                            <div class="flex items-start justify-between mb-3">
                                <h4 class="font-semibold text-slate-900 dark:text-slate-100">Item #<span class="item-number">{{ $loop->iteration }}</span></h4>
                                <button type="button" class="remove-item flex items-center gap-1 text-rose-600 hover:text-rose-700 dark:text-rose-400 text-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Remove
                                </button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="md:col-span-1">
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Cargo Type (Model)</label>
                                    <input
                                        type="text"
                                        list="equipment_list_{{ $idx }}"
                                        name="items[{{ $idx }}][cargo_type]"
                                        value="{{ $item['cargo_type'] ?? ($item['equipment_name'] ?? '') }}"
                                        placeholder="Ketik untuk cari... (e.g., Excavator)"
                                        class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm equipment-input"
                                        data-index="{{ $idx }}"
                                    >
                                    <datalist id="equipment_list_{{ $idx }}">
                                        @foreach($equipments as $eq)
                                            <option value="{{ $eq->name }}" data-id="{{ $eq->id }}" data-type="{{ $eq->category }}">{{ $eq->category }} - {{ $eq->name }}</option>
                                        @endforeach
                                    </datalist>
                                    <input type="hidden" name="items[{{ $idx }}][equipment_id]" class="equipment-id-hidden" value="{{ $item['equipment_id'] }}">
                                    <input type="hidden" name="items[{{ $idx }}][equipment_name]" class="equipment-name-hidden" value="{{ $item['equipment_name'] }}">
                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $equipments->count() }} model tersedia</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Quantity</label>
                                    <input type="number" step="0.01" name="items[{{ $idx }}][quantity]" value="{{ $item['quantity'] ?? 1 }}" class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm quantity-input" oninput="calculateTotal()">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Price per Unit</label>
                                    <input type="text" class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm price-display-input" placeholder="0" value="{{ number_format($item['price'] ?? 0, 0, ',', '.') }}" oninput="formatPriceInput(this); calculateTotal()">
                                    <input type="hidden" name="items[{{ $idx }}][price]" class="price-input" value="{{ $item['price'] ?? 0 }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Serial Numbers</label>
                                    <input type="text" name="items[{{ $idx }}][serial_numbers]" value="{{ $item['serial_numbers'] ?? '' }}" placeholder="SN-001, SN-002" class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                                </div>
                            </div>
                        </div>
                    @empty
                        {{-- Biarkan JS menambahkan item kosong jika tidak ada data --}}
                    @endforelse
                </div>
            </x-card>

            <x-card>
                <div class="flex justify-end gap-3">
                    <x-button :href="route('job-orders.show', $job)" variant="outline">
                        Batal
                    </x-button>
                    <x-button type="submit" variant="primary" :disabled="$job->isLocked()">
                        Update Job Order
                    </x-button>
                </div>
            </x-card>
        </fieldset>
    </form>
    {{-- Template for Cargo Item --}}
    <template id="cargoItemTemplate">
        <div class="cargo-item border border-slate-200 dark:border-slate-800 rounded-lg p-4 bg-slate-50 dark:bg-slate-800/30">
            <div class="flex items-start justify-between mb-3">
                <h4 class="font-semibold text-slate-900 dark:text-slate-100">Item #<span class="item-number">1</span></h4>
                <button type="button" class="remove-item flex items-center gap-1 text-rose-600 hover:text-rose-700 dark:text-rose-400 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Remove
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-1">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Cargo Type (Model)</label>
                    <input
                        type="text"
                        list="equipment_list_INDEX"
                        name="items[INDEX][cargo_type]"
                        placeholder="Ketik untuk cari... (e.g., Excavator)"
                        class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm equipment-input"
                        data-index="INDEX"
                    >
                    <datalist id="equipment_list_INDEX">
                        @foreach($equipments as $eq)
                            <option value="{{ $eq->name }}" data-id="{{ $eq->id }}" data-type="{{ $eq->category }}">{{ $eq->category }} - {{ $eq->name }}</option>
                        @endforeach
                    </datalist>
                    <input type="hidden" name="items[INDEX][equipment_id]" class="equipment-id-hidden">
                    <input type="hidden" name="items[INDEX][equipment_name]" class="equipment-name-hidden">
                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $equipments->count() }} model tersedia</div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Quantity</label>
                    <input type="number" step="0.01" name="items[INDEX][quantity]" value="1" class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm quantity-input" oninput="calculateTotal()">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Price per Unit</label>
                    <input type="text" class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm price-display-input" placeholder="0" oninput="formatPriceInput(this); calculateTotal()">
                    <input type="hidden" name="items[INDEX][price]" class="price-input" value="0">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Serial Numbers</label>
                    <input type="text" name="items[INDEX][serial_numbers]" placeholder="SN-001, SN-002" class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                </div>
            </div>
        </div>
    </template>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const invoiceInput = document.getElementById('invoice_amount');
    const invoiceDisplayInput = document.getElementById('invoice_amount_display');
    const invoicePreview = document.getElementById('invoice_preview');

    // Cargo Items Logic
    const cargoItemsContainer = document.getElementById('cargoItems');
    const addButton = document.getElementById('addCargoItem');
    const template = document.getElementById('cargoItemTemplate');
    let itemIndex = 0;

    // Jika elemen utama tidak ada, jangan lanjut
    if (!cargoItemsContainer || !template) {
        return;
    }

    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function formatRupiah(amount) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
    }

    // Format price input
    window.formatPriceInput = function(input) {
        let value = input.value.replace(/\./g, '');
        value = value.replace(/[^\d]/g, '');

        const hiddenInput = input.nextElementSibling;

        if (value) {
            input.value = formatNumber(value);
            hiddenInput.value = value;
        } else {
            input.value = '';
            hiddenInput.value = '0';
        }
    };

    // Calculate total from items
    window.calculateTotal = function() {
        let total = 0;
        const items = document.querySelectorAll('.cargo-item');

        items.forEach(item => {
            const qty = parseFloat(item.querySelector('.quantity-input').value) || 0;
            const price = parseFloat(item.querySelector('.price-input').value) || 0;
            total += qty * price;
        });

        // Update invoice amount display
        if (invoiceDisplayInput && invoiceInput && invoicePreview) {
            invoiceDisplayInput.value = formatNumber(total);
            invoiceInput.value = total;
            invoicePreview.textContent = formatRupiah(total);
        }
    };

    if (invoiceDisplayInput) {
        invoiceDisplayInput.addEventListener('input', function () {
            let value = this.value.replace(/\./g, '');
            value = value.replace(/[^\d]/g, '');

            if (value) {
                this.value = formatNumber(value);
                invoiceInput.value = value;
            } else {
                this.value = '';
                invoiceInput.value = '0';
            }

            const amount = parseFloat(invoiceInput.value) || 0;
            invoicePreview.textContent = formatRupiah(amount);
        });

        // Initialize from existing value
        const initial = parseFloat(invoiceInput.value) || 0;
        if (initial > 0) {
            invoiceDisplayInput.value = formatNumber(initial);
            invoicePreview.textContent = formatRupiah(initial);
        } else {
            invoicePreview.textContent = formatRupiah(0);
        }
    }

    // Function to add cargo item
    function addCargoItem(data = null) {
        const clone = template.content.cloneNode(true);
        const html = clone.querySelector('.cargo-item').outerHTML;
        const replaced = html.replace(/INDEX/g, itemIndex);
        cargoItemsContainer.insertAdjacentHTML('beforeend', replaced);

        const newItem = cargoItemsContainer.lastElementChild;

        // Pre-fill data if provided
        if (data) {
            const cargoInputEl = newItem.querySelector('[name="items[' + itemIndex + '][cargo_type]"]');
            const cargoValue = data.cargo_type || data.equipment_name || '';
            if (cargoInputEl) {
                cargoInputEl.value = cargoValue || '';
            }
            newItem.querySelector('[name="items[' + itemIndex + '][quantity]"]').value = data.quantity || 1;
            newItem.querySelector('[name="items[' + itemIndex + '][serial_numbers]"]').value = data.serial_numbers || '';
            newItem.querySelector('[name="items[' + itemIndex + '][equipment_id]"]').value = data.equipment_id || '';

            const price = data.price || 0;
            newItem.querySelector('.price-input').value = price;
            newItem.querySelector('.price-display-input').value = formatNumber(price);
        }

        // Update item numbers
        updateItemNumbers();

        // Attach remove listener
        newItem.querySelector('.remove-item').addEventListener('click', function() {
            newItem.remove();
            updateItemNumbers();
            calculateTotal();
        });

        // Auto-fill equipment_id when user types
        const cargoInput = newItem.querySelector('.equipment-input');
        const hiddenIdInput = newItem.querySelector('.equipment-id-hidden');

        cargoInput.addEventListener('input', function() {
            const datalistId = this.getAttribute('list');
            const datalist = document.getElementById(datalistId);
            const options = datalist.querySelectorAll('option');

            let found = false;
            options.forEach(option => {
                if (option.value === this.value) {
                    hiddenIdInput.value = option.getAttribute('data-id');
                    found = true;
                }
            });

            if (!found) {
                hiddenIdInput.value = '';
            }
        });

        itemIndex++;
    }

    // Update item numbers
    function updateItemNumbers() {
        const items = cargoItemsContainer.querySelectorAll('.cargo-item');
        items.forEach((item, index) => {
            item.querySelector('.item-number').textContent = index + 1;
        });
    }

    // Add button listener
    if (addButton) {
        addButton.addEventListener('click', function(e) {
            e.preventDefault();
            addCargoItem();
        });
    }

    // Load existing items
    const existingItems = @json($preparedItems ?? []);
    if (existingItems.length > 0 && cargoItemsContainer.querySelectorAll('.cargo-item').length === 0) {
        existingItems.forEach(item => addCargoItem(item));
    } else {
        if (cargoItemsContainer.querySelectorAll('.cargo-item').length === 0) {
            addCargoItem();
        }
    }
});
</script>
@endpush
