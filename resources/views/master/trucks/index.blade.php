@extends('layouts.app', ['title' => 'Trucks'])

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold">Trucks</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Master data armada</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('trucks.create') }}" class="px-4 py-2 rounded bg-indigo-600 hover:bg-indigo-700 text-white">+ Tambah Baru</a>
            <form method="get" class="flex items-center gap-2">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nopol..." class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
                <button class="px-3 py-2 rounded bg-slate-200 dark:bg-slate-800">Cari</button>
            </form>
        </div>
    </div>

    <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/80">
        <table class="min-w-full text-sm">
            <thead class="text-left border-b border-slate-200 dark:border-slate-800">
                <tr class="text-slate-500">
                    <th class="px-4 py-2">No. Polisi</th>
                    <th class="px-4 py-2">Tipe</th>
                    <th class="px-4 py-2">Kapasitas</th>
                    <th class="px-4 py-2">Milik Sendiri</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $it)
                    <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <td class="px-4 py-2">{{ $it->plate_number }}</td>
                        <td class="px-4 py-2">{{ $it->vehicle_type }}</td>
                        <td class="px-4 py-2">{{ $it->capacity_tonase }}</td>
                        <td class="px-4 py-2">{{ $it->is_own_fleet ? 'Ya' : 'Tidak' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $items->links() }}</div>
@endsection

