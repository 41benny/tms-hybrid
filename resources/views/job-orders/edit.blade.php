@extends('layouts.app', ['title' => 'Edit Job Order'])

@section('content')
<form method="post" action="{{ route('job-orders.update', $job) }}" class="space-y-4">
    @csrf
    @method('PUT')
    <x-card title="Header">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm mb-1">Customer</label>
                <select name="customer_id" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" required>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" @selected($job->customer_id==$c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm mb-1">Tanggal Order</label>
                <input type="date" name="order_date" value="{{ $job->order_date->format('Y-m-d') }}" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" required>
            </div>
            <div>
                <label class="block text-sm mb-1">Jenis Layanan</label>
                <select name="service_type" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" required>
                    @foreach(['jpt'=>'JPT','multi_moda'=>'Multi Moda','sewa_truk'=>'Sewa Truk'] as $k=>$v)
                        <option value="{{ $k }}" @selected($job->service_type==$k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm mb-1">Status</label>
                <select name="status" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" required>
                    @foreach(['draft','confirmed','in_progress','completed','cancelled'] as $st)
                        <option value="{{ $st }}" @selected($job->status==$st)>{{ ucfirst(str_replace('_',' ', $st)) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-4">
            <label class="block text-sm mb-1">Catatan</label>
            <textarea name="notes" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" rows="3">{{ $job->notes }}</textarea>
        </div>
    </x-card>

    <div class="flex justify-end gap-2">
        <a href="{{ route('job-orders.show', $job) }}" class="px-3 py-2 rounded border">Batal</a>
        <button class="px-4 py-2 rounded bg-indigo-600 text-white">Simpan</button>
    </div>
</form>
@endsection

