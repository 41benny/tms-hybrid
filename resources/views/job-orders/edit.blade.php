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

    // Untuk typeahead cargo type (equipment)
    $equipmentOptions = $equipments->map(function ($e) {
        return [
            'id' => $e->id,
            'name' => $e->name,
            'category' => $e->category,
        ];
    })->values();
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
            <x-card>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Customer</label>
                        <select name="customer_id" class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-2 py-1.5 text-xs text-slate-900 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                            <option value="">-- Select Customer --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" @selected(old('customer_id', $job->customer_id) == $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                        @error('customer_id')
                            <p class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Sales Agent</label>
                        <select name="sales_id" class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-2 py-1.5 text-xs text-slate-900 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                            <option value="">-- Select Sales --</option>
                            @foreach($salesList as $s)
                                <option value="{{ $s->id }}" @selected(old('sales_id', $job->sales_id) == $s->id)>{{ $s->name }}</option>
                            @endforeach
                        </select>
                        @error('sales_id')
                            <p class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Origin</label>
                        <input
                            type="text"
                            name="origin"
                            value="{{ old('origin', $job->origin) }}"
                            placeholder="e.g., Jakarta, Indonesia"
                            class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-2 py-1.5 text-xs text-slate-900 dark:text-slate-300 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
                        >
                        @error('origin')
                            <p class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Destination</label>
                        <input
                            type="text"
                            name="destination"
                            value="{{ old('destination', $job->destination) }}"
                            placeholder="e.g., Surabaya, Indonesia"
                            class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-2 py-1.5 text-xs text-slate-900 dark:text-slate-300 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
                        >
                        @error('destination')
                            <p class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Nilai Tagihan (IDR)</label>
                        <input
                            type="text"
                            id="invoice_amount_display"
                            placeholder="0"
                            readonly
                            class="w-full rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 px-2 py-1.5 text-xs text-slate-500 dark:text-slate-400 focus:outline-none cursor-not-allowed"
                        >
                        <input type="hidden" name="invoice_amount" id="invoice_amount" value="{{ old('invoice_amount', $job->invoice_amount ?? 0) }}">
                        @error('invoice_amount')
                            <p class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Tanggal Order</label>
                        <input
                            type="date"
                            name="order_date"
                            value="{{ old('order_date', $job->order_date->format('Y-m-d')) }}"
                            class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-2 py-1.5 text-xs text-slate-900 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
                        >
                        @error('order_date')
                            <p class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <input type="hidden" name="service_type" value="multimoda">

                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
                        <select name="status" class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-2 py-1.5 text-xs text-slate-900 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                            <option value="draft" @selected(old('status', $job->status) == 'draft')>Draft</option>
                            <option value="confirmed" @selected(old('status', $job->status) == 'confirmed')>Confirmed</option>
                            <option value="in_progress" @selected(old('status', $job->status) == 'in_progress')>In Progress</option>
                            <option value="completed" @selected(old('status', $job->status) == 'completed')>Completed</option>
                            <option value="cancelled" @selected(old('status', $job->status) == 'cancelled')>Cancelled</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Additional Notes</label>
                        <textarea
                            name="notes"
                            rows="3"
                            placeholder="Additional notes or special instructions..."
                            class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-2 py-1.5 text-xs text-slate-900 dark:text-slate-300 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
                        >{{ old('notes', $job->notes) }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </x-card>

            {{-- Cargo Items --}}
            <x-card>
                <x-slot:header>
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-300">Cargo Items</h3>
                        <x-button type="button" variant="outline" size="sm" id="addCargoItem">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Item
                        </x-button>
                    </div>
                </x-slot:header>

                <div id="cargoItems" class="space-y-4">
                    <!-- Items will be loaded via JS -->
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
        <div class="cargo-item border border-slate-200 dark:border-slate-800 rounded-lg p-3 bg-slate-50 dark:bg-slate-800/30">
            <div class="flex items-start justify-between mb-2">
                <h4 class="font-semibold text-sm text-slate-900 dark:text-slate-300">Item #<span class="item-number">1</span></h4>
                <button type="button" class="remove-item flex items-center gap-1 text-rose-600 hover:text-rose-700 dark:text-rose-400 text-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Remove
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div class="md:col-span-1">
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Cargo Type (Model)</label>
                    <div class="relative">
                        <input
                            type="text"
                            name="items[INDEX][cargo_type]"
                            placeholder="Ketik untuk cari... (e.g., Excavator)"
                            autocomplete="off"
                            class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-2 py-1.5 text-xs text-slate-900 dark:text-slate-300 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm equipment-input"
                            data-index="INDEX"
                        >
                        <div
                            class="equipment-suggestions absolute z-20 mt-1 w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden"
                            data-index="INDEX"
                        ></div>
                    </div>
                    <input type="hidden" name="items[INDEX][equipment_id]" class="equipment-id-hidden">
                    <input type="hidden" name="items[INDEX][equipment_name]" class="equipment-name-hidden">
                    <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $equipments->count() }} model tersedia</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Quantity</label>
                    <input type="number" step="0.01" name="items[INDEX][quantity]" value="1" class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-2 py-1.5 text-xs text-slate-900 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm quantity-input" oninput="calculateTotal()">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Price per Unit</label>
                    <input type="text" class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-2 py-1.5 text-xs text-slate-900 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm price-display-input" placeholder="0" oninput="formatPriceInput(this); calculateTotal()">
                    <input type="hidden" name="items[INDEX][price]" class="price-input" value="0">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Serial Numbers</label>
                    <input type="text" name="items[INDEX][serial_numbers]" placeholder="SN-001, SN-002" class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-2 py-1.5 text-xs text-slate-900 dark:text-slate-300 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
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

    const equipmentList = @json($equipmentOptions);

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
        if (invoiceDisplayInput && invoiceInput) {
            invoiceDisplayInput.value = formatNumber(total);
            invoiceInput.value = total;
        }
    };

    if (invoiceDisplayInput && invoiceInput) {
        // Initialize from existing value
        const initial = parseFloat(invoiceInput.value) || 0;
        if (initial > 0) {
            invoiceDisplayInput.value = formatNumber(initial);
        }
    }

    function attachEquipmentTypeahead(inputEl, hiddenIdEl, suggestionsEl) {
        if (!inputEl || !suggestionsEl) return;

        function clearEquipment() {
            suggestionsEl.innerHTML = '';
            suggestionsEl.classList.add('hidden');
        }

        function renderEquipment(items) {
            if (!items.length) {
                clearEquipment();
                return;
            }

            suggestionsEl.innerHTML = items.map(function (e) {
                const label = (e.category ? e.category + ' - ' : '') + (e.name || '');
                return '<button type="button" data-id=\"' + e.id + '\" data-name=\"' + (e.name || '').replace(/"/g, '&quot;') + '\" class=\"w-full text-left px-3 py-2 text-xs hover:bg-slate-100 dark:hover:bg-slate-800\">' +
                       label.replace(/</g, '&lt;') +
                       '</button>';
            }).join('');

            suggestionsEl.classList.remove('hidden');

            Array.prototype.forEach.call(suggestionsEl.querySelectorAll('button[data-id]'), function (btn) {
                btn.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name') || '';
                    if (hiddenIdEl) hiddenIdEl.value = id;
                    inputEl.value = name;
                    clearEquipment();
                });
            });
        }

        inputEl.addEventListener('input', function () {
            const q = (this.value || '').trim().toLowerCase();
            if (hiddenIdEl) hiddenIdEl.value = '';

            if (q.length < 2) {
                clearEquipment();
                return;
            }

            const results = (equipmentList || []).filter(function (e) {
                const name = (e.name || '').toLowerCase();
                const cat = (e.category || '').toLowerCase();
                return name.includes(q) || cat.includes(q);
            }).slice(0, 20);

            renderEquipment(results);
        });

        document.addEventListener('click', function (e) {
            if (!suggestionsEl.contains(e.target) && e.target !== inputEl) {
                clearEquipment();
            }
        });
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

        // Equipment typeahead for this item
        const cargoInput = newItem.querySelector('.equipment-input');
        const hiddenIdInput = newItem.querySelector('.equipment-id-hidden');
        const suggestionBox = newItem.querySelector('.equipment-suggestions');
        attachEquipmentTypeahead(cargoInput, hiddenIdInput, suggestionBox);

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
        calculateTotal();
    } else {
        if (cargoItemsContainer.querySelectorAll('.cargo-item').length === 0) {
            addCargoItem();
        }
    }
});
</script>
@endpush
