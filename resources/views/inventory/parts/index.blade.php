@extends('layouts.app', ['title' => 'Master Sparepart'])

@section('content')
<div class="space-y-4 md:space-y-6">
    {{-- Header Section --}}
    <x-card>
        <x-slot:header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-slate-900 dark:text-slate-100">Master Sparepart</h1>
                    <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400 mt-1">Kelola data sparepart</p>
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 md:gap-3">
                    <form method="get" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                        <input 
                            type="text" 
                            name="q" 
                            value="{{ request('q') }}" 
                            placeholder="Cari kode/nama..." 
                            class="flex-1 rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 md:px-4 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                        <select name="category" class="rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 md:px-4 py-2 text-sm">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ $cat }}</option>
                            @endforeach
                        </select>
                        <x-button variant="outline" type="submit" class="w-full sm:w-auto">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <span class="hidden sm:inline">Cari</span>
                        </x-button>
                    </form>
                    <x-button :href="route('parts.create')" variant="primary" class="w-full sm:w-auto">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span class="hidden sm:inline">Tambah</span>
                        <span class="sm:hidden">Tambah Baru</span>
                    </x-button>
                </div>
            </div>
        </x-slot:header>
    </x-card>

    {{-- Mobile Card View --}}
    <div class="block md:hidden space-y-3">
        @forelse($parts as $part)
            <x-card>
                <div class="space-y-2">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="font-semibold text-slate-900 dark:text-slate-100">{{ $part->code }}</h3>
                            <p class="text-sm text-slate-600 dark:text-slate-400">{{ $part->name }}</p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('parts.edit', $part) }}" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <span class="text-slate-500 dark:text-slate-400">Stok:</span>
                            <span class="font-semibold {{ ($part->total_stock ?? 0) < $part->min_stock ? 'text-red-600' : 'text-slate-900 dark:text-slate-100' }}">
                                {{ number_format($part->total_stock ?? 0, 2) }} {{ $part->unit }}
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-500 dark:text-slate-400">Kategori:</span>
                            <span class="text-slate-900 dark:text-slate-100">{{ $part->category ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </x-card>
        @empty
            <x-card>
                <div class="text-center py-8 text-slate-500 dark:text-slate-400">
                    <span class="text-4xl">🔧</span>
                    <p class="mt-2 text-sm">Belum ada data sparepart</p>
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
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Kode</th>
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Nama</th>
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Kategori</th>
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Satuan</th>
                        <th class="px-4 md:px-6 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Stok</th>
                        <th class="px-4 md:px-6 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Min Stok</th>
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Status</th>
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-[#1e1e1e] divide-y divide-slate-200 dark:divide-[#2d2d2d]">
                    @forelse($parts as $part)
                        <tr class="hover:bg-slate-50 dark:hover:bg-[#252525] transition-colors">
                            <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-slate-900 dark:text-slate-100">{{ $part->code }}</div>
                            </td>
                            <td class="px-4 md:px-6 py-4">
                                <div class="text-slate-900 dark:text-slate-100">{{ $part->name }}</div>
                            </td>
                            <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                <x-badge variant="default">{{ $part->category ?? '-' }}</x-badge>
                            </td>
                            <td class="px-4 md:px-6 py-4 whitespace-nowrap text-slate-600 dark:text-slate-400">
                                {{ $part->unit }}
                            </td>
                            <td class="px-4 md:px-6 py-4 whitespace-nowrap text-right">
                                <span class="font-semibold {{ ($part->total_stock ?? 0) < $part->min_stock ? 'text-red-600' : 'text-slate-900 dark:text-slate-100' }}">
                                    {{ number_format($part->total_stock ?? 0, 2) }}
                                </span>
                            </td>
                            <td class="px-4 md:px-6 py-4 whitespace-nowrap text-right text-slate-600 dark:text-slate-400">
                                {{ number_format($part->min_stock, 2) }}
                            </td>
                            <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                <x-badge :variant="$part->is_active ? 'success' : 'danger'">
                                    {{ $part->is_active ? 'Aktif' : 'Nonaktif' }}
                                </x-badge>
                            </td>
                            <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('parts.show', $part) }}" class="text-indigo-600 hover:text-indigo-800" title="Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('parts.edit', $part) }}" class="text-emerald-600 hover:text-emerald-800" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                <div class="flex flex-col items-center gap-2">
                                    <span class="text-4xl">🔧</span>
                                    <p class="text-sm">Belum ada data sparepart</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($parts->hasPages())
            <div class="px-4 md:px-6 py-4 border-t border-slate-200 dark:border-[#2d2d2d]">
                {{ $parts->links() }}
            </div>
        @endif
    </x-card>
</div>
@endsection

