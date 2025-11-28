@extends('layouts.app', ['title' => 'Create New Order'])

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    {{-- Header Card --}}
    <x-card>
        <x-slot:header>
            <div class="flex items-center justify-end">
                <x-button :href="route('job-orders.index')" variant="outline" size="sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </x-button>
            </div>
        </x-slot:header>
    </x-card>

    @php
        $selectedCustomer = old('customer_id') ? $customers->firstWhere('id', (int) old('customer_id')) : null;
        $customerOptions = $customers->map(fn($c) => [
            'id' => $c->id,
            'name' => $c->name,
        ])->values();

        // Simple list for equipment typeahead (id + name + category)
        $equipmentOptions = $equipments->map(fn($e) => [
            'id' => $e->id,
            'name' => $e->name,
            'category' => $e->category,
        ])->values();
    @endphp

    <form method="post" action="{{ route('job-orders.store') }}" class="space-y-6">
        @csrf

        {{-- Order Details --}}
        <x-card>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Customer</label>
                    <div class="relative">
                        <input type="hidden" name="customer_id" id="job_customer_id" value="{{ old('customer_id') }}">
                        <input type="text"
                               id="job_customer_search"
                               autocomplete="off"
                               placeholder="Ketik nama customer..."
                               value="{{ $selectedCustomer?->name }}"
                               class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-2 py-1.5 text-xs focus:ring-2 focus:ring-indigo-500">
                        <div id="job_customer_suggestions"
                             class="absolute z-20 mt-1 w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                            {{-- suggestions injected via JS --}}
                        </div>
                    </div>
                    @error('customer_id')
                        <p class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Sales Agent</label>
                    <select name="sales_id" class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-2 py-1.5 text-xs text-slate-900 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
                        <option value="">-- Select Sales --</option>
                        @foreach($salesList as $s)
                            <option value="{{ $s->id }}" @selected(old('sales_id')==$s->id)>{{ $s->name }}</option>
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
                        value="{{ old('origin') }}"
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
                        value="{{ old('destination') }}"
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
                    <input type="hidden" name="invoice_amount" id="invoice_amount" value="{{ old('invoice_amount', 0) }}">
                    @error('invoice_amount')
                        <p class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Tanggal Order</label>
                    <input
                        type="date"
                        name="order_date"
                        value="{{ old('order_date', date('Y-m-d')) }}"
                        class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-2 py-1.5 text-xs text-slate-900 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
                    >
                    @error('order_date')
                        <p class="mt-1 text-[10px] text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <input type="hidden" name="service_type" value="multimoda">

                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Additional Notes</label>
                    <textarea
                        name="notes"
                        rows="3"
                        placeholder="Additional notes or special instructions..."
                        class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-2 py-1.5 text-xs text-slate-900 dark:text-slate-300 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
                    >{{ old('notes') }}</textarea>
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
                <!-- Items will be added here dynamically -->
            </div>

            <div class="mt-4 text-xs text-slate-500 dark:text-slate-400">
                Cargo type tidak ada?
                <button type="button" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 font-medium" onclick="openAddCargoTypeModal()">
                    + Tambah Cargo Type Baru
                </button>
            </div>
        </x-card>

        {{-- Actions --}}
        <x-card>
            <div class="flex justify-end gap-3">
                <x-button :href="route('job-orders.index')" variant="outline" size="sm">
                    Cancel
                </x-button>
                <x-button type="submit" variant="primary" size="sm">
                    💾 Save Order
                </x-button>
            </div>
        </x-card>
    </form>
</div>

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

{{-- Modal Add Cargo Type --}}
<x-modal id="addCargoTypeModal" title="Define Cargo Type">
    <form id="addCargoTypeForm" class="space-y-4">
        @csrf
        <x-input
            name="cargo_category"
            label="Jenis Muatan (Type)"
            placeholder="e.g., Excavator, Forklift"
            id="cargo_category"
            :required="true"
            class="text-xs py-1.5"
            label-class="text-xs mb-1"
        />

        <x-input
            name="cargo_name"
            label="Model Muatan (Model)"
            placeholder="e.g., CAT 320, Zoomlion ZE215"
            id="cargo_name"
            :required="true"
            class="text-xs py-1.5"
            label-class="text-xs mb-1"
        />

        <x-input
            name="cargo_description"
            label="Description (Optional)"
            placeholder="Additional info..."
            id="cargo_description"
            class="text-xs py-1.5"
            label-class="text-xs mb-1"
        />

        <div id="modalMessage" class="hidden p-3 rounded-lg text-xs"></div>

        <div class="flex justify-end gap-3 pt-4">
            <x-button type="button" variant="outline" size="sm" onclick="closeAddCargoTypeModal()">
                Cancel
            </x-button>
            <x-button type="submit" variant="primary" size="sm">
                💾 Save Cargo Type
            </x-button>
        </div>
    </form>
</x-modal>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemIndex = 0;
    const cargoItemsContainer = document.getElementById('cargoItems');
    const addButton = document.getElementById('addCargoItem');
    const template = document.getElementById('cargoItemTemplate');
    const invoiceInput = document.getElementById('invoice_amount');

    // Typeahead sources
    const customerList = @json($customerOptions);
    const equipmentList = @json($equipmentOptions);
    const customerSearchInput = document.getElementById('job_customer_search');
    const customerHiddenInput = document.getElementById('job_customer_id');
    const customerSuggestions = document.getElementById('job_customer_suggestions');

    function clearCustomerSuggestions() {
        if (!customerSuggestions) return;
        customerSuggestions.innerHTML = '';
        customerSuggestions.classList.add('hidden');
    }

    function renderCustomerSuggestions(items) {
        if (!customerSuggestions) return;
        if (!items.length) {
            clearCustomerSuggestions();
            return;
        }
        customerSuggestions.innerHTML = items.map(function (c) {
            return '<button type="button" data-id=\"' + c.id + '\" class=\"w-full text-left px-3 py-2 text-sm hover:bg-slate-100 dark:hover:bg-slate-800\">' +
                   c.name.replace(/</g, '&lt;') +
                   '</button>';
        }).join('');
        customerSuggestions.classList.remove('hidden');

        Array.prototype.forEach.call(customerSuggestions.querySelectorAll('button[data-id]'), function (btn) {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const found = customerList.find(function (c) { return String(c.id) === String(id); });
                if (!found) {
                    if (customerHiddenInput) customerHiddenInput.value = '';
                    return;
                }

                if (customerHiddenInput) customerHiddenInput.value = found.id;
                if (customerSearchInput) customerSearchInput.value = found.name;

                clearCustomerSuggestions();
            });
        });
    }

    if (customerSearchInput && customerSuggestions) {
        customerSearchInput.addEventListener('input', function () {
            const q = (this.value || '').trim().toLowerCase();
            if (q.length < 2) {
                clearCustomerSuggestions();
                if (customerHiddenInput) customerHiddenInput.value = '';
                return;
            }
            const results = customerList.filter(function (c) {
                return (c.name || '').toLowerCase().includes(q);
            }).slice(0, 10);
            renderCustomerSuggestions(results);
        });

        document.addEventListener('click', function (e) {
            if (!customerSuggestions.contains(e.target) && e.target !== customerSearchInput) {
                clearCustomerSuggestions();
            }
        });
    }

    // Equipment typeahead (Cargo Type) - similar pola dengan customer
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
    function addCargoItem() {
        const clone = template.content.cloneNode(true);
        const html = clone.querySelector('.cargo-item').outerHTML;
        const replaced = html.replace(/INDEX/g, itemIndex);
        cargoItemsContainer.insertAdjacentHTML('beforeend', replaced);

        // Update item numbers
        updateItemNumbers();

        // Attach remove listener
        const items = cargoItemsContainer.querySelectorAll('.cargo-item');
        const lastItem = items[items.length - 1];
        lastItem.querySelector('.remove-item').addEventListener('click', function() {
            lastItem.remove();
            updateItemNumbers();
        });

        // Equipment typeahead for this item
        const cargoInput = lastItem.querySelector('.equipment-input');
        const hiddenIdInput = lastItem.querySelector('.equipment-id-hidden');
        const suggestionBox = lastItem.querySelector('.equipment-suggestions');
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

    // Format number with thousand separator
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function parseNumber(str) {
        return parseFloat(str.replace(/\./g, '')) || 0;
    }

    // Format rupiah preview
    function formatRupiah(amount) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
    }

    // Setup formatted input for invoice amount
    const invoiceDisplayInput = document.getElementById('invoice_amount_display');
    if (invoiceDisplayInput && invoiceInput) {
        // Initialize with old value if exists
        if (invoiceInput.value && parseFloat(invoiceInput.value) > 0) {
            invoiceDisplayInput.value = formatNumber(invoiceInput.value);
            
            // Update preview if it exists (for edit page)
            const invoicePreview = document.getElementById('invoice_preview');
            if (invoicePreview) {
                invoicePreview.textContent = formatRupiah(invoiceInput.value);
            }
        }
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
        const invoiceDisplay = document.getElementById('invoice_amount_display');
        const invoiceHidden = document.getElementById('invoice_amount');

        if (invoiceDisplay && invoiceHidden) {
            invoiceDisplay.value = formatNumber(total);
            invoiceHidden.value = total;
        }

        // Update preview if it exists (for edit page)
        const invoicePreview = document.getElementById('invoice_preview');
        if (invoicePreview) {
            invoicePreview.textContent = formatRupiah(total);
        }
    };

    // Add button listener
    addButton.addEventListener('click', addCargoItem);

    // Add first item by default
    addCargoItem();
});

// Modal functions
function openAddCargoTypeModal() {
    document.getElementById('addCargoTypeModal').classList.remove('hidden');
}

function closeAddCargoTypeModal() {
    document.getElementById('addCargoTypeModal').classList.add('hidden');
    document.getElementById('addCargoTypeForm').reset();
    document.getElementById('modalMessage').classList.add('hidden');
}

// Handle modal form submission
document.getElementById('addCargoTypeForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData();
    formData.append('category', document.getElementById('cargo_category').value);
    formData.append('name', document.getElementById('cargo_name').value);
    formData.append('description', document.getElementById('cargo_description').value);

    const messageDiv = document.getElementById('modalMessage');

    fetch('{{ route('equipment.store') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Add to all equipment datalists
            const datalists = document.querySelectorAll('datalist[id^="equipment_list_"]');
            datalists.forEach(datalist => {
                const option = document.createElement('option');
                option.value = data.equipment.name;
                option.setAttribute('data-id', data.equipment.id);
                option.setAttribute('data-type', data.equipment.category);
                option.textContent = data.equipment.category + ' - ' + data.equipment.name;
                datalist.appendChild(option);
            });

            // Show success message
            messageDiv.className = 'p-3 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 text-emerald-800 dark:text-emerald-200';
            messageDiv.textContent = '✅ ' + data.message;
            messageDiv.classList.remove('hidden');

            // Close modal after 1.5 seconds
            setTimeout(() => {
                closeAddCargoTypeModal();
            }, 1500);
        }
    })
    .catch(error => {
        messageDiv.className = 'p-3 rounded-lg bg-rose-50 dark:bg-rose-950/30 text-rose-800 dark:text-rose-200';
        messageDiv.textContent = '❌ Gagal menambahkan cargo type';
        messageDiv.classList.remove('hidden');
    });
});
</script>
@endsection
