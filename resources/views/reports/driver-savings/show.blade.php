@extends('layouts.app', ['title' => 'Buku Tabungan - ' . $driver->name])

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ $driver->name }}</h1>
            <p class="text-slate-500 dark:text-slate-400">Laporan Mutasi Tabungan & Jaminan</p>
        </div>
        <div class="flex gap-2">
            <x-button :href="route('reports.driver-savings.index')" variant="secondary" size="sm" class="no-print">
                &larr; Kembali
            </x-button>
            <x-button onclick="window.print()" variant="primary" size="sm" class="no-print">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak
            </x-button>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div class="bg-white dark:bg-slate-800 p-4 rounded-lg shadow border border-slate-200 dark:border-slate-700">
        <h3 class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Total Tabungan</h3>
        <p class="text-2xl font-bold text-slate-900 dark:text-white">Rp {{ number_format($totalSavings, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white dark:bg-slate-800 p-4 rounded-lg shadow border border-slate-200 dark:border-slate-700">
        <h3 class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Total Jaminan</h3>
        <p class="text-2xl font-bold text-slate-900 dark:text-white">Rp {{ number_format($totalGuarantee, 0, ',', '.') }}</p>
    </div>
</div>

<x-card title="Rincian Mutasi" class="overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-xs text-left">
            <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-slate-700 dark:text-slate-400">
                <tr>
                    <th rowspan="2" class="px-3 py-3 border-r dark:border-slate-600">Tanggal</th>
                    <th rowspan="2" class="px-3 py-3 border-r dark:border-slate-600">Tgl Jalan</th>
                    <th rowspan="2" class="px-3 py-3 border-r dark:border-slate-600">Rute</th>
                    <th rowspan="2" class="px-3 py-3 border-r dark:border-slate-600">Nopol</th>
                    <th rowspan="2" class="px-3 py-3 border-r dark:border-slate-600">No. Ref / JO</th>
                    <th rowspan="2" class="px-3 py-3 border-r dark:border-slate-600 w-1/4">Keterangan</th>
                    <th colspan="3" class="px-3 py-2 text-center border-b border-r dark:border-slate-600 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300">Tabungan</th>
                    <th colspan="3" class="px-3 py-2 text-center border-b dark:border-slate-600 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300">Jaminan</th>
                </tr>
                <tr>
                    {{-- Savings Subheaders --}}
                    <th class="px-3 py-2 text-right border-r dark:border-slate-600 bg-blue-50 dark:bg-blue-900/20">Masuk</th>
                    <th class="px-3 py-2 text-right border-r dark:border-slate-600 bg-blue-50 dark:bg-blue-900/20">Keluar</th>
                    <th class="px-3 py-2 text-right border-r dark:border-slate-600 bg-blue-50 dark:bg-blue-900/20 font-bold">Saldo</th>
                    
                    {{-- Guarantee Subheaders --}}
                    <th class="px-3 py-2 text-right border-r dark:border-slate-600 bg-emerald-50 dark:bg-emerald-900/20">Masuk</th>
                    <th class="px-3 py-2 text-right border-r dark:border-slate-600 bg-emerald-50 dark:bg-emerald-900/20">Keluar</th>
                    <th class="px-3 py-2 text-right bg-emerald-50 dark:bg-emerald-900/20 font-bold">Saldo</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                @forelse($formattedMutations as $m)
                    <tr class="bg-white hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:hover:bg-slate-700 transition">
                        <td class="px-3 py-2 whitespace-nowrap border-r dark:border-slate-600">
                            {{ $m->date->format('d/m/Y') }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap border-r dark:border-slate-600">
                            {{ $m->trip_date ? $m->trip_date->format('d/m/Y') : '-' }}
                        </td>
                        <td class="px-3 py-2 border-r dark:border-slate-600 max-w-[150px] truncate" title="{{ $m->route }}">
                            {{Str::limit($m->route, 20) }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap border-r dark:border-slate-600">
                            {{ $m->nopol }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap border-r dark:border-slate-600">
                            @if($m->job_order_id)
                                <a href="{{ route('job-orders.show', $m->job_order_id) }}" class="text-blue-600 hover:underline">
                                    {{ $m->doc_ref }}
                                </a>
                            @else
                                {{ $m->doc_ref }}
                            @endif
                        </td>
                        <td class="px-3 py-2 border-r dark:border-slate-600 max-w-[200px] truncate" title="{{ $m->description }}">
                            {{ Str::limit($m->description, 35) }}
                        </td>
                        
                        {{-- Savings Columns --}}
                        <td class="px-3 py-2 text-right border-r dark:border-slate-600 bg-blue-50/50 dark:bg-blue-900/10">
                            @if($m->savings_in > 0)
                                <span class="text-green-600 dark:text-green-400">{{ number_format($m->savings_in, 0, ',', '.') }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right border-r dark:border-slate-600 bg-blue-50/50 dark:bg-blue-900/10">
                             @if($m->savings_out > 0)
                                <span class="text-red-600 dark:text-red-400">{{ number_format($m->savings_out, 0, ',', '.') }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right border-r dark:border-slate-600 bg-blue-50/50 dark:bg-blue-900/10 font-bold">
                            {{ number_format($m->savings_balance, 0, ',', '.') }}
                        </td>

                        {{-- Guarantee Columns --}}
                        <td class="px-3 py-2 text-right border-r dark:border-slate-600 bg-emerald-50/50 dark:bg-emerald-900/10">
                            @if($m->guarantee_in > 0)
                                <span class="text-green-600 dark:text-green-400">{{ number_format($m->guarantee_in, 0, ',', '.') }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right border-r dark:border-slate-600 bg-emerald-50/50 dark:bg-emerald-900/10">
                             @if($m->guarantee_out > 0)
                                <span class="text-red-600 dark:text-red-400">{{ number_format($m->guarantee_out, 0, ',', '.') }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right bg-emerald-50/50 dark:bg-emerald-900/10 font-bold">
                            {{ number_format($m->guarantee_balance, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="px-6 py-4 text-center text-slate-500">Belum ada mutasi tabungan atau jaminan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-card>

<style media="print">
    @page { size: landscape; }
    nav, footer, button, .no-print { display: none !important; }
    .card { box-shadow: none !important; border: none !important; }
    body { background: white !important; }
</style>
@endsection
