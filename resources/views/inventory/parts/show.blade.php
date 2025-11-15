@extends('layouts.app', ['title' => 'Detail Sparepart'])

@section('content')
<div class="space-y-4 md:space-y-6">
    <x-card>
        <x-slot:header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $part->code }}</h1>
                    <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400 mt-1">{{ $part->name }}</p>
                </div>
                <div class="flex gap-2">
                    <x-button :href="route('parts.edit', $part)" variant="outline" size="sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit
                    </x-button>
                    <x-button :href="route('parts.index')" variant="ghost" size="sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Kembali
                    </x-button>
                </div>
            </div>
        </x-slot:header>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
            <div>
                <label class="text-xs md:text-sm text-slate-500 dark:text-slate-400">Kode</label>
                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $part->code }}</p>
            </div>
            <div>
                <label class="text-xs md:text-sm text-slate-500 dark:text-slate-400">Nama</label>
                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $part->name }}</p>
            </div>
            <div>
                <label class="text-xs md:text-sm text-slate-500 dark:text-slate-400">Kategori</label>
                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $part->category ?? '-' }}</p>
            </div>
            <div>
                <label class="text-xs md:text-sm text-slate-500 dark:text-slate-400">Satuan</label>
                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ $part->unit }}</p>
            </div>
            <div>
                <label class="text-xs md:text-sm text-slate-500 dark:text-slate-400">Total Stok</label>
                <p class="font-semibold {{ $part->total_stock < $part->min_stock ? 'text-red-600' : 'text-slate-900 dark:text-slate-100' }}">
                    {{ number_format($part->total_stock, 2) }} {{ $part->unit }}
                </p>
            </div>
            <div>
                <label class="text-xs md:text-sm text-slate-500 dark:text-slate-400">Minimum Stok</label>
                <p class="font-semibold text-slate-900 dark:text-slate-100">{{ number_format($part->min_stock, 2) }} {{ $part->unit }}</p>
            </div>
            <div>
                <label class="text-xs md:text-sm text-slate-500 dark:text-slate-400">Status</label>
                <p>
                    <x-badge :variant="$part->is_active ? 'success' : 'danger'">
                        {{ $part->is_active ? 'Aktif' : 'Nonaktif' }}
                    </x-badge>
                </p>
            </div>
            @if($part->description)
                <div class="md:col-span-2">
                    <label class="text-xs md:text-sm text-slate-500 dark:text-slate-400">Deskripsi</label>
                    <p class="text-slate-900 dark:text-slate-100">{{ $part->description }}</p>
                </div>
            @endif
        </div>
    </x-card>

    {{-- Stock by Location --}}
    @if($part->stocks->count() > 0)
        <x-card title="Stok per Lokasi">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-[#252525]">
                        <tr>
                            <th class="px-4 py-2 text-left">Lokasi</th>
                            <th class="px-4 py-2 text-right">Qty</th>
                            <th class="px-4 py-2 text-right">Harga Rata-rata</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-[#2d2d2d]">
                        @foreach($part->stocks as $stock)
                            <tr>
                                <td class="px-4 py-2">{{ $stock->location }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format($stock->quantity, 2) }} {{ $part->unit }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format($stock->unit_cost, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>
    @endif
</div>
@endsection

