@extends('layouts.app', ['title' => 'Rekapitulasi PPh 23'])

@section('content')
    <div class="mb-4">
        <div class="text-xl font-semibold">Rekapitulasi PPh 23</div>
        <p class="text-sm text-slate-500 dark:text-slate-400">
            Summary dan breakdown PPh 23 Dipotong vs Dipungut
        </p>
    </div>

    <!-- Tabs -->
    <div class="mb-4 border-b border-slate-200 dark:border-slate-700">
        <nav class="-mb-px flex space-x-8">
            <a href="?view=monthly&month={{ $month }}"
               class="@if($view === 'monthly') border-orange-500 text-orange-600 dark:text-orange-400 @else border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Summary Bulanan
            </a>
            <a href="?view=annual&year={{ $year }}"
               class="@if($view === 'annual') border-orange-500 text-orange-600 dark:text-orange-400 @else border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Rekapitulasi Tahunan
            </a>
        </nav>
    </div>

    @if($view === 'monthly')
        <!-- Monthly View -->
        <div class="mb-4 flex items-center justify-end">
            <form method="get" class="flex items-center gap-2">
                <input type="hidden" name="view" value="monthly">
                <input type="month" name="month" value="{{ $month }}"
                       class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-3 py-2 text-sm" />
                <button type="submit" class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium">
                    Terapkan
                </button>
            </form>
        </div>

        <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
            Periode {{ \Carbon\Carbon::parse($from)->format('d M Y') }} – {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
        </p>

        <!-- Main Summary Card -->
        <div class="max-w-2xl mx-auto">
            <x-card title="Ringkasan PPh 23">
                <div class="space-y-4">
                    <!-- PPh 23 Dipotong -->
                    <div class="flex items-center justify-between p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg border border-orange-200 dark:border-orange-800">
                        <div>
                            <div class="text-sm text-slate-600 dark:text-slate-400 mb-1">PPh 23 Dipotong (dari Vendor)</div>
                            <div class="text-2xl font-semibold text-orange-600 dark:text-orange-400">
                                Rp {{ number_format($pph23Dipotong, 0, ',', '.') }}
                            </div>
                            <a href="{{ route('reports.tax.pph23-dipotong') }}?month={{ $month }}"
                               class="text-xs text-orange-600 dark:text-orange-400 hover:underline mt-1 inline-block">
                                Lihat Detail →
                            </a>
                        </div>
                        <div class="text-4xl text-orange-600 dark:text-orange-400">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                            </svg>
                        </div>
                    </div>

                    <!-- PPh 23 Dipungut -->
                    <div class="flex items-center justify-between p-4 bg-teal-50 dark:bg-teal-900/20 rounded-lg border border-teal-200 dark:border-teal-800">
                        <div>
                            <div class="text-sm text-slate-600 dark:text-slate-400 mb-1">PPh 23 Dipungut (oleh Customer)</div>
                            <div class="text-2xl font-semibold text-teal-600 dark:text-teal-400">
                                Rp {{ number_format($pph23Dipungut, 0, ',', '.') }}
                            </div>
                            <a href="{{ route('reports.tax.pph23-dipungut') }}?month={{ $month }}"
                               class="text-xs text-teal-600 dark:text-teal-400 hover:underline mt-1 inline-block">
                                Lihat Detail →
                            </a>
                        </div>
                        <div class="text-4xl text-teal-600 dark:text-teal-400">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="border-t-2 border-slate-300 dark:border-slate-600"></div>

                    <!-- Net PPh 23 Position -->
                    <div class="p-4 {{ $netPph23 > 0 ? 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800' : 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800' }} rounded-lg border">
                        <div class="text-sm text-slate-600 dark:text-slate-400 mb-1">
                            Net PPh 23 Position
                        </div>
                        <div class="text-3xl font-bold {{ $netPph23 > 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                            Rp {{ number_format(abs($netPph23), 0, ',', '.') }}
                        </div>
                        <div class="text-xs text-slate-600 dark:text-slate-400 mt-2">
                            @if($netPph23 > 0)
                                <span class="inline-flex items-center px-2 py-1 rounded-full bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd"/>
                                    </svg>
                                    Harus disetor ke negara
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-200">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd"/>
                                    </svg>
                                    Dapat dikompensasi
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Calculation Breakdown -->
                    <div class="mt-4 p-4 bg-slate-50 dark:bg-slate-800 rounded-lg text-sm">
                        <div class="font-semibold mb-2">Perhitungan:</div>
                        <div class="space-y-1 font-mono">
                            <div class="flex justify-between">
                                <span>PPh 23 Dipotong (Hutang)</span>
                                <span>Rp {{ number_format($pph23Dipotong, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>PPh 23 Dipungut (Piutang)</span>
                                <span>Rp {{ number_format($pph23Dipungut, 0, ',', '.') }}</span>
                            </div>
                            <div class="border-t border-slate-300 dark:border-slate-600 my-2"></div>
                            <div class="flex justify-between font-semibold">
                                <span>Net Position</span>
                                <span>Rp {{ number_format(abs($netPph23), 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Breakdown by Type -->
                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-lg">
                            <div class="text-xs text-slate-600 dark:text-slate-400 mb-1">Hutang PPh 23</div>
                            <div class="text-lg font-semibold">Rp {{ number_format($pph23Dipotong, 0, ',', '.') }}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-500 mt-1">Akun: 2240</div>
                        </div>
                        <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-lg">
                            <div class="text-xs text-slate-600 dark:text-slate-400 mb-1">Piutang PPh 23</div>
                            <div class="text-lg font-semibold">Rp {{ number_format($pph23Dipungut, 0, ',', '.') }}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-500 mt-1">Akun: 1530</div>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Info Box -->
        <div class="mt-4 max-w-2xl mx-auto">
            <x-card>
                <div class="text-sm text-slate-600 dark:text-slate-400">
                    <p class="font-semibold mb-2">Catatan:</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>PPh 23 Dipotong harus disetor paling lambat tanggal 10 bulan berikutnya</li>
                        <li>PPh 23 Dipungut adalah piutang pajak yang dapat dikompensasikan</li>
                        <li>Pastikan bukti potong PPh 23 lengkap untuk keperluan pelaporan SPT</li>
                        <li>Net position menunjukkan posisi bersih kewajiban PPh 23</li>
                    </ul>
                </div>
            </x-card>
        </div>

    @else
        <!-- Annual View -->
        <div class="mb-4 flex items-center justify-end">
            <form method="get" class="flex items-center gap-2">
                <input type="hidden" name="view" value="annual">
                <select name="year" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-3 py-2 text-sm">
                    @for($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <button type="submit" class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium">
                    Terapkan
                </button>
            </form>
        </div>

        <x-card title="Rekapitulasi PPh 23 Tahun {{ $year }}">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-100 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Periode</th>
                            <th class="px-4 py-3 text-right font-semibold">PPh 23 Dipotong</th>
                            <th class="px-4 py-3 text-right font-semibold">PPh 23 Dipungut</th>
                            <th class="px-4 py-3 text-right font-semibold">Net Position</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalDipotong = 0;
                            $totalDipungut = 0;
                            $totalNet = 0;
                        @endphp
                        @foreach($annualData as $data)
                            @php
                                $totalDipotong += $data['pph23_dipotong'];
                                $totalDipungut += $data['pph23_dipungut'];
                                $totalNet += $data['net_pph23'];
                            @endphp
                            <tr class="border-b border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="px-4 py-3">{{ $data['month_name_id'] }} {{ $year }}</td>
                                <td class="px-4 py-3 text-right {{ $data['pph23_dipotong'] > 0 ? 'text-orange-600 dark:text-orange-400' : '' }}">
                                    {{ $data['pph23_dipotong'] > 0 ? 'Rp ' . number_format($data['pph23_dipotong'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-right {{ $data['pph23_dipungut'] > 0 ? 'text-teal-600 dark:text-teal-400' : '' }}">
                                    {{ $data['pph23_dipungut'] > 0 ? 'Rp ' . number_format($data['pph23_dipungut'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-right font-medium {{ $data['net_pph23'] > 0 ? 'text-red-600 dark:text-red-400' : ($data['net_pph23'] < 0 ? 'text-emerald-600 dark:text-emerald-400' : '') }}">
                                    @if($data['net_pph23'] != 0)
                                        {{ $data['net_pph23'] > 0 ? 'Rp ' : '(Rp ' }}{{ number_format(abs($data['net_pph23']), 0, ',', '.') }}{{ $data['net_pph23'] < 0 ? ')' : '' }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-100 dark:bg-slate-800 border-t-2 border-slate-300 dark:border-slate-600">
                        <tr class="font-semibold">
                            <td class="px-4 py-3">TOTAL {{ $year }}</td>
                            <td class="px-4 py-3 text-right text-orange-600 dark:text-orange-400">
                                Rp {{ number_format($totalDipotong, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right text-teal-600 dark:text-teal-400">
                                Rp {{ number_format($totalDipungut, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right {{ $totalNet > 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                {{ $totalNet > 0 ? 'Rp ' : '(Rp ' }}{{ number_format(abs($totalNet), 0, ',', '.') }}{{ $totalNet < 0 ? ')' : '' }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-card>

        <!-- Info Box -->
        <div class="mt-4">
            <x-card>
                <div class="text-sm text-slate-600 dark:text-slate-400">
                    <p class="font-semibold mb-2">Catatan:</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Rekapitulasi ini menampilkan breakdown PPh 23 per bulan dalam 1 tahun</li>
                        <li>Angka dalam kurung ( ) menunjukkan posisi net negatif (lebih banyak dipungut)</li>
                        <li>Gunakan laporan ini untuk rekonsiliasi SPT Tahunan</li>
                    </ul>
                </div>
            </x-card>
        </div>
    @endif
@endsection
