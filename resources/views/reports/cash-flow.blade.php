@extends('layouts.app', ['title' => 'Laporan Arus Kas'])

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <div>
            <div class="text-xl font-semibold">Laporan Arus Kas</div>
            <p class="text-sm text-slate-500 dark:text-slate-400">Periode {{ \Carbon\Carbon::parse($from)->format('d M Y') }} – {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</p>
        </div>
        <form method="get" class="flex items-center gap-2">
            <input type="date" name="from" value="{{ $from }}" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" />
            <input type="date" name="to" value="{{ $to }}" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" />
            <button class="px-3 py-2 rounded bg-slate-200 dark:bg-slate-800">Terapkan</button>
        </form>
    </div>

    @php
        // Helper to format number
        $fmt = fn($n) => number_format($n, 2, ',', '.');
        $fmtNeg = fn($n) => $n < 0 ? '(' . number_format(abs($n), 2, ',', '.') . ')' : number_format($n, 2, ',', '.');

        // Calculate Totals
        $totalOperating = $report['operating']['net_income'];
        foreach($report['operating']['adjustments'] as $val) $totalOperating += $val;
        foreach($report['operating']['changes'] as $val) $totalOperating += $val;
        
        $totalInvesting = 0;
        foreach($report['investing'] as $val) $totalInvesting += $val;
        
        $totalFinancing = 0;
        foreach($report['financing'] as $val) $totalFinancing += $val;
        
        $netChange = $totalOperating + $totalInvesting + $totalFinancing;
        $closingCash = $openingCash + $netChange;
    @endphp

    <div class="space-y-4">
        {{-- OPERATING ACTIVITIES --}}
        <x-card title="ARUS KAS DARI AKTIVITAS OPERASI">
            <table class="min-w-full text-sm">
                <tbody>
                    <tr class="font-semibold">
                        <td class="px-2 py-1">Laba / (Rugi) Bersih</td>
                        <td class="px-2 py-1 text-right">{{ $fmtNeg($report['operating']['net_income']) }}</td>
                    </tr>
                    
                    {{-- Adjustments --}}
                    <tr><td colspan="2" class="px-2 py-1 font-semibold text-slate-500 mt-2">Penyesuaian untuk merekonsiliasi laba bersih menjadi kas bersih:</td></tr>
                    <tr>
                        <td class="px-2 py-1 pl-6">Penyusutan</td>
                        <td class="px-2 py-1 text-right">{{ $fmtNeg($report['operating']['adjustments']['depreciation']) }}</td>
                    </tr>
                    @if($report['operating']['adjustments']['other'] != 0)
                    <tr>
                        <td class="px-2 py-1 pl-6">Penyesuaian Lainnya</td>
                        <td class="px-2 py-1 text-right">{{ $fmtNeg($report['operating']['adjustments']['other']) }}</td>
                    </tr>
                    @endif

                    {{-- Changes in Working Capital --}}
                    <tr><td colspan="2" class="px-2 py-1 font-semibold text-slate-500 mt-2">Perubahan dalam aktiva dan kewajiban operasi:</td></tr>
                    
                    <tr>
                        <td class="px-2 py-1 pl-6">Piutang Usaha & Lainnya</td>
                        <td class="px-2 py-1 text-right">{{ $fmtNeg($report['operating']['changes']['receivables']) }}</td>
                    </tr>
                    <tr>
                        <td class="px-2 py-1 pl-6">Persediaan</td>
                        <td class="px-2 py-1 text-right">{{ $fmtNeg($report['operating']['changes']['inventory']) }}</td>
                    </tr>
                    <tr>
                        <td class="px-2 py-1 pl-6">Uang Muka & Aktiva Lancar Lainnya</td>
                        <td class="px-2 py-1 text-right">{{ $fmtNeg($report['operating']['changes']['prepayments']) }}</td>
                    </tr>
                    <tr>
                        <td class="px-2 py-1 pl-6">Hutang Usaha</td>
                        <td class="px-2 py-1 text-right">{{ $fmtNeg($report['operating']['changes']['payables']) }}</td>
                    </tr>
                    <tr>
                        <td class="px-2 py-1 pl-6">Hutang / Simpanan Supir</td>
                        <td class="px-2 py-1 text-right">{{ $fmtNeg($report['operating']['changes']['driver_balances']) }}</td>
                    </tr>
                    <tr>
                        <td class="px-2 py-1 pl-6">Hutang Pajak</td>
                        <td class="px-2 py-1 text-right">{{ $fmtNeg($report['operating']['changes']['taxes_payable']) }}</td>
                    </tr>
                    <tr>
                        <td class="px-2 py-1 pl-6">Biaya yang Masih Harus Dibayar</td>
                        <td class="px-2 py-1 text-right">{{ $fmtNeg($report['operating']['changes']['accruals']) }}</td>
                    </tr>
                    @if($report['operating']['changes']['other_current_liabilities'] != 0)
                    <tr>
                        <td class="px-2 py-1 pl-6">Hutang Lancar Lainnya</td>
                        <td class="px-2 py-1 text-right">{{ $fmtNeg($report['operating']['changes']['other_current_liabilities']) }}</td>
                    </tr>
                    @endif

                </tbody>
                <tfoot>
                    <tr class="font-bold border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                        <td class="px-2 py-2">Kas Bersih dari Aktivitas Operasi</td>
                        <td class="px-2 py-2 text-right">{{ $fmtNeg($totalOperating) }}</td>
                    </tr>
                </tfoot>
            </table>
        </x-card>

        {{-- INVESTING ACTIVITIES --}}
        <x-card title="ARUS KAS DARI AKTIVITAS INVESTASI">
            <table class="min-w-full text-sm">
                <tbody>
                    <tr>
                        <td class="px-2 py-1">Perolehan / Penjualan Aktiva Tetap</td>
                        <td class="px-2 py-1 text-right">{{ $fmtNeg($report['investing']['fixed_assets']) }}</td>
                    </tr>
                    @if($report['investing']['other'] != 0)
                    <tr>
                        <td class="px-2 py-1">Aktivitas Investasi Lainnya</td>
                        <td class="px-2 py-1 text-right">{{ $fmtNeg($report['investing']['other']) }}</td>
                    </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr class="font-bold border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                        <td class="px-2 py-2">Kas Bersih dari Aktivitas Investasi</td>
                        <td class="px-2 py-2 text-right">{{ $fmtNeg($totalInvesting) }}</td>
                    </tr>
                </tfoot>
            </table>
        </x-card>

        {{-- FINANCING ACTIVITIES --}}
        <x-card title="ARUS KAS DARI AKTIVITAS PENDANAAN">
            <table class="min-w-full text-sm">
                <tbody>
                    <tr>
                        <td class="px-2 py-1">Penambahan (Pembayaran) Hutang Bank & Pinjaman</td>
                        <td class="px-2 py-1 text-right">{{ $fmtNeg($report['financing']['loans']) }}</td>
                    </tr>
                    <tr>
                        <td class="px-2 py-1">Modal & Ekuitas Lainnya</td>
                        <td class="px-2 py-1 text-right">{{ $fmtNeg($report['financing']['equity']) }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="font-bold border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                        <td class="px-2 py-2">Kas Bersih dari Aktivitas Pendanaan</td>
                        <td class="px-2 py-2 text-right">{{ $fmtNeg($totalFinancing) }}</td>
                    </tr>
                </tfoot>
            </table>
        </x-card>

        {{-- SUMMARY --}}
        <x-card title="RINGKASAN KAS DAN BANK">
            <table class="min-w-full text-sm">
                <tbody>
                    <tr class="font-semibold text-lg {{ $netChange < 0 ? 'text-red-500' : 'text-emerald-600' }}">
                        <td class="px-2 py-2">KENAIKAN (PENURUNAN) BERSIH KAS DAN BANK</td>
                        <td class="px-2 py-2 text-right">{{ $fmtNeg($netChange) }}</td>
                    </tr>
                    <tr class="font-semibold text-lg">
                        <td class="px-2 py-2">KAS DAN BANK AWAL</td>
                        <td class="px-2 py-2 text-right">{{ $fmtNeg($openingCash) }}</td>
                    </tr>
                    <tr class="font-bold text-xl border-t-2 border-slate-300 dark:border-slate-600">
                        <td class="px-2 py-3">KAS DAN BANK AKHIR</td>
                        <td class="px-2 py-3 text-right">{{ $fmtNeg($closingCash) }}</td>
                    </tr>
                </tbody>
            </table>
        </x-card>
    </div>
@endsection
