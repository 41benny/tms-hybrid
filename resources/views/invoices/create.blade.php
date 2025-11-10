@extends('layouts.app', ['title' => 'Buat Invoice'])

@section('content')
<form method="post" action="{{ route('invoices.store') }}" class="space-y-4">
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

    <div class="flex justify-end gap-2">
        <a href="{{ route('invoices.index') }}" class="px-3 py-2 rounded border">Batal</a>
        <button class="px-4 py-2 rounded bg-indigo-600 text-white">Simpan</button>
    </div>
</form>

<template id="itemRow">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <input type="text" name="items[IDX][description]" placeholder="Deskripsi" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
        <input type="number" step="0.01" min="0" name="items[IDX][qty]" placeholder="Qty" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
        <input type="number" step="0.01" min="0" name="items[IDX][unit_price]" placeholder="Harga" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
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
    function addRow() { wrap.insertAdjacentHTML('beforeend', tpl.replaceAll('IDX', idx++)); }
    add.addEventListener('click', addRow);
    addRow();
});
</script>
@endsection
