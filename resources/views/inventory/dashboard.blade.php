@extends('layouts.app', ['title' => 'Dashboard Inventory'])

@section('content')
<div class="space-y-4 md:space-y-6">
    <x-card>
        <x-slot:header>
            <div class="text-xl md:text-2xl font-bold text-slate-900 dark:text-slate-100">Dashboard Inventory</div>
        </x-slot:header>
    </x-card>

    {{-- Low Stock Alert --}}
    @if($lowStockParts->count() > 0)
        <x-card>
            <div class="flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Peringatan Stok Rendah</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                @foreach($lowStockParts as $part)
                    <div class="p-3 border border-red-200 dark:border-red-800 rounded-lg bg-red-50 dark:bg-red-900/20">
                        <div class="font-semibold text-slate-900 dark:text-slate-100">{{ $part->code }}</div>
                        <div class="text-sm text-slate-600 dark:text-slate-400">{{ $part->name }}</div>
                        <div class="text-xs mt-1">
                            <span class="text-red-600 font-semibold">Stok: {{ number_format($part->total_stock, 2) }} {{ $part->unit }}</span>
                            <span class="text-slate-500"> | Min: {{ number_format($part->min_stock, 2) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>
    @endif

    {{-- Usage by Truck --}}
    <x-card>
        <x-slot:header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Pemakaian per Truck</h2>
                <form method="get" class="flex gap-2">
                    <select name="truck_id" class="rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm">
                        <option value="">Semua Truck</option>
                        @foreach($trucks as $truck)
                            <option value="{{ $truck->id }}" @selected(request('truck_id') == $truck->id)>{{ $truck->plate_number }}</option>
                        @endforeach
                    </select>
                    <x-button variant="outline" type="submit" size="sm">Filter</x-button>
                </form>
            </div>
        </x-slot:header>

        @if($usageByTruck->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-[#252525]">
                        <tr>
                            <th class="px-4 py-2 text-left">Truck</th>
                            <th class="px-4 py-2 text-right">Jumlah Pemakaian</th>
                            <th class="px-4 py-2 text-right">Total Cost</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-[#2d2d2d]">
                        @foreach($usageByTruck as $usage)
                            <tr>
                                <td class="px-4 py-2">
                                    @if($usage->truck)
                                        <x-badge variant="default">{{ $usage->truck->plate_number }}</x-badge>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-right">{{ $usage->usage_count }}x</td>
                                <td class="px-4 py-2 text-right font-semibold">{{ number_format($usage->total_cost, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-center py-8 text-slate-500 dark:text-slate-400">Belum ada data pemakaian</p>
        @endif
    </x-card>

    {{-- Recent Usage --}}
    <x-card>
        <x-slot:header>
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Pemakaian Terakhir</h2>
        </x-slot:header>

        <div class="space-y-2">
            @forelse($recentUsages as $usage)
                <div class="flex items-center justify-between p-3 border border-slate-200 dark:border-[#2d2d2d] rounded-lg">
                    <div class="flex-1">
                        <div class="font-semibold text-slate-900 dark:text-slate-100">{{ $usage->part->code }} - {{ $usage->part->name }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">
                            {{ $usage->usage_date->format('d M Y') }}
                            @if($usage->truck)
                                | Truck: {{ $usage->truck->plate_number }}
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-slate-900 dark:text-slate-100">{{ number_format($usage->total_cost, 2, ',', '.') }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ number_format($usage->quantity, 2) }} {{ $usage->part->unit }}</div>
                    </div>
                </div>
            @empty
                <p class="text-center py-8 text-slate-500 dark:text-slate-400">Belum ada pemakaian</p>
            @endforelse
        </div>
    </x-card>
</div>
@endsection

