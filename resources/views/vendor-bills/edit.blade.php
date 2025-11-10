@extends('layouts.app', ['title' => 'Edit Vendor Bill'])

@section('content')
<form method="post" action="{{ route('vendor-bills.update', $bill) }}" class="space-y-4">
    @csrf
    @method('PUT')
    <x-card title="Header">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm mb-1">Vendor</label>
                <select name="vendor_id" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" required>
                    @foreach($vendors as $v)
                        <option value="{{ $v->id }}" @selected($bill->vendor_id==$v->id)>{{ $v->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm mb-1">Tanggal Bill</label>
                <input type="date" name="bill_date" value="{{ $bill->bill_date->format('Y-m-d') }}" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" required>
            </div>
            <div>
                <label class="block text-sm mb-1">Jatuh Tempo</label>
                <input type="date" name="due_date" value="{{ optional($bill->due_date)->format('Y-m-d') }}" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
            </div>
            <div>
                <label class="block text-sm mb-1">Status</label>
                <select name="status" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" required>
                    @foreach(['draft','received','partially_paid','paid','cancelled'] as $st)
                        <option value="{{ $st }}" @selected($bill->status==$st)>{{ ucfirst(str_replace('_',' ', $st)) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-4">
            <label class="block text-sm mb-1">Catatan</label>
            <textarea name="notes" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" rows="3">{{ $bill->notes }}</textarea>
        </div>
    </x-card>

    <x-card title="Item">
        <div id="items" class="space-y-3">
            @foreach($bill->items as $i => $it)
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <input type="text" name="items[{{ $i }}][description]" value="{{ $it->description }}" placeholder="Deskripsi" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
                    <input type="number" step="0.01" min="0" name="items[{{ $i }}][qty]" value="{{ $it->qty }}" placeholder="Qty" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
                    <input type="number" step="0.01" min="0" name="items[{{ $i }}][unit_price]" value="{{ $it->unit_price }}" placeholder="Harga" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
                    <input type="text" name="items[{{ $i }}][transport_id]" value="{{ $it->transport_id }}" placeholder="Transport ID (opsional)" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
                </div>
            @endforeach
        </div>
        <button type="button" id="addItem" class="mt-2 px-3 py-2 rounded bg-slate-200 dark:bg-slate-800">+ Tambah Item</button>
    </x-card>

    <div class="flex justify-end gap-2">
        <a href="{{ route('vendor-bills.show', $bill) }}" class="px-3 py-2 rounded border">Batal</a>
        <button class="px-4 py-2 rounded bg-indigo-600 text-white">Simpan</button>
    </div>
</form>

<template id="itemRow">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <input type="text" name="items[IDX][description]" placeholder="Deskripsi" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
        <input type="number" step="0.01" min="0" name="items[IDX][qty]" placeholder="Qty" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
        <input type="number" step="0.01" min="0" name="items[IDX][unit_price]" placeholder="Harga" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
        <input type="text" name="items[IDX][transport_id]" placeholder="Transport ID (opsional)" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const wrap = document.getElementById('items');
    const tpl = document.getElementById('itemRow').innerHTML;
    const add = document.getElementById('addItem');
    let idx = {{ count($bill->items) }};
    add.addEventListener('click', () => { wrap.insertAdjacentHTML('beforeend', tpl.replaceAll('IDX', idx++)); });
});
</script>
@endsection

