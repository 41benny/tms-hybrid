@extends('layouts.app', ['title' => 'Edit Transport'])

@section('content')
<form method="post" action="{{ route('transports.update', $transport) }}" class="space-y-4">
    @csrf
    @method('PUT')
    <x-card title="Data Transport">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm mb-1">Job Order</label>
                <select name="job_order_id" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" required>
                    @foreach($jobs as $j)
                        <option value="{{ $j->id }}" @selected($transport->job_order_id==$j->id)>{{ $j->job_number }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm mb-1">Eksekutor</label>
                <select name="executor_type" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" required>
                    <option value="internal" @selected($transport->executor_type==='internal')>Internal</option>
                    <option value="vendor" @selected($transport->executor_type==='vendor')>Vendor</option>
                </select>
            </div>
            <div>
                <label class="block text-sm mb-1">Tanggal Berangkat</label>
                <input type="date" name="departure_date" value="{{ optional($transport->departure_date)->format('Y-m-d') }}" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
            </div>
            <div>
                <label class="block text-sm mb-1">Tanggal Tiba</label>
                <input type="date" name="arrival_date" value="{{ optional($transport->arrival_date)->format('Y-m-d') }}" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
            </div>
            <div>
                <label class="block text-sm mb-1">Truck (Internal)</label>
                <select name="truck_id" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
                    <option value="">-</option>
                    @foreach($trucks as $t)
                        <option value="{{ $t->id }}" @selected($transport->truck_id==$t->id)>{{ $t->plate_number }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm mb-1">Driver (Internal)</label>
                <select name="driver_id" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
                    <option value="">-</option>
                    @foreach($drivers as $d)
                        <option value="{{ $d->id }}" @selected($transport->driver_id==$d->id)>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm mb-1">Vendor</label>
                <select name="vendor_id" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
                    <option value="">-</option>
                    @foreach($vendors as $v)
                        <option value="{{ $v->id }}" @selected($transport->vendor_id==$v->id)>{{ $v->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm mb-1">SPJ/Surat Jalan</label>
                <input type="text" name="spj_number" value="{{ $transport->spj_number }}" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
            </div>
            <div>
                <label class="block text-sm mb-1">Status</label>
                <select name="status" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" required>
                    @foreach(['planned','on_route','delivered','closed','cancelled'] as $st)
                        <option value="{{ $st }}" @selected($transport->status==$st)>{{ ucfirst(str_replace('_',' ', $st)) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-4">
            <label class="block text-sm mb-1">Catatan</label>
            <textarea name="notes" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" rows="3">{{ $transport->notes }}</textarea>
        </div>
    </x-card>

    <x-card title="Biaya (Transport Costs)">
        <div id="costs" class="space-y-3">
            @foreach($transport->costs as $i => $c)
                <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                    <input type="text" name="costs[{{ $i }}][cost_category]" value="{{ $c->cost_category }}" placeholder="Kategori (uang_jalan/solar/tol/makan_supir/lainnya/vendor_charge)" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
                    <input type="text" name="costs[{{ $i }}][description]" value="{{ $c->description }}" placeholder="Deskripsi" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
                    <input type="number" step="0.01" min="0" name="costs[{{ $i }}][amount]" value="{{ $c->amount }}" placeholder="Jumlah" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="costs[{{ $i }}][is_vendor_cost]" value="1" @checked($c->is_vendor_cost)>
                        <span class="text-sm">Biaya Vendor?</span>
                    </label>
                </div>
            @endforeach
        </div>
        <button type="button" id="addCost" class="mt-2 px-3 py-2 rounded bg-slate-200 dark:bg-slate-800">+ Tambah Biaya</button>
    </x-card>

    <div class="flex justify-end gap-2">
        <a href="{{ route('transports.show', $transport) }}" class="px-3 py-2 rounded border">Batal</a>
        <button class="px-4 py-2 rounded bg-indigo-600 text-white">Simpan</button>
    </div>
</form>

<template id="costRow">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <input type="text" name="costs[IDX][cost_category]" placeholder="Kategori (uang_jalan/solar/tol/makan_supir/lainnya/vendor_charge)" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
        <input type="text" name="costs[IDX][description]" placeholder="Deskripsi" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
        <input type="number" step="0.01" min="0" name="costs[IDX][amount]" placeholder="Jumlah" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
        <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="costs[IDX][is_vendor_cost]" value="1">
            <span class="text-sm">Biaya Vendor?</span>
        </label>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const wrap = document.getElementById('costs');
    const tpl = document.getElementById('costRow').innerHTML;
    const add = document.getElementById('addCost');
    let idx = {{ count($transport->costs) }};
    add.addEventListener('click', () => {
        wrap.insertAdjacentHTML('beforeend', tpl.replaceAll('IDX', idx++));
    });
});
</script>
@endsection

