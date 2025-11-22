@extends('layouts.app', ['title' => 'Rekapitulasi PPN'])

@section('content')
    <div class="mb-4">
        <div class="text-xl font-semibold">Rekapitulasi PPN</div>
        <p class="text-sm text-slate-500 dark:text-slate-400">
            Summary dan breakdown PPN Keluaran vs PPN Masukan
        </p>
    </div>

    <!-- Tabs -->
    <div class="mb-4 border-b border-slate-200 dark:border-slate-700">
        <nav class="-mb-px flex space-x-8">
            <a href="?view=monthly&month={{ $month }}"
               class="@if($view === 'monthly') border-blue-500 text-blue-600 dark:text-blue-400 @else border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Summary Bulanan
            </a>
            <a href="?view=annual&year={{ $year }}"
               class="@if($view === 'annual') border-blue-500 text-blue-600 dark:text-blue-400 @else border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
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
            <x-card title="Ringkasan PPN">
                <div class="space-y-4">
                    <!-- PPN Keluaran -->
                    <div class="flex items-center justify-between p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                        <div>
                            <div class="text-sm text-slate-600 dark:text-slate-400 mb-1">PPN Keluaran (Output VAT)</div>
                            <div class="text-2xl font-semibold text-blue-600 dark:text-blue-400">
                                Rp {{ number_format($ppnKeluaran, 0, ',', '.') }}
                            </div>
                            <a href="{{ route('reports.tax.ppn-keluaran') }}?month={{ $month }}"
                               class="text-xs text-blue-600 dark:text-blue-400 hover:underline mt-1 inline-block">
                                Lihat Detail →
                            </a>
                        </div>
                        <div class="text-4xl text-blue-600 dark:text-blue-400">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </div>
                    </div>

                    <!-- PPN Masukan -->
                    <div class="flex items-center justify-between p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                        <div>
                            <div class="text-sm text-slate-600 dark:text-slate-400 mb-1">PPN Masukan (Input VAT)</div>
                            <div class="text-2xl font-semibold text-green-600 dark:text-green-400">
                                Rp {{ number_format($ppnMasukan, 0, ',', '.') }}
                            </div>
                            <a href="{{ route('reports.tax.ppn-masukan') }}?month={{ $month }}"
                               class="text-xs text-green-600 dark:text-green-400 hover:underline mt-1 inline-block">
                                Lihat Detail →
                            </a>
                        </div>
                        <div class="text-4xl text-green-600 dark:text-green-400">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="border-t-2 border-slate-300 dark:border-slate-600"></div>

                    <!-- PPN Kurang/(Lebih) Bayar -->
                    <div class="p-4 {{ $ppnKurangBayar > 0 ? 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800' : 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800' }} rounded-lg border">
                        <div class="text-sm text-slate-600 dark:text-slate-400 mb-1">
                            PPN {{ $ppnKurangBayar > 0 ? 'Kurang Bayar' : 'Lebih Bayar' }}
                        </div>
                        <div class="text-3xl font-bold {{ $ppnKurangBayar > 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                            Rp {{ number_format(abs($ppnKurangBayar), 0, ',', '.') }}
                        </div>
                        <div class="text-xs text-slate-600 dark:text-slate-400 mt-2">
                            @if($ppnKurangBayar > 0)
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
                                    Dapat dikompensasi ke periode berikutnya
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Calculation Breakdown -->
                    <div class="mt-4 p-4 bg-slate-50 dark:bg-slate-800 rounded-lg text-sm">
                        <div class="font-semibold mb-2">Perhitungan:</div>
                        <div class="space-y-1 font-mono">
                            <div class="flex justify-between">
                                <span>PPN Keluaran</span>
                                <span>Rp {{ number_format($ppnKeluaran, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>PPN Masukan</span>
                                <span>Rp {{ number_format($ppnMasukan, 0, ',', '.') }}</span>
                            </div>
                            <div class="border-t border-slate-300 dark:border-slate-600 my-2"></div>
                            <div class="flex justify-between font-semibold">
                                <span>{{ $ppnKurangBayar > 0 ? 'Kurang Bayar' : 'Lebih Bayar' }}</span>
                                <span>Rp {{ number_format(abs($ppnKurangBayar), 0, ',', '.') }}</span>
                            </div>
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
                        <li>PPN Kurang Bayar harus disetor paling lambat tanggal 15 bulan berikutnya</li>
                        <li>PPN Lebih Bayar dapat dikompensasikan ke masa pajak berikutnya atau diminta restitusi</li>
                        <li>Pastikan semua faktur pajak sudah lengkap dan valid</li>
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

        <x-card title="Rekapitulasi PPN Tahun {{ $year }}">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-100 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Periode</th>
                            <th class="px-4 py-3 text-right font-semibold">PPN Keluaran</th>
                            <th class="px-4 py-3 text-right font-semibold">PPN Masukan</th>
                            <th class="px-4 py-3 text-right font-semibold">Kurang/(Lebih) Bayar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalKeluaran = 0;
                            $totalMasukan = 0;
                            $totalKurangBayar = 0;
                        @endphp
                        @foreach($annualData as $data)
                            @php
                                $totalKeluaran += $data['ppn_keluaran'];
                                $totalMasukan += $data['ppn_masukan'];
                                $totalKurangBayar += $data['kurang_bayar'];
                            @endphp
                            <tr class="border-b border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="px-4 py-3">{{ $data['month_name_id'] }} {{ $year }}</td>
                                <td class="px-4 py-3 text-right {{ $data['ppn_keluaran'] > 0 ? 'text-blue-600 dark:text-blue-400' : '' }}">
                                    {{ $data['ppn_keluaran'] > 0 ? 'Rp ' . number_format($data['ppn_keluaran'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-right {{ $data['ppn_masukan'] > 0 ? 'text-green-600 dark:text-green-400' : '' }}">
                                    {{ $data['ppn_masukan'] > 0 ? 'Rp ' . number_format($data['ppn_masukan'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-right font-medium {{ $data['kurang_bayar'] > 0 ? 'text-red-600 dark:text-red-400' : ($data['kurang_bayar'] < 0 ? 'text-emerald-600 dark:text-emerald-400' : '') }}">
                                    @if($data['kurang_bayar'] != 0)
                                        {{ $data['kurang_bayar'] > 0 ? 'Rp ' : '(Rp ' }}{{ number_format(abs($data['kurang_bayar']), 0, ',', '.') }}{{ $data['kurang_bayar'] < 0 ? ')' : '' }}
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
                            <td class="px-4 py-3 text-right text-blue-600 dark:text-blue-400">
                                Rp {{ number_format($totalKeluaran, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right text-green-600 dark:text-green-400">
                                Rp {{ number_format($totalMasukan, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right {{ $totalKurangBayar > 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                {{ $totalKurangBayar > 0 ? 'Rp ' : '(Rp ' }}{{ number_format(abs($totalKurangBayar), 0, ',', '.') }}{{ $totalKurangBayar < 0 ? ')' : '' }}
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
                        <li>Rekapitulasi ini menampilkan breakdown PPN per bulan dalam 1 tahun</li>
                        <li>Angka dalam kurung ( ) menunjukkan PPN Lebih Bayar</li>
                        <li>Gunakan laporan ini untuk rekonsiliasi SPT Tahunan</li>
                    </ul>
                </div>
            </x-card>
        </div>
    @endif
@endsection
