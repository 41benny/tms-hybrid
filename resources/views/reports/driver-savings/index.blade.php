@extends('layouts.app', ['title' => 'Laporan Tabungan Supir'])

@section('content')
<div class="flex items-center justify-between mb-4">
    <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100">Laporan Tabungan & Jaminan Supir</h2>
    <div class="flex gap-2">
        <!-- Search -->
        <form method="GET" action="{{ route('reports.driver-savings.index') }}" class="relative">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari supir..." class="pl-3 pr-8 py-1.5 rounded-lg border border-slate-300 dark:border-slate-600 text-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-slate-800 dark:text-slate-200">
            <button type="submit" class="absolute right-2 top-1.5 text-slate-400 hover:text-slate-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </button>
        </form>
    </div>
</div>

<x-card class="overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-slate-700 dark:text-slate-400">
                <tr>
                    <th scope="col" class="px-6 py-3">Nama Supir</th>
                    <th scope="col" class="px-6 py-3 text-right">Saldo Tabungan</th>
                    <th scope="col" class="px-6 py-3 text-right">Saldo Jaminan</th>
                    <th scope="col" class="px-6 py-3 text-right">Total Simpanan</th>
                    <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                @forelse($drivers as $driver)
                    <tr class="bg-white hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:hover:bg-slate-700 transition">
                        <td class="px-6 py-4 font-medium text-slate-900 dark:text-white">
                            {{ $driver->name }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="font-mono {{ $driver->savings_balance < 0 ? 'text-red-600' : 'text-slate-600 dark:text-slate-300' }}">
                                Rp {{ number_format($driver->savings_balance ?? 0, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="font-mono {{ $driver->guarantee_balance < 0 ? 'text-red-600' : 'text-slate-600 dark:text-slate-300' }}">
                                Rp {{ number_format($driver->guarantee_balance ?? 0, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-slate-800 dark:text-white">
                            Rp {{ number_format(($driver->savings_balance ?? 0) + ($driver->guarantee_balance ?? 0), 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('reports.driver-savings.show', $driver->id) }}" class="text-blue-600 hover:text-blue-800 font-medium hover:underline">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-slate-500">Tidak ada data supir ditemukan</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($drivers->hasPages())
        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">
            {{ $drivers->links() }}
        </div>
    @endif
</x-card>
@endsection
