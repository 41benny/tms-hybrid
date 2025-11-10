@extends('layouts.app', ['title' => 'Detail Transport'])

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold">Transport #{{ $transport->id }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Job: {{ $transport->jobOrder->job_number }}</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="post" action="{{ route('transports.update-status', $transport) }}" class="flex items-center gap-2">
                @csrf
                <select name="status" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
                    @foreach(['planned','on_route','delivered','closed','cancelled'] as $st)
                        <option value="{{ $st }}" @selected($transport->status==$st)>{{ ucfirst(str_replace('_',' ', $st)) }}</option>
                    @endforeach
                </select>
                <button class="px-3 py-2 rounded bg-slate-200 dark:bg-slate-800">Update Status</button>
            </form>
            <a href="{{ route('transports.edit', $transport) }}" class="px-3 py-2 rounded bg-slate-200 dark:bg-slate-800">Edit</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-card title="Info">
            <div class="space-y-1 text-sm">
                <div>Eksekutor: <b>{{ ucfirst($transport->executor_type) }}</b></div>
                <div>Truck: {{ $transport->truck->plate_number ?? '-' }}</div>
                <div>Driver: {{ $transport->driver->name ?? '-' }}</div>
                <div>Vendor: {{ $transport->vendor->name ?? '-' }}</div>
                <div>Jadwal: {{ optional($transport->departure_date)->format('d M Y') }} → {{ optional($transport->arrival_date)->format('d M Y') }}</div>
                <div>SPJ: {{ $transport->spj_number ?: '-' }}</div>
                <div>Status: <x-badge>{{ ucfirst(str_replace('_',' ', $transport->status)) }}</x-badge></div>
            </div>
        </x-card>
        <x-card title="Catatan">
            <div class="text-sm">{{ $transport->notes ?: '-' }}</div>
        </x-card>
        <x-card title="Biaya">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-slate-500">
                            <th class="px-2 py-1">Kategori</th>
                            <th class="px-2 py-1">Deskripsi</th>
                            <th class="px-2 py-1">Jumlah</th>
                            <th class="px-2 py-1">Vendor?</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transport->costs as $c)
                            <tr class="border-t border-slate-200 dark:border-slate-800">
                                <td class="px-2 py-1">{{ $c->cost_category }}</td>
                                <td class="px-2 py-1">{{ $c->description }}</td>
                                <td class="px-2 py-1">{{ number_format($c->amount, 2, ',', '.') }}</td>
                                <td class="px-2 py-1">{{ $c->is_vendor_cost ? 'Ya' : 'Tidak' }}</td>
                            </tr>
                        @empty
                            <tr><td class="px-2 py-2" colspan="4">Belum ada biaya.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
@endsection

