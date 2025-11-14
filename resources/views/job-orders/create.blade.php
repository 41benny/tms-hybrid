@extends('layouts.app', ['title' => 'Create New Order'])

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    {{-- Header Card --}}
    <x-card>
        <x-slot:header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Create New Order</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Buat job order baru</p>
                </div>
                <x-button :href="route('job-orders.index')" variant="ghost" size="sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Close
                </x-button>
            </div>
        </x-slot:header>
    </x-card>

    <form method="post" action="{{ route('job-orders.store') }}" class="space-y-6">
        @csrf
        
        {{-- Order Details --}}
        <x-card>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-select 
                    name="customer_id" 
                    label="Customer"
                    :error="$errors->first('customer_id')"
                    :required="true"
                >
                    <option value="">-- Select a Customer --</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" @selected(old('customer_id')==$c->id)>{{ $c->name }}</option>
                    @endforeach
                </x-select>

                <x-select 
                    name="sales_id" 
                    label="Sales Agent"
                    :error="$errors->first('sales_id')"
                >
                    <option value="">-- Select Sales --</option>
                    @foreach($salesList as $s)
                        <option value="{{ $s->id }}" @selected(old('sales_id')==$s->id)>{{ $s->name }}</option>
                    @endforeach
                </x-select>

                <x-input 
                    name="origin" 
                    label="Origin" 
                    :value="old('origin')"
                    :error="$errors->first('origin')"
                    placeholder="e.g., Jakarta, Indonesia"
                />

                <x-input 
                    name="destination" 
                    label="Destination" 
                    :value="old('destination')"
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
                    <input type="hidden" name="invoice_amount" id="invoice_amount" value="{{ old('invoice_amount', 0) }}">
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

                <input type="hidden" name="order_date" value="{{ date('Y-m-d') }}">
                <input type="hidden" name="service_type" value="multimoda">

                <div class="md:col-span-2">
                    <x-textarea 
                        name="notes" 
                        label="Additional Notes"
                        :error="$errors->first('notes')"
                        :rows="3"
                        placeholder="Additional notes or special instructions..."
                    >{{ old('notes') }}</x-textarea>
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
                <!-- Items will be added here dynamically -->
            </div>
            
            <div class="mt-4 text-sm text-slate-500 dark:text-slate-400">
                Cargo type tidak ada? 
                <button type="button" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 font-medium" onclick="openAddCargoTypeModal()">
                    + Tambah Cargo Type Baru
                </button>
            </div>
        </x-card>

        {{-- Actions --}}
        <x-card>
            <div class="flex justify-end gap-3">
                <x-button :href="route('job-orders.index')" variant="outline">
                    Cancel
                </x-button>
                <x-button type="submit" variant="primary">
                    💾 Save Order
                </x-button>
            </div>
        </x-card>
    </form>
</div>

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
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
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
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $equipments->count() }} model tersedia</div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Quantity</label>
                <input type="number" step="0.01" name="items[INDEX][quantity]" value="1" class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Serial Numbers</label>
                <input type="text" name="items[INDEX][serial_numbers]" placeholder="SN-001, SN-002" class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-4 py-2.5 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm">
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
        />
        
        <x-input 
            name="cargo_name" 
            label="Model Muatan (Model)" 
            placeholder="e.g., CAT 320, Zoomlion ZE215"
            id="cargo_name"
            :required="true"
        />
        
        <x-input 
            name="cargo_description" 
            label="Description (Optional)" 
            placeholder="Additional info..."
            id="cargo_description"
        />
        
        <div id="modalMessage" class="hidden p-3 rounded-lg"></div>
        
        <div class="flex justify-end gap-3 pt-4">
            <x-button type="button" variant="outline" onclick="closeAddCargoTypeModal()">
                Cancel
            </x-button>
            <x-button type="submit" variant="primary">
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
    const invoicePreview = document.getElementById('invoice_preview');

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
        
        // Auto-fill equipment_id when user types
        const cargoInput = lastItem.querySelector('.equipment-input');
        const hiddenIdInput = lastItem.querySelector('.equipment-id-hidden');
        
        cargoInput.addEventListener('input', function() {
            const datalistId = this.getAttribute('list');
            const datalist = document.getElementById(datalistId);
            const options = datalist.querySelectorAll('option');
            
            // Find matching option
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
    if (invoiceDisplayInput && invoiceInput && invoicePreview) {
        invoiceDisplayInput.addEventListener('input', function() {
            let value = this.value.replace(/\./g, ''); // Remove dots
            value = value.replace(/[^\d]/g, ''); // Only digits
            
            if (value) {
                this.value = formatNumber(value);
                invoiceInput.value = value;
            } else {
                this.value = '';
                invoiceInput.value = '0';
            }
            
            // Update preview
            const amount = parseFloat(invoiceInput.value) || 0;
            invoicePreview.textContent = formatRupiah(amount);
        });
        
        // Initialize with old value if exists
        if (invoiceInput.value && parseFloat(invoiceInput.value) > 0) {
            invoiceDisplayInput.value = formatNumber(invoiceInput.value);
            invoicePreview.textContent = formatRupiah(invoiceInput.value);
        }
    }

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
