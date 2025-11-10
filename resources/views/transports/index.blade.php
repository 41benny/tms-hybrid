@extends('layouts.app', ['title' => 'Transports'])

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold">Transports</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Daftar pengiriman dari Job Order</p>
        </div>
        <a href="{{ route('transports.create') }}" class="px-3 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-500">+ Transport</a>
    </div>

    <x-card>
        <form method="get" class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <select name="status" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
                <option value="">Status</option>
                @foreach(['planned','on_route','delivered','closed','cancelled'] as $st)
                    <option value="{{ $st }}" @selected(request('status')===$st)>{{ ucfirst(str_replace('_',' ', $st)) }}</option>
                @endforeach
            </select>
            <select name="executor_type" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
                <option value="">Eksekutor</option>
                @foreach(['internal'=>'Internal','vendor'=>'Vendor'] as $k=>$v)
                    <option value="{{ $k }}" @selected(request('executor_type')===$k)>{{ $v }}</option>
                @endforeach
            </select>
            <select name="job_order_id" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
                <option value="">Job Order</option>
                @foreach($jobs as $j)
                    <option value="{{ $j->id }}" @selected(request('job_order_id')==$j->id)>{{ $j->job_number }}</option>
                @endforeach
            </select>
            <div></div>
            <button class="px-3 py-2 rounded bg-slate-200 dark:bg-slate-800">Filter</button>
        </form>
    </x-card>

    <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/80">
        <table class="min-w-full text-sm">
            <thead class="text-left border-b border-slate-200 dark:border-slate-800">
                <tr class="text-slate-500">
                    <th class="px-4 py-2">Job</th>
                    <th class="px-4 py-2">Eksekutor</th>
                    <th class="px-4 py-2">Armada/Vendor</th>
                    <th class="px-4 py-2">Jadwal</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @foreach($transports as $t)
                <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                    <td class="px-4 py-2 font-medium">{{ $t->jobOrder->job_number }}</td>
                    <td class="px-4 py-2">{{ ucfirst($t->executor_type) }}</td>
                    <td class="px-4 py-2">
                        @if($t->executor_type==='internal')
                            {{ $t->truck->plate_number ?? '-' }} / {{ $t->driver->name ?? '-' }}
                        @else
                            {{ $t->vendor->name ?? '-' }}
                        @endif
                    </td>
                    <td class="px-4 py-2">{{ optional($t->departure_date)->format('d M Y') }} → {{ optional($t->arrival_date)->format('d M Y') }}</td>
                    <td class="px-4 py-2"><x-badge>{{ ucfirst(str_replace('_',' ', $t->status)) }}</x-badge></td>
                    <td class="px-4 py-2 flex gap-2">
                        <a class="underline" href="{{ route('transports.show',$t) }}">Lihat</a>
                        <a class="underline" href="{{ route('transports.edit',$t) }}">Edit</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $transports->links() }}</div>
@endsection
