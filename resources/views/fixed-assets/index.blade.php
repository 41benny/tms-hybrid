@extends('layouts.app')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <div>
            <div class="text-xl font-semibold">Daftar Aset Tetap</div>
            <p class="text-sm text-slate-500 dark:text-slate-400">Manajemen aset tetap perusahaan</p>
        </div>
        <a href="{{ route('fixed-assets.create') }}" class="px-2 py-1.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-500 transition-all flex items-center gap-1 text-xs font-medium shadow-sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Aset Tetap
        </a>
    </div>

    <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/80">
        <table class="min-w-full text-sm">
            <thead class="text-left border-b border-slate-200 dark:border-slate-800">
                <tr class="text-slate-500">
                    <th class="px-4 py-2">Kode</th>
                    <th class="px-4 py-2">Nama</th>
                    <th class="px-4 py-2">Tanggal Perolehan</th>
                    <th class="px-4 py-2">Nilai Perolehan</th>
                    <th class="px-4 py-2">Umur (bulan)</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assets as $asset)
                <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-[#252525] transition-colors">
                    <td class="px-4 py-2 font-medium">{{ $asset->code }}</td>
                    <td class="px-4 py-2">{{ $asset->name }}</td>
                    <td class="px-4 py-2">{{ $asset->acquisition_date->format('d M Y') }}</td>
                    <td class="px-4 py-2">Rp {{ number_format($asset->acquisition_cost, 0, ',', '.') }}</td>
                    <td class="px-4 py-2">{{ $asset->useful_life_months }}</td>
                    <td class="px-4 py-2"><x-badge>{{ ucfirst($asset->status) }}</x-badge></td>
                    <td class="px-4 py-2 flex gap-3">
                        <a class="underline" href="{{ route('fixed-assets.show', $asset) }}" title="Lihat">👁️</a>
                        <a class="underline" href="{{ route('fixed-assets.edit', $asset) }}" title="Edit">✎</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
