@extends('layouts.app', ['title' => 'Detail Pemakaian Part'])

@section('content')
<div class="space-y-4 md:space-y-6">
    <x-card>
        <x-slot:header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="text-xl md:text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $partUsage->usage_number }}</div>
                    <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400 mt-1">{{ $partUsage->usage_date->format('d M Y') }}</p>
                </div>
                <x-button :href="route('part-usages.index')" variant="ghost" size="sm">Kembali</x-button>
            </div>
        </x-slot:header>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
            <div>
                <label class="text-xs md:text-sm text-slate-500 dark:text-slate-400">Part</label>
                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $partUsage->part->code }} - {{ $partUsage->part->name }}</p>
            </div>
            <div>
                <label class="text-xs md:text-sm text-slate-500 dark:text-slate-400">Quantity</label>
                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ number_format($partUsage->quantity, 2) }} {{ $partUsage->part->unit }}</p>
            </div>
            <div>
                <label class="text-xs md:text-sm text-slate-500 dark:text-slate-400">Truck</label>
                <p class="font-semibold text-slate-900 dark:text-slate-100">
                    @if($partUsage->truck)
                        <x-badge variant="default">{{ $partUsage->truck->plate_number }}</x-badge>
                    @else
                        <span class="text-slate-400">-</span>
                    @endif
                </p>
            </div>
            <div>
                <label class="text-xs md:text-sm text-slate-500 dark:text-slate-400">Jenis Pemakaian</label>
                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ ucfirst($partUsage->usage_type) }}</p>
            </div>
            <div>
                <label class="text-xs md:text-sm text-slate-500 dark:text-slate-400">Harga per Unit</label>
                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ number_format($partUsage->unit_cost, 2, ',', '.') }}</p>
            </div>
            <div>
                <label class="text-xs md:text-sm text-slate-500 dark:text-slate-400">Total Cost</label>
                <p class="font-semibold text-slate-900 dark:text-slate-100 text-lg">{{ number_format($partUsage->total_cost, 2, ',', '.') }}</p>
            </div>
            @if($partUsage->description)
                <div class="md:col-span-2">
                    <label class="text-xs md:text-sm text-slate-500 dark:text-slate-400">Deskripsi</label>
                    <p class="text-slate-900 dark:text-slate-100">{{ $partUsage->description }}</p>
                </div>
            @endif
        </div>
    </x-card>
</div>
@endsection

