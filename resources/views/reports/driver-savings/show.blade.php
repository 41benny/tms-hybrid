@extends('layouts.app', ['title' => 'Buku Tabungan - ' . $driver->name])

@section('content')
{{-- Screen View --}}
<div class="screen-only">
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
                    Cetak Buku Tabungan
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
                        <th class="px-3 py-2 text-right border-r dark:border-slate-600 bg-blue-50 dark:bg-blue-900/20">Masuk</th>
                        <th class="px-3 py-2 text-right border-r dark:border-slate-600 bg-blue-50 dark:bg-blue-900/20">Keluar</th>
                        <th class="px-3 py-2 text-right border-r dark:border-slate-600 bg-blue-50 dark:bg-blue-900/20 font-bold">Saldo</th>
                        
                        <th class="px-3 py-2 text-right border-r dark:border-slate-600 bg-emerald-50 dark:bg-emerald-900/20">Masuk</th>
                        <th class="px-3 py-2 text-right border-r dark:border-slate-600 bg-emerald-50 dark:bg-emerald-900/20">Keluar</th>
                        <th class="px-3 py-2 text-right bg-emerald-50 dark:bg-emerald-900/20 font-bold">Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse($formattedMutations as $m)
                        <tr class="bg-white hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:hover:bg-slate-700 transition">
                            <td class="px-3 py-2 whitespace-nowrap border-r dark:border-slate-600">{{ $m->date->format('d/m/Y') }}</td>
                            <td class="px-3 py-2 whitespace-nowrap border-r dark:border-slate-600">{{ $m->trip_date ? $m->trip_date->format('d/m/Y') : '-' }}</td>
                            <td class="px-3 py-2 border-r dark:border-slate-600 max-w-[150px] truncate">{{ Str::limit($m->route, 20) }}</td>
                            <td class="px-3 py-2 whitespace-nowrap border-r dark:border-slate-600">{{ $m->nopol }}</td>
                            <td class="px-3 py-2 whitespace-nowrap border-r dark:border-slate-600">{{ $m->doc_ref }}</td>
                            <td class="px-3 py-2 border-r dark:border-slate-600 max-w-[200px] truncate">{{ Str::limit($m->description, 35) }}</td>
                            
                            {{-- Savings --}}
                            <td class="px-3 py-2 text-right border-r dark:border-slate-600 bg-blue-50/50 dark:bg-blue-900/10">
                                @if($m->savings_in > 0) <span class="text-green-600">{{ number_format($m->savings_in, 0, ',', '.') }}</span> @else - @endif
                            </td>
                            <td class="px-3 py-2 text-right border-r dark:border-slate-600 bg-blue-50/50 dark:bg-blue-900/10">
                                @if($m->savings_out > 0) <span class="text-red-600">{{ number_format($m->savings_out, 0, ',', '.') }}</span> @else - @endif
                            </td>
                            <td class="px-3 py-2 text-right border-r dark:border-slate-600 bg-blue-50/50 dark:bg-blue-900/10 font-bold">{{ number_format($m->savings_balance, 0, ',', '.') }}</td>

                            {{-- Guarantee --}}
                            <td class="px-3 py-2 text-right border-r dark:border-slate-600 bg-emerald-50/50 dark:bg-emerald-900/10">
                                @if($m->guarantee_in > 0) <span class="text-green-600">{{ number_format($m->guarantee_in, 0, ',', '.') }}</span> @else - @endif
                            </td>
                            <td class="px-3 py-2 text-right border-r dark:border-slate-600 bg-emerald-50/50 dark:bg-emerald-900/10">
                                @if($m->guarantee_out > 0) <span class="text-red-600">{{ number_format($m->guarantee_out, 0, ',', '.') }}</span> @else - @endif
                            </td>
                            <td class="px-3 py-2 text-right bg-emerald-50/50 dark:bg-emerald-900/10 font-bold">{{ number_format($m->guarantee_balance, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="12" class="px-6 py-4 text-center text-slate-500">Belum ada mutasi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</div>

{{-- Print View (Hidden on Screen) --}}
<div class="print-only hidden">
    <div class="mb-4 border-b-2 border-slate-800 pb-2">
        <h1 class="text-2xl font-bold uppercase tracking-wide text-slate-900">Buku Tabungan Supir</h1>
        <div class="flex justify-between items-end mt-2">
            <div>
                <table class="text-sm font-semibold">
                    <tr>
                        <td class="pr-4 py-1">Nama Supir</td>
                        <td class="px-2">:</td>
                        <td>{{ $driver->name }}</td>
                    </tr>
                    <tr>
                        <td class="pr-4 py-1">Tanggal Cetak</td>
                        <td class="px-2">:</td>
                        <td>{{ now()->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="pr-4 py-1">Periode Data</td>
                        <td class="px-2">:</td>
                        <td>
                            @if($formattedMutations->count() > 0)
                                {{ $formattedMutations->first()->date->format('d/m/Y') }} s/d {{ $formattedMutations->last()->date->format('d/m/Y') }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
            <div class="text-right">
                <p class="text-xs uppercase text-slate-500 mb-1">PT. TRANSPORINDO AGUNG SEJAHTERA</p>
            </div>
        </div>
    </div>

    <table class="w-full text-[10px] border-collapse border border-slate-900">
        <thead>
            <tr class="bg-gray-200">
                <th rowspan="2" class="border border-slate-900 px-1 py-1 w-16 text-center">Tgl</th>
                <th rowspan="2" class="border border-slate-900 px-1 py-1 w-24">No Ref</th>
                <th rowspan="2" class="border border-slate-900 px-1 py-1">Keterangan / Rute</th>
                <th colspan="3" class="border border-slate-900 px-1 py-1 text-center bg-gray-300">Tabungan</th>
                <th colspan="3" class="border border-slate-900 px-1 py-1 text-center bg-gray-300">Jaminan</th>
            </tr>
            <tr class="bg-gray-200">
                <th class="border border-slate-900 px-1 py-1 text-right w-20">Masuk</th>
                <th class="border border-slate-900 px-1 py-1 text-right w-20">Keluar</th>
                <th class="border border-slate-900 px-1 py-1 text-right w-24 font-bold">Saldo</th>
                <th class="border border-slate-900 px-1 py-1 text-right w-20">Masuk</th>
                <th class="border border-slate-900 px-1 py-1 text-right w-20">Keluar</th>
                <th class="border border-slate-900 px-1 py-1 text-right w-24 font-bold">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($formattedMutations as $m)
                <tr>
                    <td class="border border-slate-900 px-1 py-1 text-center font-mono">{{ $m->date->format('d/m/y') }}</td>
                    <td class="border border-slate-900 px-1 py-1 font-mono text-[9px]">{{ $m->doc_ref }}</td>
                    <td class="border border-slate-900 px-1 py-1">
                        <div class="font-bold">{{ $m->route }}</div>
                        <div class="text-[9px] text-slate-600 italic">{{ Str::limit($m->description, 40) }}</div>
                    </td>
                    
                    {{-- Tabungan --}}
                    <td class="border border-slate-900 px-1 py-1 text-right">
                        @if($m->savings_in > 0) {{ number_format($m->savings_in, 0, ',', '.') }} @endif
                    </td>
                    <td class="border border-slate-900 px-1 py-1 text-right">
                        @if($m->savings_out > 0) {{ number_format($m->savings_out, 0, ',', '.') }} @endif
                    </td>
                    <td class="border border-slate-900 px-1 py-1 text-right font-bold bg-gray-50">
                        {{ number_format($m->savings_balance, 0, ',', '.') }}
                    </td>

                    {{-- Jaminan --}}
                    <td class="border border-slate-900 px-1 py-1 text-right">
                        @if($m->guarantee_in > 0) {{ number_format($m->guarantee_in, 0, ',', '.') }} @endif
                    </td>
                    <td class="border border-slate-900 px-1 py-1 text-right">
                        @if($m->guarantee_out > 0) {{ number_format($m->guarantee_out, 0, ',', '.') }} @endif
                    </td>
                    <td class="border border-slate-900 px-1 py-1 text-right font-bold bg-gray-50">
                        {{ number_format($m->guarantee_balance, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach
            {{-- Total Row --}}
            <tr class="bg-gray-100 font-bold border-t-2 border-slate-900">
                <td colspan="3" class="border border-slate-900 px-2 py-2 text-right uppercase">Saldo Akhir</td>
                <td colspan="3" class="border border-slate-900 px-2 py-2 text-right text-lg">{{ number_format($totalSavings, 0, ',', '.') }}</td>
                <td colspan="3" class="border border-slate-900 px-2 py-2 text-right text-lg">{{ number_format($totalGuarantee, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="mt-8 flex justify-between text-xs text-center">
        <div class="w-32">
            <p class="mb-12">Diterima Oleh,</p>
            <p class="font-bold uppercase border-b border-black select-none">&nbsp;</p>
            <p>( {{ $driver->name }} )</p>
        </div>
        <div class="w-32">
            <p class="mb-12">Mengetahui,</p>
            <p class="font-bold uppercase border-b border-black">{{ auth()->user()->name ?? 'Finance' }}</p>
            <p>( Admin Keuangan )</p>
        </div>
    </div>
</div>

<style>
    @media print {
        @page { size: A4 landscape; margin: 10mm; }
        body { background: white !important; font-family: 'Courier New', Courier, monospace; color: black !important; }
        .screen-only, nav, header, aside, .no-print { display: none !important; }
        .print-only { display: block !important; }
        .print-only table { width: 100%; border-collapse: collapse; }
        .print-only th, .print-only td { border: 1px solid #000 !important; color: #000 !important; }
        tr { page-break-inside: avoid; }
    }
</style>
@endsection
