@extends('layouts.app', ['title' => 'Laporan Laba Rugi'])

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <div class="text-2xl font-bold text-slate-900 dark:text-slate-100">Laporan Laba Rugi</div>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Periode {{ \Carbon\Carbon::parse($from)->format('d M Y') }} – {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
            </p>
        </div>
        <form method="get" class="flex items-center gap-3 bg-white dark:bg-slate-800 p-2 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-2 px-2">
                <span class="text-xs font-semibold text-slate-500 uppercase">Periode</span>
            </div>
            <input type="date" name="from" value="{{ $from }}" class="rounded-lg bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-700 text-sm px-3 py-2 focus:ring-2 focus:ring-indigo-500" />
            <span class="text-slate-400">-</span>
            <input type="date" name="to" value="{{ $to }}" class="rounded-lg bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-700 text-sm px-3 py-2 focus:ring-2 focus:ring-indigo-500" />
            <button class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium transition-colors shadow-sm">
                Terapkan
            </button>
        </form>
    </div>

    <div class="max-w-5xl mx-auto space-y-6">
        <x-card class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-700">
                            <th class="px-6 py-4 text-left font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider text-xs">Keterangan</th>
                            <th class="px-6 py-4 text-right font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider text-xs w-48">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        
                        {{-- 1. PENDAPATAN --}}
                        <x-report-section-row title="PENDAPATAN USAHA" :amount="$totalRevenue" :items="$revenue" />

                        {{-- 2. HPP --}}
                        <x-report-section-row title="BEBAN POKOK PENDAPATAN (HPP)" :amount="$totalCogs" :items="$cogs" is-expense="true" />

                        {{-- LABA KOTOR --}}
                        <tr class="bg-slate-50 dark:bg-slate-800/30 font-bold border-t-2 border-slate-200 dark:border-slate-700">
                            <td class="px-6 py-4 text-slate-800 dark:text-slate-200">LABA KOTOR</td>
                            <td class="px-6 py-4 text-right font-mono text-base">{{ number_format($grossProfit, 2, ',', '.') }}</td>
                        </tr>

                        {{-- 3. BEBAN OPERASIONAL --}}
                        <x-report-section-row title="BEBAN ADMINISTRASI & UMUM" :amount="$totalOpex" :items="$opex" is-expense="true" />

                        {{-- LABA OPERASI --}}
                        <tr class="bg-slate-50 dark:bg-slate-800/30 font-bold border-t-2 border-slate-200 dark:border-slate-700">
                            <td class="px-6 py-4 text-slate-800 dark:text-slate-200">LABA OPERASI</td>
                            <td class="px-6 py-4 text-right font-mono text-base">{{ number_format($operatingProfit, 2, ',', '.') }}</td>
                        </tr>

                        {{-- 4. PENDAPATAN & BEBAN LAIN-LAIN --}}
                        <x-report-section-row title="PENDAPATAN (BEBAN) LAIN-LAIN" :amount="$totalOther" :items="$other_income_expense" />

                        {{-- LABA BERSIH --}}
                        <tr class="bg-indigo-50 dark:bg-indigo-900/20 border-t-2 border-indigo-200 dark:border-indigo-800">
                            <td class="px-6 py-5 font-bold text-lg text-indigo-900 dark:text-indigo-100">LABA (RUGI) BERSIH</td>
                            <td class="px-6 py-5 text-right font-bold text-lg font-mono {{ $netProfit < 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                {{ number_format($netProfit, 2, ',', '.') }}
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
@endsection

{{-- Component Definition for Row with Expandable Details --}}
@once
    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('reportSection', () => ({
                expanded: false,
                toggle() { this.expanded = !this.expanded }
            }))
        })
    </script>
    @endpush
@endonce
