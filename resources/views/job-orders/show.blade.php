@extends('layouts.app', ['title' => 'Detail Job Order'])

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold">{{ $job->job_number }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $job->customer->name ?? '-' }} • {{ strtoupper($job->service_type) }}</p>
        </div>
        <a href="{{ route('job-orders.edit', $job) }}" class="px-3 py-2 rounded bg-slate-200 dark:bg-slate-800">Edit</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-card title="Ringkasan">
            <div class="space-y-1 text-sm">
                <div>Status: <x-badge>{{ ucfirst(str_replace('_',' ', $job->status)) }}</x-badge></div>
                <div>Tanggal: {{ $job->order_date->format('d M Y') }}</div>
                <div>Jenis Layanan: {{ strtoupper(str_replace('_',' ', $job->service_type)) }}</div>
                <div>Catatan: {{ $job->notes ?: '-' }}</div>
            </div>
        </x-card>
        <x-card title="Item">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left">
                        <tr class="text-slate-500">
                            <th class="px-2 py-1">Nama</th>
                            <th class="px-2 py-1">SN</th>
                            <th class="px-2 py-1">Qty</th>
                            <th class="px-2 py-1">Asal</th>
                            <th class="px-2 py-1">Tujuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($job->items as $it)
                            <tr class="border-t border-slate-200 dark:border-slate-800">
                                <td class="px-2 py-1">{{ $it->equipment_name ?? $it->equipment->name ?? '-' }}</td>
                                <td class="px-2 py-1">{{ $it->serial_number ?: '-' }}</td>
                                <td class="px-2 py-1">{{ $it->qty }}</td>
                                <td class="px-2 py-1">{{ $it->origin_text ?? $it->originRoute->origin ?? '-' }}</td>
                                <td class="px-2 py-1">{{ $it->destination_text ?? $it->destinationRoute->destination ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>
        <x-card title="Transport Terkait">
            <p class="text-sm text-slate-500">Akan ditampilkan setelah modul Transport dibuat.</p>
        </x-card>
    </div>
@endsection

