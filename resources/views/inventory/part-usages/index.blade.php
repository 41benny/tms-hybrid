@extends('layouts.app', ['title' => 'Pemakaian Part'])

@section('content')
<div class="space-y-4 md:space-y-6">
    <x-card>
        <x-slot:header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="text-xl md:text-2xl font-bold text-slate-900 dark:text-slate-100">Pemakaian Part</div>
                    <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400 mt-1">Daftar pemakaian sparepart</p>
                </div>
                <x-button :href="route('part-usages.create')" variant="primary" class="w-full sm:w-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span class="hidden sm:inline">Tambah</span>
                    <span class="sm:hidden">Tambah Pemakaian</span>
                </x-button>
            </div>
        </x-slot:header>
    </x-card>

    <x-card>
        <form method="get" class="flex flex-col sm:flex-row gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari part..." class="flex-1 rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 md:px-4 py-2 text-sm">
            <select name="truck_id" class="rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 md:px-4 py-2 text-sm">
                <option value="">Semua Truck</option>
                @foreach($trucks as $truck)
                    <option value="{{ $truck->id }}" @selected(request('truck_id') == $truck->id)>{{ $truck->plate_number }}</option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ request('from') }}" class="rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 md:px-4 py-2 text-sm">
            <input type="date" name="to" value="{{ request('to') }}" class="rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 md:px-4 py-2 text-sm">
            <x-button variant="outline" type="submit" class="w-full sm:w-auto">Cari</x-button>
        </form>
    </x-card>

    {{-- Mobile Card View --}}
    <div class="block md:hidden space-y-3">
        @forelse($usages as $usage)
            <x-card>
                <div class="space-y-2">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="font-semibold text-slate-900 dark:text-slate-100">{{ $usage->part->code }}</h3>
                            <p class="text-sm text-slate-600 dark:text-slate-400">{{ $usage->part->name }}</p>
                        </div>
                        <x-badge variant="default">{{ $usage->usage_type }}</x-badge>
                    </div>
                    <div class="text-sm space-y-1">
                        <p class="text-slate-600 dark:text-slate-400">Qty: {{ number_format($usage->quantity, 2) }} {{ $usage->part->unit }}</p>
                        @if($usage->truck)
                            <p class="text-slate-600 dark:text-slate-400">Truck: {{ $usage->truck->plate_number }}</p>
                        @endif
                        <p class="font-semibold text-slate-900 dark:text-slate-100">Total: {{ number_format($usage->total_cost, 2, ',', '.') }}</p>
                    </div>
                    <a href="{{ route('part-usages.show', $usage) }}" class="text-indigo-600 text-sm hover:underline">Lihat Detail →</a>
                </div>
            </x-card>
        @empty
            <x-card>
                <div class="text-center py-8 text-slate-500 dark:text-slate-400">
                    <span class="text-4xl">🔧</span>
                    <p class="mt-2 text-sm">Belum ada pemakaian part</p>
                </div>
            </x-card>
        @endforelse
    </div>

    {{-- Desktop Table View --}}
    <x-card :noPadding="true" class="hidden md:block">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-[#2d2d2d]">
                <thead class="bg-slate-50 dark:bg-[#252525]">
                    <tr>
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">No Pemakaian</th>
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Tanggal</th>
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Part</th>
                        <th class="px-4 md:px-6 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Qty</th>
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Truck</th>
                        <th class="px-4 md:px-6 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Total</th>
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-[#1e1e1e] divide-y divide-slate-200 dark:divide-[#2d2d2d]">
                    @forelse($usages as $usage)
                        <tr class="hover:bg-slate-50 dark:hover:bg-[#252525] transition-colors">
                            <td class="px-4 md:px-6 py-4 whitespace-nowrap font-medium text-slate-900 dark:text-slate-100">{{ $usage->usage_number }}</td>
                            <td class="px-4 md:px-6 py-4 whitespace-nowrap text-slate-600 dark:text-slate-400">{{ $usage->usage_date->format('d M Y') }}</td>
                            <td class="px-4 md:px-6 py-4">
                                <div class="font-medium text-slate-900 dark:text-slate-100">{{ $usage->part->code }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $usage->part->name }}</div>
                            </td>
                            <td class="px-4 md:px-6 py-4 whitespace-nowrap text-right text-slate-600 dark:text-slate-400">
                                {{ number_format($usage->quantity, 2) }} {{ $usage->part->unit }}
                            </td>
                            <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                @if($usage->truck)
                                    <x-badge variant="default">{{ $usage->truck->plate_number }}</x-badge>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 md:px-6 py-4 whitespace-nowrap text-right font-semibold text-slate-900 dark:text-slate-100">
                                {{ number_format($usage->total_cost, 2, ',', '.') }}
                            </td>
                            <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('part-usages.show', $usage) }}" class="text-indigo-600 hover:text-indigo-800">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                <div class="flex flex-col items-center gap-2">
                                    <span class="text-4xl">🔧</span>
                                    <p class="text-sm">Belum ada pemakaian part</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($usages->hasPages())
            <div class="px-4 md:px-6 py-4 border-t border-slate-200 dark:border-[#2d2d2d]">
                {{ $usages->links() }}
            </div>
        @endif
    </x-card>
</div>
@endsection

