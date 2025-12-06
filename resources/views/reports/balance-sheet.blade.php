@extends('layouts.app', ['title' => 'Neraca'])

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <div>
            <div class="text-xl font-semibold">Laporan Neraca (Balance Sheet)</div>
            <p class="text-sm text-slate-500 dark:text-slate-400">Per Tanggal {{ \Carbon\Carbon::parse($asOf)->format('d M Y') }}</p>
        </div>
        <form method="get" class="flex items-center gap-2">
            <input type="date" name="as_of" value="{{ $asOf }}" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" />
            <button class="px-3 py-2 rounded bg-slate-200 dark:bg-slate-800">Terapkan</button>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Left Column: ASSETS --}}
        <div class="space-y-6">
            <x-card title="ASET (ASSETS)">
                <table class="min-w-full text-sm">
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        {{-- Current Assets (Assuming 11xx-15xx) --}}
                        @php
                            $currentAssets = collect($sections['asset'])->filter(fn($r) => str_starts_with($r['acc']->code, '11') || str_starts_with($r['acc']->code, '12') || str_starts_with($r['acc']->code, '13') || str_starts_with($r['acc']->code, '14') || str_starts_with($r['acc']->code, '15'));
                            $fixedAssets = collect($sections['asset'])->filter(fn($r) => str_starts_with($r['acc']->code, '16') || str_starts_with($r['acc']->code, '17'));
                            $otherAssets = collect($sections['asset'])->diffKeys($currentAssets)->diffKeys($fixedAssets);
                        @endphp

                        {{-- Current Assets Section --}}
                        <tr class="bg-slate-50/50 dark:bg-slate-800/50">
                            <td colspan="2" class="px-4 py-2 font-bold text-slate-700 dark:text-slate-300 text-xs uppercase tracking-wider">Aset Lancar</td>
                        </tr>
                        @foreach($currentAssets as $r)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-4 py-1.5 pl-8">{{ $r['acc']->code }} - {{ $r['acc']->name }}</td>
                                <td class="px-4 py-1.5 text-right font-mono">{{ number_format($r['balance'], 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        <tr class="font-semibold bg-slate-50 dark:bg-slate-800/30">
                            <td class="px-4 py-2 pl-8">Total Aset Lancar</td>
                            <td class="px-4 py-2 text-right font-mono">{{ number_format($currentAssets->sum('balance'), 2, ',', '.') }}</td>
                        </tr>

                        {{-- Fixed Assets Section --}}
                        <tr class="bg-slate-50/50 dark:bg-slate-800/50">
                            <td colspan="2" class="px-4 py-2 font-bold text-slate-700 dark:text-slate-300 text-xs uppercase tracking-wider mt-2">Aset Tetap</td>
                        </tr>
                        @foreach($fixedAssets as $r)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-4 py-1.5 pl-8">{{ $r['acc']->code }} - {{ $r['acc']->name }}</td>
                                <td class="px-4 py-1.5 text-right font-mono">{{ number_format($r['balance'], 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        <tr class="font-semibold bg-slate-50 dark:bg-slate-800/30">
                            <td class="px-4 py-2 pl-8">Total Aset Tetap</td>
                            <td class="px-4 py-2 text-right font-mono">{{ number_format($fixedAssets->sum('balance'), 2, ',', '.') }}</td>
                        </tr>

                         {{-- Other Assets Section --}}
                        @if($otherAssets->isNotEmpty())
                            <tr class="bg-slate-50/50 dark:bg-slate-800/50">
                                <td colspan="2" class="px-4 py-2 font-bold text-slate-700 dark:text-slate-300 text-xs uppercase tracking-wider mt-2">Aset Lainnya</td>
                            </tr>
                            @foreach($otherAssets as $r)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                    <td class="px-4 py-1.5 pl-8">{{ $r['acc']->code }} - {{ $r['acc']->name }}</td>
                                    <td class="px-4 py-1.5 text-right font-mono">{{ number_format($r['balance'], 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        @endif

                    </tbody>
                    <tfoot>
                        <tr class="font-bold text-lg border-t-2 border-slate-300 dark:border-slate-600 bg-slate-100 dark:bg-slate-800">
                            <td class="px-4 py-3">TOTAL ASET</td>
                            <td class="px-4 py-3 text-right font-mono">{{ number_format($totals['asset'], 2, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </x-card>
        </div>

        {{-- Right Column: LIABILITIES & EQUITY --}}
        <div class="space-y-6">
            <x-card title="KEWAJIBAN & EKUITAS">
                <table class="min-w-full text-sm">
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        {{-- LIABILITIES --}}
                         @php
                            $currentLiabilities = collect($sections['liability'])->filter(fn($r) => str_starts_with($r['acc']->code, '21') || str_starts_with($r['acc']->code, '22') || str_starts_with($r['acc']->code, '23') || str_starts_with($r['acc']->code, '24'));
                            $longTermLiabilities = collect($sections['liability'])->filter(fn($r) => str_starts_with($r['acc']->code, '25') || str_starts_with($r['acc']->code, '26') || str_starts_with($r['acc']->code, '27'));
                            $otherLiabilities = collect($sections['liability'])->diffKeys($currentLiabilities)->diffKeys($longTermLiabilities);
                        @endphp

                        <tr class="bg-slate-50/50 dark:bg-slate-800/50">
                            <td colspan="2" class="px-4 py-2 font-bold text-slate-700 dark:text-slate-300 text-xs uppercase tracking-wider">Kewajiban Lancar</td>
                        </tr>
                        @foreach($currentLiabilities as $r)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-4 py-1.5 pl-8">{{ $r['acc']->code }} - {{ $r['acc']->name }}</td>
                                <td class="px-4 py-1.5 text-right font-mono">{{ number_format($r['balance'], 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                         <tr class="font-semibold bg-slate-50 dark:bg-slate-800/30">
                            <td class="px-4 py-2 pl-8">Total Kewajiban Lancar</td>
                            <td class="px-4 py-2 text-right font-mono">{{ number_format($currentLiabilities->sum('balance'), 2, ',', '.') }}</td>
                        </tr>

                        @if($longTermLiabilities->isNotEmpty())
                            <tr class="bg-slate-50/50 dark:bg-slate-800/50">
                                <td colspan="2" class="px-4 py-2 font-bold text-slate-700 dark:text-slate-300 text-xs uppercase tracking-wider mt-2">Kewajiban Jangka Panjang</td>
                            </tr>
                            @foreach($longTermLiabilities as $r)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                    <td class="px-4 py-1.5 pl-8">{{ $r['acc']->code }} - {{ $r['acc']->name }}</td>
                                    <td class="px-4 py-1.5 text-right font-mono">{{ number_format($r['balance'], 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                             <tr class="font-semibold bg-slate-50 dark:bg-slate-800/30">
                                <td class="px-4 py-2 pl-8">Total Kewajiban Jangka Panjang</td>
                                <td class="px-4 py-2 text-right font-mono">{{ number_format($longTermLiabilities->sum('balance'), 2, ',', '.') }}</td>
                            </tr>
                        @endif

                        <tr class="font-bold bg-slate-100 dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700">
                            <td class="px-4 py-2">Total Kewajiban</td>
                            <td class="px-4 py-2 text-right font-mono">{{ number_format($totals['liability'], 2, ',', '.') }}</td>
                        </tr>

                        {{-- EQUITY --}}
                        <tr class="bg-slate-50/50 dark:bg-slate-800/50">
                            <td colspan="2" class="px-4 py-2 font-bold text-slate-700 dark:text-slate-300 text-xs uppercase tracking-wider mt-4">Ekuitas</td>
                        </tr>
                        @foreach($sections['equity'] as $r)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-4 py-1.5 pl-8">{{ $r['acc']->code }} - {{ $r['acc']->name }}</td>
                                <td class="px-4 py-1.5 text-right font-mono">{{ number_format($r['balance'], 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        
                        {{-- Current Earnings Row --}}
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-4 py-1.5 pl-8 font-semibold text-slate-600 dark:text-slate-400">Laba Rugi Tahun Berjalan</td>
                            <td class="px-4 py-1.5 text-right font-mono font-semibold text-slate-600 dark:text-slate-400">{{ number_format($currentEarnings, 2, ',', '.') }}</td>
                        </tr>
                        
                        <tr class="font-bold bg-slate-100 dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700">
                            <td class="px-4 py-2">Total Ekuitas</td>
                            <td class="px-4 py-2 text-right font-mono">{{ number_format($totals['equity'], 2, ',', '.') }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="font-bold text-lg border-t-2 border-slate-300 dark:border-slate-600 bg-slate-100 dark:bg-slate-800">
                            <td class="px-4 py-3">TOTAL KEWAJIBAN & EKUITAS</td>
                            <td class="px-4 py-3 text-right font-mono">{{ number_format($totals['liability'] + $totals['equity'], 2, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </x-card>
        </div>
    </div>
@endsection
