@extends('layouts.app', ['title' => 'Buat Invoice'])

@section('content')
<div class="space-y-6">
    {{-- Header Card --}}
    <x-card>
        <x-slot:header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Buat Invoice</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Buat invoice baru untuk customer</p>
                </div>
                <x-button :href="route('invoices.index')" variant="ghost" size="sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Close
                </x-button>
            </div>
        </x-slot:header>
    </x-card>

    <form method="post" action="{{ route('invoices.store') }}" class="space-y-6">
        @csrf
        <x-card title="Header">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm mb-1">Customer</label>
                <select name="customer_id" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" required>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" @selected(request('customer_id')==$c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm mb-1">Tanggal Invoice</label>
                <input type="date" name="invoice_date" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" required>
            </div>
            <div>
                <label class="block text-sm mb-1">Jatuh Tempo</label>
                <input type="date" name="due_date" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
            </div>
        </div>
        <div class="mt-4">
            <label class="block text-sm mb-1">Catatan</label>
            <textarea name="notes" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" rows="3"></textarea>
        </div>
    </x-card>

    <x-card title="Item">
        <div id="items" class="space-y-3"></div>
        <button type="button" id="addItem" class="mt-2 px-3 py-2 rounded bg-slate-200 dark:bg-slate-800">+ Tambah Item</button>
    </x-card>

        <x-card>
            <div class="flex justify-end gap-3">
                <x-button :href="route('invoices.index')" variant="outline">Batal</x-button>
                <x-button type="submit" variant="primary">Simpan</x-button>
            </div>
        </x-card>
    </form>
</div>

<template id="itemRow">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-3 item-row">
        <input type="text" name="items[IDX][description]" placeholder="Deskripsi" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
        <input type="number" step="0.01" min="0" name="items[IDX][qty]" placeholder="Qty" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
        <div>
            <input type="text" data-formatted-input="unit_price" placeholder="1.000.000" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2 w-full">
            <input type="hidden" name="items[IDX][unit_price]" class="unit-price-hidden">
        </div>
        <input type="text" name="items[IDX][job_order_id]" placeholder="Job Order ID (opsional)" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
        <input type="text" name="items[IDX][transport_id]" placeholder="Transport ID (opsional)" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const wrap = document.getElementById('items');
    const tpl = document.getElementById('itemRow').innerHTML;
    const add = document.getElementById('addItem');
    let idx = 0;
    
    // Format number with thousand separator
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
    
    function setupFormattedInput(displayInput, hiddenInput) {
        displayInput.addEventListener('input', function() {
            let value = this.value.replace(/\./g, ''); // Remove dots
            value = value.replace(/[^\d]/g, ''); // Only digits
            
            if (value) {
                this.value = formatNumber(value);
                hiddenInput.value = value;
            } else {
                this.value = '';
                hiddenInput.value = '0';
            }
        });
    }
    
    function addRow() {
        wrap.insertAdjacentHTML('beforeend', tpl.replaceAll('IDX', idx++));
        
        // Setup formatted input for the newly added row
        const lastRow = wrap.lastElementChild;
        const displayInput = lastRow.querySelector('[data-formatted-input="unit_price"]');
        const hiddenInput = lastRow.querySelector('.unit-price-hidden');
        if (displayInput && hiddenInput) {
            setupFormattedInput(displayInput, hiddenInput);
        }
    }
    
    add.addEventListener('click', addRow);
    addRow(); // Add first row
});
</script>
@endsection
