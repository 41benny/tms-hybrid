@extends('layouts.app', ['title' => 'Buat Transport'])

@section('content')
<form method="post" action="{{ route('transports.store') }}" class="space-y-4">
    @csrf
    <x-card title="Data Transport">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm mb-1">Job Order</label>
                <select name="job_order_id" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" required>
                    @foreach($jobs as $j)
                        <option value="{{ $j->id }}" @selected(request('job_order_id')==$j->id)>{{ $j->job_number }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm mb-1">Eksekutor</label>
                <select name="executor_type" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" required>
                    <option value="internal">Internal</option>
                    <option value="vendor">Vendor</option>
                </select>
            </div>
            <div>
                <label class="block text-sm mb-1">Tanggal Berangkat</label>
                <input type="date" name="departure_date" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
            </div>
            <div>
                <label class="block text-sm mb-1">Tanggal Tiba</label>
                <input type="date" name="arrival_date" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
            </div>
            <div>
                <label class="block text-sm mb-1">Truck (Internal)</label>
                <select name="truck_id" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
                    <option value="">-</option>
                    @foreach($trucks as $t)
                        <option value="{{ $t->id }}">{{ $t->plate_number }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm mb-1">Driver (Internal)</label>
                <select name="driver_id" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
                    <option value="">-</option>
                    @foreach($drivers as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm mb-1">Vendor</label>
                <select name="vendor_id" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
                    <option value="">-</option>
                    @foreach($vendors as $v)
                        <option value="{{ $v->id }}">{{ $v->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm mb-1">SPJ/Surat Jalan</label>
                <input type="text" name="spj_number" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
            </div>
        </div>
        <div class="mt-4">
            <label class="block text-sm mb-1">Catatan</label>
            <textarea name="notes" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" rows="3"></textarea>
        </div>
    </x-card>

    <div class="flex justify-end gap-2">
        <a href="{{ route('transports.index') }}" class="px-3 py-2 rounded border">Batal</a>
        <button class="px-4 py-2 rounded bg-indigo-600 text-white">Simpan</button>
    </div>
</form>
@endsection
