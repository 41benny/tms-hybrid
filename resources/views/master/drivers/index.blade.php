@extends('layouts.app', ['title' => 'Drivers'])

@section('content')
<div class="space-y-6">
    {{-- Header Section --}}
    <x-card>
        <x-slot:header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-end gap-4">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <form method="get" class="flex items-center gap-2">
                        <input
                            type="text"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="Cari nama..."
                            class="rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-4 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        >
                        <x-button variant="outline" type="submit">
                            🔍 Cari
                        </x-button>
                    </form>
                    <x-button :href="route('drivers.create')" variant="primary">
                        ✨ Tambah Baru
                    </x-button>
                </div>
            </div>
        </x-slot:header>
    </x-card>

    {{-- Table Section --}}
    <x-card :noPadding="true">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-[#2d2d2d]">
                <thead class="bg-slate-50 dark:bg-[#252525]">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                            Nama
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                            Telepon
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-[#1e1e1e] divide-y divide-slate-200 dark:divide-[#2d2d2d]">
                    @forelse($items as $item)
                        <tr class="hover:bg-slate-50 dark:hover:bg-[#252525] transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-slate-900 dark:text-slate-100">{{ $item->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-600 dark:text-slate-400">
                                {{ $item->phone }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-badge :variant="$item->is_active ? 'success' : 'default'">
                                    {{ $item->is_active ? '✅ Aktif' : '⏸️ Tidak Aktif' }}
                                </x-badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <x-button :href="route('cash-banks.create', ['sumber' => 'driver_withdrawal', 'recipient_name' => $item->name])" variant="ghost" size="sm" title="Cairkan Tabungan">
                                        💰
                                    </x-button>
                                    <x-button :href="route('drivers.edit', $item)" variant="ghost" size="sm">
                                        ✏️
                                    </x-button>
                                    <form method="POST" action="{{ route('drivers.destroy', $item) }}" onsubmit="return confirm('Yakin ingin menghapus?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-button variant="ghost" size="sm" type="submit">
                                            🗑️
                                        </x-button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                <div class="flex flex-col items-center gap-2">
                                    <span class="text-4xl">👨‍✈️</span>
                                    <p class="text-sm">Belum ada data driver</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800">
                {{ $items->links() }}
            </div>
        @endif
    </x-card>
</div>
@endsection
