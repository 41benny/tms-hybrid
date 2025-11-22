@extends('layouts.app', ['title' => 'Laporan PPN Masukan'])

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <div>
            <div class="text-xl font-semibold">Laporan PPN Masukan (Input VAT)</div>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Periode {{ \Carbon\Carbon::parse($from)->format('d M Y') }} – {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
            </p>
        </div>
        <form method="get" class="flex items-center gap-2">
            <input type="month" name="month" value="{{ $month }}"
                   class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-3 py-2 text-sm" />
            <button type="submit" class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium">
                Terapkan
            </button>
            <a href="{{ route('reports.tax.ppn-summary') }}?month={{ $month }}"
               class="px-4 py-2 rounded bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-sm font-medium">
                Lihat Summary
            </a>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <x-card>
            <div class="text-sm text-slate-600 dark:text-slate-400 mb-1">Total DPP</div>
            <div class="text-2xl font-semibold">Rp {{ number_format($totalDpp, 0, ',', '.') }}</div>
        </x-card>
        <x-card>
            <div class="text-sm text-slate-600 dark:text-slate-400 mb-1">Total PPN Masukan (11%)</div>
            <div class="text-2xl font-semibold text-green-600 dark:text-green-400">Rp {{ number_format($totalPpn, 0, ',', '.') }}</div>
        </x-card>
        <x-card>
            <div class="text-sm text-slate-600 dark:text-slate-400 mb-1">Grand Total</div>
            <div class="text-2xl font-semibold">Rp {{ number_format($grandTotal, 0, ',', '.') }}</div>
        </x-card>
    </div>

    <!-- Data Table -->
    <x-card title="Detail Transaksi PPN Masukan">
        @if($data->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-100 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-3 py-3 text-left font-semibold">No</th>
                            <th class="px-3 py-3 text-left font-semibold">Tanggal</th>
                            <th class="px-3 py-3 text-left font-semibold">No. Dokumen</th>
                            <th class="px-3 py-3 text-left font-semibold">Vendor</th>
                            <th class="px-3 py-3 text-left font-semibold">NPWP</th>
                            <th class="px-3 py-3 text-left font-semibold">Tipe</th>
                            <th class="px-3 py-3 text-right font-semibold">DPP</th>
                            <th class="px-3 py-3 text-right font-semibold">PPN 11%</th>
                            <th class="px-3 py-3 text-right font-semibold">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $index => $row)
                            <tr class="border-b border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="px-3 py-2">{{ $index + 1 }}</td>
                                <td class="px-3 py-2">{{ \Carbon\Carbon::parse($row->journal_date)->format('d/m/Y') }}</td>
                                <td class="px-3 py-2">{{ $row->document_number }}</td>
                                <td class="px-3 py-2">{{ $row->vendor_name }}</td>
                                <td class="px-3 py-2 text-xs">{{ $row->npwp ?: '-' }}</td>
                                <td class="px-3 py-2">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $row->source_type == 'Vendor Bill' ? 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200' : 'bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200' }}">
                                        {{ $row->source_type }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-right">{{ number_format($row->dpp, 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-right text-green-600 dark:text-green-400">{{ number_format($row->ppn, 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-right font-medium">{{ number_format($row->total, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-100 dark:bg-slate-800 border-t-2 border-slate-300 dark:border-slate-600">
                        <tr class="font-semibold">
                            <td colspan="6" class="px-3 py-3 text-right">TOTAL</td>
                            <td class="px-3 py-3 text-right">{{ number_format($totalDpp, 0, ',', '.') }}</td>
                            <td class="px-3 py-3 text-right text-green-600 dark:text-green-400">{{ number_format($totalPpn, 0, ',', '.') }}</td>
                            <td class="px-3 py-3 text-right">{{ number_format($grandTotal, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div class="text-center py-8 text-slate-500 dark:text-slate-400">
                <p>Tidak ada data PPN Masukan untuk periode ini</p>
            </div>
        @endif
    </x-card>

    <!-- Info Box -->
    <div class="mt-4">
        <x-card>
            <div class="text-sm text-slate-600 dark:text-slate-400">
                <p class="font-semibold mb-2">Catatan:</p>
                <ul class="list-disc list-inside space-y-1">
                    <li>Laporan ini menampilkan PPN Masukan dari vendor bill dan part purchase yang sudah di-post ke jurnal</li>
                    <li>PPN Masukan dapat dikreditkan untuk mengurangi PPN Keluaran</li>
                    <li>Data diambil dari akun 1130 - PPN Masukan</li>
                </ul>
            </div>
        </x-card>
    </div>
@endsection
